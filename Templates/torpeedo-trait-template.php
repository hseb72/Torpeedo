<?php

namespace MTGWars\Persistence ;

use Torpeedo\Objects as TO ;
use Torpeedo\Databases as DB ;
use Torpeedo\Logs as TL ;
use Torpeedo\Http as TH ;

trait __ClassNameCaps__Methods
{
	private function isRelatedToSessionOwner ( )
	{
		return ( true ) ;
		
		/*
		// See example below
		//
		$iWizId = $this -> session -> get ( 'wizard' ) ;

		$oDeck = new deck ( $this -> xDBConnection ) ;
		$oDeck -> select ( $this -> get ( 'deck' ) ) ;

 		$oOpp = new opponent ( $this -> xDBConnection ) ;
		$oOpp -> select ( $oDeck -> get ( 'opponent' ) ) ;

		// I only can delete the card if i am its owner *
		if ( $oOpp -> get ('wizard' ) == $iWizId ) {
			$this -> set ( "Done", "But I shoudn't have" ) ;
		}
		
		* End of example
		*/
	}

	private function kickUnauthorizedRequest ( )
	{
		if ( ! $this -> isRelatedToSessionOwner () ) {
			TH\THttp :: forbidden () ;
			exit (0) ;
		}
	}

	public function getAllRecords ( )
	{
		/*
		// Should be replaced by a filtered query
		// Where only authorized record are given
		*/
		$this -> all () ;

		if ( count ( $this -> getList() ) == 0 ) TH\THttp :: noContent () ; exit (0) ;
		
		return ( $this ) ;
	}

	public function getRecord ( $iId )
	{
		/*
		// Should be replaced by a filtered query
		// Where only authorized record are given
		*/
		$this -> select ( $iId ) ;
		
		if ( $this -> isRelatedToSessionOwner() ) return ( $this ) ;
		else TH\THttp :: forbidden () ; exit (0) ; 
	}

	public function routeget ( $aService, $aData = null)
	{
		switch ( count ( $aService ) ) {
			case 1 :
				$this -> getAllRecords () -> displaylist ( 'json-utf8' ) ;
				break ;
			case 2 :
				$this -> getRecord ( $aService [1] ) -> display ( 'json-utf8') ;
				break ;
			case 3 : 
				switch ( $aService [1] ) {
					case 'first' :
						$this -> first ( $aService [2] ) -> displaylist ( 'json-utf8' ) ;
						break ;
				}
				break ;
			default :
				TH\THttp :: teapot () ;
				exit (0) ; 
		}
	}

	public function routepost ( $aService, $aData )
	{
		TH\THttp :: methodNotAllowed () ;
		exit (0) ; 

	}
	
	public function put ( $aData ) {
		foreach ( $aData as $sKey => $sValue ) {
			$this -> set ( $sKey, $sValue ) ;
		}
		
		/* check for data ownership before doing anything */
		if ( $this -> get ( 'id' ) && $this -> exists ( $this -> get ( 'id' ) ) )
			$this -> kickUnauthorizedRequest () ;
			$this -> update () ;
		}
		/* check for data AUTHORIZATIONS before doing anything */

		$this -> insert () ;
		return ( $this ) ;
	}
	
	public function routepatch ( $aService, $aData ) {
		/* check for data ownership before doing anything */
		switch ( count ( $aService ) ) {
			case 2 :
				$this -> select ( $aService [1] )
					  -> patch ( $aData )
					  -> display ( 'json-utf8' ) ;
						break ;
				break ;
			default :
				TH\THttp :: teapot () ;
				exit (0) ; 
				break ;
		}
	}
	
	public function routedelete ( $aService, $aData ) {
		$this -> select ( $aService [1] ) ;

		/* check for data existence before doing anything */
		if ( ! $this -> get ( 'id' ) ) { TH\THttp :: notFound () ; exit () ; }
			
		/* check for data ownership before doing anything */
		$this -> kickUnauthorizedRequest () ;

		$this -> delete () ;
		$this -> set ( 'id', '' ) ;

		return ( $this -> display ( 'json-utf8' ) ) ;
	}

	public function patch ( $aData )
	{
		/* check for data existence before doing anything */
		if ( ! $this -> get ( 'id' ) ) { TH\THttp :: notFound () ; exit () ; }

		foreach ( $aData as $sKey => $sValue ) {
			$this -> set ( $sKey, utf8_decode($sValue) ) ;
		}

		/* check for data ownership before doing anything */
		$this -> kickUnauthorizedRequest () ;

		$this -> update () ;
		return ( $this ) ;
	}
}