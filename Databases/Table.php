<?php

namespace Torpeedo\Databases ;

use Torpeedo\Logs as TLogs ;

class Table
{
	private $xDatabase ;
	private $sName ;
	private $sShortName ;
	private $sPrimaryKey ;
	private $aFields ;
	private $aTypes ;
	private $aSizes ;
	private $aWithNulls ;
	private $aForeignKeys ;
	
	private $bTorpeedoNotation ;
	private $aTorpeedoFields ;
	
	private $sTP ;

    public function __construct ( $sTable, $xDatabase, $sContext = '' )
    {
		/*
		// if Context if set, use it as a part of the path to reach the config file
		*/
		if ( $sContext !== '' ) $sTableFile = $sContext . "/" . $sTable . ".json" ;
		else $sTableFile = $sTable . ".json" ;

		try {
			/*
			// Cast the json file content to an array
			*/
			$aTableConfig = json_decode ( file_get_contents ( $sTableFile ), true ) ;
		} catch ( Exception $e ) { Logs\TLog :: std ( $e ) ; }

		/*
		// Dispatch the informations in the expected properties
		*/
		$this -> bTorpeedoNotation = ( ( isset ( $aTableConfig [ "TorpeedoNotation" ] ) && strtolower ( $aTableConfig [ "TorpeedoNotation" ] ) !== 'no' ) ? true : false ) ;
	
		$this -> sName = $aTableConfig [ "Name" ] ;
		$this -> sShortName = $aTableConfig [ "ShortName" ] ;
		$this -> sName = $aTableConfig [ "Name" ] ;
		$this -> sPrimaryKey = $aTableConfig [ "PrimaryKey" ] ;
		if ( isset ( $aTableConfig [ "ForeignKeys" ] ) )
			$this -> aForeignKeys = $aTableConfig [ "ForeignKeys" ] ;
		
		/*
		// The Fields part is to be dispatched into 3 differents local arrays
		*/
		if ( $this -> bTorpeedoNotation ) {
			foreach ( $aTableConfig [ "Fields" ] as $aField ) {
				$this -> aFields [ $aField [ 'Name' ] ] = '' ;
				$this -> aTypes [ $aField [ 'Name' ] ] = strtolower ( $aField [ 'Type' ] ) ;
				$this -> aSizes [ $aField [ 'Name' ] ] = $aField [ 'Size' ] ;
				$this -> aWithNulls [ $aField [ 'Name' ] ] = ( isset ( $aField [ 'Null' ] ) ? true : false ) ;

				$aName = explode ( '_', $aField [ 'Name' ] ) ;
				if ( count ( $aName ) == 1 ) $sName = $aField [ 'Name' ] ;
				else {
					$sName = $aName [ 1 ] ; $sSep = '_'  ;
					for ( $i = 2 ; $i < count ( $aName ) - 1 ; $i++ ) $sName .= $sSep . $aName [ $i ] ;
				}
				$this -> aTorpeedoFields [ $sName ] = $aField [ 'Name' ] ;
			}
		} else {
			foreach ( $aTableConfig [ "Fields" ] as $aField ) {
				$this -> aFields [ $aField [ 'Name' ] ] = '' ;
				$this -> aTypes [ $aField [ 'Name' ] ] = strtolower ( $aField [ 'Type' ] ) ;
				$this -> aSizes [ $aField [ 'Name' ] ] = $aField [ 'Size' ] ;
				$this -> aWithNulls [ $aField [ 'Name' ] ] = ( isset ( $aField [ 'Null' ] ) ? true : false ) ;
			}
		}

		/*
		// Local storage of the database pointer
		*/
		$this -> xDatabase = $xDatabase ;
		$this -> sTP = $this -> xDatabase -> get ( "TablePrefix" ) ;
	}

    public function exists ( $iId )
    {
		try {
			/*
			// check if a record where $iId is the value of the primary key.
			µ// return true if exists, false otherwise.
			*/
			$sQuery = "select count(1) as ICOUNT from " . $this -> sTP . $this -> sName . " where " . $this -> sPrimaryKey . " = " . $iId ;
			if ( $aLocal = $this -> xDatabase -> query ( $sQuery ) ) {
				if ( $aLocal [ 0 ] -> ICOUNT >= 1 ) {
					return ( true ) ;
				} else return ( false) ;
			} else return ( false) ;
		} catch ( Exception $e ) { TLogs\TLog :: std ( $e ) ; }

		return ( $this ) ;
	}

    public function select ( $iId )
    {
		try {
			/*
			// get a record where $iId is the value of the primary key,
			// then store each field value in the matching cell of the local "aField" array
			*/
			$sQuery = "select * from " . $this -> sTP . $this -> sName . " where " . $this -> sPrimaryKey . " = " . $iId ;
			if ( $aLocal = $this -> xDatabase -> query ( $sQuery ) ) {
				if ( is_array ( $aLocal ) ) {
					foreach ( $aLocal [ 0 ] as $sField => $sValue ) {
						$this -> aFields [ $sField ] = $sValue ;
					}
				} else Logs\TLog :: std ( "No Record with id " . $iId ) ;
			}
		} catch ( Exception $e ) { TLogs\TLog :: std ( $e ) ; }

		return ( $this ) ;
	}

