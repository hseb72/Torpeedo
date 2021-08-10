<?php

namespace Torpeedo\Databases ;

use Torpeedo\Logs as TLogs ;

trait TTable
{
	public $xDBConnection ;
	private $sName ;
	private $sShortName ;
	private $sPrimaryKey ;
	private $aFields ;
	private $aFTypes ;
	private $aFSizes ;
	private $aFWithNulls ;
	private $aFProperties ;
	private $aForeignKeys ;

	private $sTP ;

    public function loadTable ( $xDBConnection )
    {
		/*
		// Local storage of the database pointer
		*/
		if ( ! $xDBConnection ) {
			Logs\TLog :: std ( "No Database connection provided." ) ;
			return ( false ) ;
		}
		
		$this -> xDBConnection = $xDBConnection ;
		$this -> sTP = $this -> xDBConnection -> get ( "TablePrefix" ) ;

		/*
		// Local storage of the table elements
		*/
		$this -> sName = $this -> aTConfig [ "Persistence" ] [ "TableName" ] ;
		$this -> sShortName = $this -> aTConfig [ "Persistence" ] [ "ShortName" ] ;
		$this -> sPrimaryKey = $this -> aTConfig [ "Persistence" ] [ "PrimaryKey" ] ;
		if ( isset ( $this -> aTConfig [ "Persistence" ] [ "ForeignKeys" ] ) )
			$this -> aForeignKeys = $this -> aTConfig [ "Persistence" ] [ "ForeignKeys" ] ;
		
		/*
		// The Fields part is to be dispatched into 4 differents local arrays
		*/
		foreach ( $this -> aTConfig [ "Persistence" ] [ "Fields" ] as $aField ) {
			$this -> aFProperties [ $aField [ 'Name' ] ] = $aField [ 'Property' ] ;
			$this -> aFTypes [ $aField [ 'Name' ] ] = strtolower ( $aField [ 'Type' ] ) ;
			$this -> aFSizes [ $aField [ 'Name' ] ] = $aField [ 'Size' ] ;
			$this -> aFWithNulls [ $aField [ 'Name' ] ] = ( isset ( $aField [ 'Null' ] ) ? true : false ) ;
		}
	}

