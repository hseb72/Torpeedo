<?php

namespace Torpeedo\Rest ;

class Rest
{
	public static $app ;
	public static $xDB ;
	
	public function init ()
	{
	}
	
	public static function route ( $app, $xDB )
	{
		self :: $app = $app ;
		self :: $xDB = $xDB ;
		
		$sServiceLine = preg_replace ( '!/*$!', '', $_REQUEST [ 'q' ] ) ;
		
		$aService = explode ( '/', $sServiceLine ) ;

		if ( count ( $aService ) < 1 ) { echo "Fin des haricots !" ; return (false) ; }

		$sMethod = strtolower ( $_SERVER['REQUEST_METHOD'] ) ;

		switch ( $sMethod ) {
			case "get" :
			case "delete" :
				$aData = '' ;
				break ;
			case "put" :
			case "post" :
			case "patch" :
				$aData = json_decode(file_get_contents("php://input"), true);
				break ;
			default :
				echo "Pouah ! Unknown Rest Verb." ;
				return ( false ) ;
		}

		if ( ! $sService = $aService [ 0 ] ) return (false ) ;

		$sServiceClass = self :: $app . "\\Persistence\\" . $sService ;

		if ( ! $oU = new $sServiceClass ( self :: $xDB ) ) return ( false ) ;

		$sRouteMethod = "route" . $sMethod ;
		return ( $oU -> $sRouteMethod ( $aService, $aData ) ) ;
	}

	private static function put ( $aService, $sData )
	{
		if ( $sService = $aService [ 0 ] ) {
			$sServiceClass = self :: $app . "\\Persistence\\" . $sService ;
			$oU = new $sServiceClass ( self :: $xDB ) ;
			$oU -> select ( $aService [ 1 ] ) ;
		} else { throw ( new \Exception ( 'No service requested.' ) ) ; return (false) ; }
	}

	private static function get ( $aService, $sData )
	{
		if ( $sService = $aService [ 0 ] ) {
			$sServiceClass = self :: $app . "\\Persistence\\" . $sService ;
			$oU = new $sServiceClass ( self :: $xDB ) ;
			$oU -> select ( $aService [ 1 ] ) -> display () ;
		} else { throw ( new \Exception ( 'No service requested.' ) ) ; return (false) ; }
	}

	private static function post ( $aService, $sData )
	{
		if ( $sService = $aService [ 0 ] ) {
			$sServiceClass = self :: $app . "\\Persistence\\" . $sService ;
			$oU = new $sServiceClass ( self :: $xDB ) ;
			$oU -> select ( $aService [ 1 ] ) ;
		} else { throw ( new \Exception ( 'No service requested.' ) ) ; return (false) ; }
	}

	private static function patch ( $aService, $sData )
	{
		if ( $sService = $aService [ 0 ] ) {
			$sServiceClass = self :: $app . "\\Persistence\\" . $sService ;
			$oU = new $sServiceClass ( self :: $xDB ) ;
			$oU -> select ( $aService [ 1 ] ) ;
		} else { throw ( new \Exception ( 'No service requested.' ) ) ; return (false) ; }
	}
	
	private static function delete ( $aService, $sData )
	{
		if ( $sService = $aService [ 0 ] ) {
			$sServiceClass = self :: $app . "\\Persistence\\" . $sService ;
			$oU = new $sServiceClass ( self :: $xDB ) ;
			$oU -> select ( $aService [ 1 ] ) ;
		} else { throw ( new \Exception ( 'No service requested.' ) ) ; return (false) ; }
	}
}