    public function delete ()
    {
		try {
			/*
			// delete a record where $iId is the value of the primary key if exists
			*/
			$sQuery = "delete from " . $this -> sTP . $this -> sName . " where " . $this -> sPrimaryKey . " = " . $this -> aFields [ $this -> sPrimaryKey ] ;
			if ( $aLocal = $this -> xDatabase -> query ( $sQuery ) ) {
				return ( true ) ;
			} else return ( false) ;
		} catch ( Exception $e ) { TLogs\TLog :: std ( $e ) ; }

		return ( $this ) ;
	}

    public function first ( $n = 1 )
    {
		try {
			$sQuery = "select * from " . $this -> sTP . $this -> sName . " limit " . $n ;
			if ( $aLocal = $this -> xDatabase -> query ( $sQuery ) ) {
				if ( is_array ( $aLocal ) ) {
					$aResult = array () ;
					foreach ( $aLocal as $iKey => $aRow ) {
						$aResult [ $iKey ] = clone ( $this ) ;
						foreach ( $aRow as $sField => $sValue ) {
							if ( ! isset ( $this -> aFields [ $sField ] ) ) $c = "*" . $sField ; else $c = $sField ;
							$aResult [ $iKey ] -> aFields [ $c ] = $sValue ;
						}
					}
				} else Logs\TLog :: std ( "No Record with id " . $iId ) ;
			}
		} catch ( Exception $e ) { TLogs\TLog :: std ( $e ) ; }
			
		return ( $aResult ) ;
	}

    public function insert ()
    {
		try {
			/*
			// Build the query regarding to the "aField" array keys and values
			*/
			$sQuery  = "insert into " . $this -> sTP . $this -> sName . " " ;
			$sQueryFields = '' ;
			$sQueryValues = '' ;
			
			/*
			// Depending on the value, add quote or replace with the "null" word when necessary
			*/
			$sFieldSep = "" ;
			foreach ( $this -> aFields as $sField => $sValue ) {
				$sQueryFields .= $sFieldSep . $sField ;
				if ( trim ( $sValue ) === '' && $sField === $this -> sPrimaryKey ) {
					$sQueryValues .= $sFieldSep . 'null' ;
				} else if ( trim ( $sValue ) === '' && $this -> aWithNulls [ $sField ] === true ) {
					$sQueryValues .= $sFieldSep . 'null' ;
				} else if ( $this -> aTypes [ $sField ] == 'integer' ) {
					$sQueryValues .= $sFieldSep . $sValue ;
				} else
					$sQueryValues .= $sFieldSep . "'" . $sValue . "'" ;
				$sFieldSep = ", " ;
			}

			/*
			// Build the full query concatenating all the pieces together
			*/
			$sQuery .= "(" . $sQueryFields . ") values (" . $sQueryValues . ")" ;
//			Logs\TLog ::  std ( $sQuery ) ;

			/*
			// try to persist this object in a database record
			// Say "WTF" if not able
			*/
			if ( ! $xReturnCode = $this -> xDatabase -> query ( $sQuery ) )
				throw new Exception ( "The insert query cannot be executed : " . $sQuery ) ;
		} catch ( Exception $e ) { TLogs\TLog :: std ( $e ) ; }

		return ( $this ) ;
	}

    public function update ()
    {
		try {
			/*
			// Build the query regarding to the "aField" array keys and values
			*/
			$sQuery  = "update " . $this -> sTP . $this -> sName . " set " ;
			$sQueryFields = '' ;
			$sQueryValues = '' ;
			
			/*
			// Depending on the value, add quote or replace with the "null" word when necessary
			*/
			$sFieldSep = "" ;
			foreach ( $this -> aFields as $sField => $sValue ) {
				$sQueryFields .= $sFieldSep . $sField . " = " ;
				echo $sValue . " --- " ;
				if ( trim ( $sValue ) === '' && $this -> aWithNulls [ $sField ] === true ) {
					$sQueryFields .= 'null' ;
				} else if ( $this -> aTypes [ $sField ] == 'integer' ) {
					$sQueryFields .= $sValue ;
				} else
					$sQueryFields .= "'" . $sValue . "'" ;
				$sFieldSep = ", " ;
			}
			
			/*
			// Build the full query concatenating all the pieces together
			*/
			$sQuery .= $sQueryFields . " where " . $this -> sPrimaryKey . " = " . $this -> aFields [ $this -> sPrimaryKey ] ;
//			Logs\TLog ::  std ( $sQuery ) ;

			/*
			// try to persist this object in a database record
			// Say "WTF" if not able
			*/
			if ( ! $xReturnCode = $this -> xDatabase -> query ( $sQuery ) )
				throw new Exception ( "De la merdasse : " . $sQuery ) ;
		} catch ( Exception $e ) { TLogs\TLog :: std ( $e ) ; }

		return ( $this ) ;
	}