    public function exists ( $iId )
    {

		try {
			/*
			// check if a record where $iId is the value of the primary key.
			µ// return true if exists, false otherwise.
			*/
			$sQuery = "select count(1) as ICOUNT from " . $this -> sTP . $this -> sName . " where " . $this -> sPrimaryKey . " = " . $iId ;
			if ( $aLocal = $this -> xDBConnection -> query ( $sQuery ) ) {
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
			if ( $aLocal = $this -> xDBConnection -> query ( $sQuery ) ) {
				if ( is_array ( $aLocal ) ) {
					foreach ( $aLocal [ 0 ] as $sField => $sValue ) {
						$this -> aTProperties [ $this -> aFProperties [ $sField ] ] = $sValue ;
					}
				} else TLogs\TLog :: std ( "No Record with id " . $iId ) ;
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
			$sQuery = "delete from " . $this -> sTP . $this -> sName . " 
						where " . $this -> sPrimaryKey . " = " . $this -> aTProperties [ $this -> aFProperties [ $this -> sPrimaryKey ] ] ;
			if ( $aLocal = $this -> xDBConnection -> query ( $sQuery ) ) {
				return ( true ) ;
			} else return ( false) ;
		} catch ( Exception $e ) { TLogs\TLog :: std ( $e ) ; }

		return ( $this ) ;
	}

    public function first ( $n = 1 )
    {
		try {
			$sQuery = "select * from " . $this -> sTP . $this -> sName . " limit " . $n ;
			if ( $aLocal = $this -> xDBConnection -> query ( $sQuery ) ) {
				if ( is_array ( $aLocal ) ) {
					$aResult = array () ;
					foreach ( $aLocal as $iKey => $aRow ) {
						$aResult [ $iKey ] = clone ( $this ) ;
						foreach ( $aRow as $sField => $sValue ) {
							if ( ! isset ( $this -> aFProperties [ $sField ] ) )
								$c = "*" . $sField ;
							else
								$c = $this -> aFProperties [ $sField ] ;
							$aResult [ $iKey ] -> aTProperties [ $c ] = $sValue ;
						}
					}
				} else TLogs\TLog :: std ( "No Record with id " . $iId ) ;
			}
		} catch ( Exception $e ) { TLogs\TLog :: std ( $e ) ; }
		
		$this -> aTList = $aResult ;
		
		return ( $this ) ;
	}

    public function sortByProperty ( $sProperty, $n = 1000 )
    {
		try {
			foreach ( $this -> aFProperties as $sKey => $sValue) {
				if ( $sValue == $sProperty ) $sOrderBy = " order by " . $sKey ;
			}

			$sQuery = "select * from " . $this -> sTP . $this -> sName . $sOrderBy . " limit " . $n ;

			if ( $aLocal = $this -> xDBConnection -> query ( $sQuery ) ) {
				if ( is_array ( $aLocal ) ) {
					$aResult = array () ;
					foreach ( $aLocal as $iKey => $aRow ) {
						$aResult [ $iKey ] = clone ( $this ) ;
						foreach ( $aRow as $sField => $sValue ) {
							if ( ! isset ( $this -> aFProperties [ $sField ] ) )
								$c = "*" . $sField ;
							else
								$c = $this -> aFProperties [ $sField ] ;
							$aResult [ $iKey ] -> aTProperties [ $c ] = $sValue ;
						}
					}
				} else TLogs\TLog :: std ( "No Record with id " . $iId ) ;
			}
		} catch ( Exception $e ) { TLogs\TLog :: std ( $e ) ; }
		
		$this -> aTList = $aResult ;
		
		return ( $this ) ;
	}

    public function all ()
    {
		try {
			$sQuery = "select * from " . $this -> sTP . $this -> sName . " limit 1000" ;
			if ( $aLocal = $this -> xDBConnection -> query ( $sQuery ) ) {
				if ( is_array ( $aLocal ) ) {
					$aResult = array () ;
					foreach ( $aLocal as $iKey => $aRow ) {
						$aResult [ $iKey ] = clone ( $this ) ;
						foreach ( $aRow as $sField => $sValue ) {
							if ( ! isset ( $this -> aFProperties [ $sField ] ) )
								$c = "__" . $sField ;
							else
								$c = $this -> aFProperties [ $sField ] ;
							$aResult [ $iKey ] -> aTProperties [ $c ] = $sValue ;
						}
					}
				} else TLogs\TLog :: std ( "No Record with id " . $iId ) ;
			}
		} catch ( Exception $e ) { TLogs\TLog :: std ( $e ) ; }
		
		$this -> aTList = $aResult ;
		
		return ( $this ) ;
	}

    public function singleFilter ( $sFilterField, $sFilterValue )
    {
		foreach ( $this -> aFProperties as $sField => $sValue ) {
			if ( $sFilterField == $sValue ) 
				return ( $this -> filter ( 
					"where " . $sField . " = '" . $sFilterValue . "'"
				)) ;
		}
		
		return $this -> all () ;
	}

    public function filter ( $sFilter )
    {
		$aResult = array () ;

		try {
			$sQuery = "select * from " . $this -> sTP . $this -> sName . " " . $sFilter . " limit 1000" ;
			if ( $aLocal = $this -> xDBConnection -> query ( $sQuery ) ) {
				if ( is_array ( $aLocal ) ) {
					foreach ( $aLocal as $iKey => $aRow ) {
						$aResult [ $iKey ] = clone ( $this ) ;
						foreach ( $aRow as $sField => $sValue ) {
							if ( ! isset ( $this -> aFProperties [ $sField ] ) )
								$c = "__" . $sField ;
							else
								$c = $this -> aFProperties [ $sField ] ;
							$aResult [ $iKey ] -> aTProperties [ $c ] = $sValue ;
						}
					}
				} //else TLogs\TLog :: std ( "No Record for filter " . $sFilter ) ;
			}
		} catch ( Exception $e ) { TLogs\TLog :: std ( $e ) ; }
		
		$this -> aTList = $aResult ;
		
		return ( $this ) ;
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
			foreach ( $this -> aFProperties as $sField => $sValue ) {
				$sQueryFields .= $sFieldSep . $sField ;
				if ( $sField === $this -> sPrimaryKey ) {
					$sQueryValues .= $sFieldSep . 'null' ;
				} else if ( trim ( $this -> aTProperties [ $sValue ] ) === '' && $this -> aFWithNulls [ $sField ] === true ) {
					$sQueryValues .= $sFieldSep . 'null' ;
				} else if ( $this -> aFTypes [ $sField ] == 'integer' || $this -> aFTypes [ $sField ] == 'float' ) {
					$sQueryValues .= $sFieldSep . $this -> aTProperties [ $sValue ] ;
				} else
					$sQueryValues .= $sFieldSep . "'" . preg_replace ( "/'/", "''", $this -> aTProperties [ $sValue ] ) . "'" ;
				$sFieldSep = ", " ;
			}

			/*
			// Build the full query concatenating all the pieces together
			*/
			$sQuery .= "(" . $sQueryFields . ") values (" . $sQueryValues . ")" ;
			//TLogs\TLog ::  std ( $sQuery ) ;

			/*
			// try to persist this object in a database record
			// Say "WTF" if not able
			*/
			if ( ! $xReturnCode = $this -> xDBConnection -> query ( $sQuery ) )
				throw new \Exception ( "The insert query cannot be executed : " . $sQuery ) ;
			if ( $sLII = $this -> xDBConnection -> lastInsertedId () ) {
				if ( $sLII != $this -> aTProperties [ $this -> aFProperties [ $this -> sPrimaryKey ] ] ) {
					$this -> aTProperties [ $this -> aFProperties [ $this -> sPrimaryKey ] ] = $sLII ;
				}
			}
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
			foreach ( $this -> aFProperties as $sField => $sValue ) {
				$sQueryFields .= $sFieldSep . $sField . " = " ;
				if ( trim ( $this -> aTProperties [ $sValue ] ) === '' && $this -> aFWithNulls [ $sField ] === true ) {
					$sQueryFields .= 'null' ;
				} else if ( $this -> aFTypes [ $sField ] == 'integer' ) {
					$sQueryFields .= $this -> aTProperties [ $sValue ] ;
				} else
					$sQueryFields .= "'" . preg_replace ( "/'/", "''", $this -> aTProperties [ $sValue ] ) . "'" ;
				$sFieldSep = ", " ;
			}
			
			/*
			// Build the full query concatenating all the pieces together
			*/
			$sQuery .= $sQueryFields . " where " . $this -> sPrimaryKey . " = " . $this -> aTProperties [ $this -> aFProperties [ $this -> sPrimaryKey ] ] ;
			//TLogs\TLog ::  std ( $sQuery ) ;

			/*
			// try to persist this object in a database record
			// Say "WTF" if not able
			*/
			if ( ! $xReturnCode = $this -> xDBConnection -> query ( $sQuery ) )
				throw new \Exception ( "De la merdasse : " . $sQuery ) ;
		} catch ( Exception $e ) { TLogs\TLog :: std ( $e ) ; }

		return ( $this ) ;
	}
}

