<?php

namespace Torpeedo\Databases ;

class mysqliDatabase implements \Torpeedo\Databases\DatabaseInterface
{
	private $aConfig ;
	protected $xLink ;
	
    public function __construct ( $aConfig )
    {
		$this -> aConfig = $aConfig ;
	}

    public function __destruct ()
    {
		$this -> disconnect () ;
	}

    public function disconnect ()
    {
		if ( $this -> xLink !== '' ) $this -> xLink -> close () ;
		$this -> xLink = '' ;
	}

    public function get ( $sParam )
    {
		if ( isset ( $this -> aConfig [ $sParam ] ) ) return ( $this -> aConfig [ $sParam ] ) ;
		return ( '' ) ;
	}

    public function connect ()
    {
		if ( ! isset ( $this -> aConfig [ 'Port' ] ) ) $this -> aConfig [ 'Port' ] = 3306 ;

		try {
			$this->xLink = new \mysqli ( $this -> aConfig [ 'Server' ] . ':' . $this -> aConfig [ 'Port' ],
										  $this -> aConfig [ 'User' ],
										  $this -> aConfig [ 'Password' ],
										  $this -> aConfig [ 'Database' ] ) ;

			if ($this->xLink->connect_error) {
				echo('Erreur de connexion (' . $this->xLink->connect_errno . ') ' . $this->xLink->connect_error);
			}
			return ($this) ;
		} catch ( Exception $e ) { TLog :: std ( $e ) ; }
		
		return (false) ;
	}

    public function query ( $sQuery )
    {
        // Implémentation spécifique
		$aResult = array() ;
		try {
			if ( $oResult = $this -> xLink -> query ( $sQuery ) ) {
				if ( isset ( $oResult -> num_rows ) && $oResult -> num_rows > 0 ) {
					while ( $oRow = $oResult -> fetch_object () ) $aResult [] = $oRow ;
					$oResult -> close () ;	
				} else return ( true ) ;
			}
		} catch ( Exception $e ) { \Torpeedo\Logs\TLog :: std ( $e ) ; }
		
		return ( $aResult ) ;
    }

    public function getTableStructure ( $sTable, $bTorpeedoNotation = false )
    {
		$sSN = '' ;
		$sPK = '' ;
		$aFields = array () ;
		$aFK = array () ;

		try {
			$sQuery = "select * from " . $this -> get ( "TablePrefix" ) . $sTable . " limit 1" ;

			if ( $oResult = $this -> xLink -> query ( $sQuery ) ) {
				if ( isset ( $oResult -> num_rows ) ) {
					$aFields = $oResult -> fetch_fields () ;

					if ( $bTorpeedoNotation === true ) {
						$sSN = explode ( '_', $aFields [ 0 ] -> name ) [ 0 ] ;
						if ( $sSN === $aFields [ 0 ] -> name ) $sSN = '' ;
					}
				}
				$oResult -> close () ;	
			}
		} catch ( Exception $e ) { \Torpeedo\Logs\TLog :: std ( $e ) ; }

		$sFieldSep = '' ;
		$sJsonFieldOutput = '' ;
		
		foreach ( $aFields as $iKey => $oField ) {
	
			if ( $oField -> flags & 2 ) $sPK = $oField -> name ;
			if ( $oField -> flags & 1 ) $sWN = '' ; else $sWN = ', "Null" : "allowed"' ;

			switch ( $oField -> type ) {
				case 3 :
					$type = 'integer' ;
					break ;
				case 12 :
					$type = 'date' ;
					break ;
				default :
					$type = 'string' ;
					break ;
			}
			
			$sJsonFieldOutput .= $sFieldSep . "\n\t\t" . '{ "Name" : "' . $oField -> name .'", "Type" : "' . $type . '", "Size" : ' . $oField -> length . $sWN . ' }' ;
			$sFieldSep = ", " ;
		}

		try {
			$sQuery = "SELECT 
							TABLE_NAME,COLUMN_NAME,CONSTRAINT_NAME, REFERENCED_TABLE_NAME,REFERENCED_COLUMN_NAME
						FROM
							INFORMATION_SCHEMA.KEY_COLUMN_USAGE
						WHERE
							REFERENCED_TABLE_SCHEMA = '" . $this -> aConfig [ 'Database' ]  . "' AND
							TABLE_NAME = '" . $this -> get ( "TablePrefix" ) . $sTable . "'" ;

			if ( $oResult = $this -> xLink -> query ( $sQuery ) ) {
				if ( isset ( $oResult -> num_rows ) && $oResult -> num_rows > 0 ) {
					while ( $oFK = $oResult -> fetch_object () ) $aFK [] = $oFK ;
					$oResult -> close () ;	
				}
			}

		} catch ( Exception $e ) { \Torpeedo\Logs\TLog :: std ( $e ) ; }

		$sFieldSep = '' ;
		$sJsonFKOutput = '' ;

		foreach ( $aFK as $iKey => $oFK ) {
			$sTP = explode ( '_', $oFK -> REFERENCED_TABLE_NAME ) [ 0 ] . '_' ;

			if ( $sTP === $this -> get ( "TablePrefix" ) ) {
				$sTN = preg_replace ( '/^' . $sTP . '/', '', $oFK -> REFERENCED_TABLE_NAME ) ; 
			} else {
				$sTN = $oFK -> REFERENCED_TABLE_NAME ;
			}

			$sJsonFKOutput .= $sFieldSep . "\n\t\t" . '{ "Key" : "' . $oFK -> COLUMN_NAME .'", "Table" : "' . $sTN .'", "Field" : "' . $oFK -> REFERENCED_COLUMN_NAME .'" }' ;
			$sFieldSep = ", " ;
		}

		$sJsonOutput  = '{' . "\n" ;
		if ( $bTorpeedoNotation === true ) $sJsonOutput .= '	"TorpeedoNotation" : "yes",' ."\n" ;
		$sJsonOutput .= '	"Name" : "' . $sTable . '",' ."\n" ;
		$sJsonOutput .= '	"ShortName" : "' . $sSN . '",' ."\n" ;
		$sJsonOutput .= '	"PrimaryKey" : "' . $sPK . '",' ."\n" ;
		$sJsonOutput .= '	"Fields" : [' ;
		$sJsonOutput .= $sJsonFieldOutput ;
		$sJsonOutput .= "\n\t" . ']' ;
		if ( $sJsonFKOutput !== '' ) {
			$sJsonOutput .=  ",\n" . '	"ForeignKeys" : [' ;
			$sJsonOutput .= $sJsonFKOutput ;
			$sJsonOutput .= "\n\t" . ']' ;
		}
		$sJsonOutput .=  "\n" . '}' ;

		return ( $sJsonOutput ) ;
	}

}