    public function display ( $sMode = '' )
    {
		print ( "<pre>" ) ;

		switch ( strtolower ( $sMode ) ) {
			case 'array' :
				print_r ( $this -> aFields ) ;
				break ;
			case 'json' :
			case 'json-educated' :
				print ( '{' ) ;
				print ( "\n\t" . '"Object" : "' . $this -> sName . '",' ) ;
				print ( "\n\t" . '"Properties" : [' ) ;
				$sFieldSep = "\n" ;
				if ( $this -> bTorpeedoNotation ) {
					foreach ( $this -> aTorpeedoFields as $sField => $sValue ) {
						echo $sFieldSep . "\t\t" . '"' . $sField . '" : "' . $this -> aFields [ $sValue ] . '"' ;
						$sFieldSep = ',' . "\n" ;
					}
				} else {
					foreach ( $this -> aFields as $sField => $sValue ) {
						echo $sFieldSep . "\t\t" . '"' . $sField . '" : "' . $sValue . '"' ;
						$sFieldSep = ',' . "\n" ;
					}
				}
				print ( "\n\t" . ']' ) ;
				print ( "\n" . '}' ) ;
				break ;
			case 'json-utf8' :
				print ( '{' ) ;
				print ( "\n\t" . '"Object" : "' . $this -> sName . '",' ) ;
				print ( "\n\t" . '"Properties" : [' ) ;
				$sFieldSep = "\n" ;
				if ( $this -> bTorpeedoNotation ) {
					foreach ( $this -> aTorpeedoFields as $sField => $sValue ) {
						echo $sFieldSep . "\t\t" . '"' . $sField . '" : "' . utf8_encode ( $this -> aFields [ $sValue ] ) . '"' ;
						$sFieldSep = ',' . "\n" ;
					}
				} else {
					foreach ( $this -> aFields as $sField => $sValue ) {
						echo $sFieldSep . "\t\t" . '"' . $sField . '" : "' . utf8_encode ( $sValue ) . '"' ;
						$sFieldSep = ',' . "\n" ;
					}
				}
				print ( "\n\t" . ']' ) ;
				print ( "\n" . '}' ) ;
				break ;
			case "rude" :
				if ( $this -> bTorpeedoNotation ) {
					foreach ( $this -> aTorpeedoFields as $sField => $sValue )
						echo $sField . " = " . $this -> aFields [ $sValue ] . "<br />" ;
				} else {
					foreach ( $this -> aFields as $sField => $sValue )
						echo $sField . " = " . $sValue . "<br />" ;
				}
				break ;
			default :
				if ( $this -> bTorpeedoNotation ) {
					foreach ( $this -> aTorpeedoFields as $sField => $sValue )
						echo $sField . " = " . utf8_encode ( $this -> aFields [ $sValue ] ) . "<br />" ;
				} else {
					foreach ( $this -> aFields as $sField => $sValue )
						echo $sField . " = " . utf8_encode ( $sValue ) . "<br />" ;
				}
		}

		print ( "</pre>" ) ;

		return ( $this ) ;
	}

    public function get ( $sProperty )
    {
		if ( isset ( $this -> aFields [ $sProperty ] ) )
			return ( $this -> aFields [ $sProperty ] ) ;
		
		if ( $this -> bTorpeedoNotation && $LocalProperty = $this -> getTorpeedoProperty ( $sProperty ) )
			return ( $this -> aFields [ $LocalProperty ] ) ;
		
		return ( false ) ;
	}

    public function getTorpeedoProperty ( $sProperty )
    {
		if ( isset ( $this -> aTorpeedoFields [ $sProperty ] ) )
			return ( $this -> aTorpeedoFields [ $sProperty ] ) ;
		
		if ( isset ( $this -> aFields [ $sProperty ] ) )
			return ( $sProperty ) ;

		return ( false ) ;
	}

    public function set ( $sProperty, $sValue )
    {
		if ( isset ( $this -> aFields [ $sProperty ] ) )
			$sLocalProperty = $sProperty ;
		else if ( $this -> bTorpeedoNotation && isset ( $this -> aFields [ $this -> getTorpeedoProperty ( $sProperty ) ] ) )
			$sLocalProperty = $this -> getTorpeedoProperty ( $sProperty ) ;
		else {
			Logs\TLog :: std ( "Cannot find " . $sProperty . " property." ) ;
			return ( $this ) ;
		}
		
//		TLogs\TLog :: std ( "Query : " . $sProperty . ' = ' . utf8_encode ( $this -> aFields [ $sLocalProperty ] ) . ' => ' . utf8_encode ( $sValue ) ) ;

		$this -> aFields [ $sLocalProperty ] = $sValue ;
		
//		TLogs\TLog :: std ( "Result : " . $sProperty . ' = ' . utf8_encode ( $this -> aFields [ $sLocalProperty ] ) ) ;

		return ( $this ) ;
	}

}