<?php

namespace Torpeedo\Objects ;

use Torpeedo\Logs as TLogs ;

trait TObject
{
    private $aTConfig ;
    private $aTProperties ;
    private $aTList ;

    public function loadObject ( $sTableFile, $xDatabase = '' )
    {
        try {
            /*
            // Cast the json file content to an array
            */
            $sExtension = strtolower ( pathinfo ( $sTableFile, PATHINFO_EXTENSION ) ) ;

            switch ( $sExtension ) {
                case "json" :
                default :
                    $this -> aTConfig = json_decode ( file_get_contents ( $sTableFile ), true ) ;
            }
        } catch ( Exception $e ) { TLogs\TLog :: std ( $e ) ; }

        /*
        // Get the properties from Config
        */
        foreach ( $this -> aTConfig [ "Properties" ] as $aTProperty ) {
            $this -> aTProperties [ $aTProperty [ 'Name' ] ] = '' ;
        }

        if ( ! empty ( $xDatabase ) ) {
		$this -> loadTable ( $xDatabase ) ;
	}
    }

    public function clean ()
    {
	foreach ( $this -> aTProperties as $sProperty => $sValue )
		$this -> set ( $sProperty, '' ) ;

	$this -> aTList = array () ;

	return ( $oTObject ) ;
    }

    public function get ( $sProperty )
    {
        if ( isset ( $this -> aTProperties [ $sProperty ] ) )
            return ( $this -> aTProperties [ $sProperty ] ) ;
        
        return ( false ) ;
    }

    public function set ( $sProperty, $sValue )
    {
	if ( isset ( $this -> aFProperties [ $sProperty ] ) ) {
		$this -> aTProperties [ $this -> aFProperties [ $sProperty ] ] = $sValue ;
        } else {
		$this -> aTProperties [ $sProperty ] = $sValue ;
        }

        return ( $this ) ;
    }

    public function map ( $xSource, $sStringSourceType = 'json' )
    {
        if ( is_object ( $xSource ) ) $sSourceType = 'object' ;
        else if ( is_array ( $xSource ) ) $sSourceType = 'array' ;
        else $sSourceType = $sStringSourceType ;

        switch ( $sSourceType ) {
            case 'array' :
                $aSource = $xSource ;
                break ;
            case 'object' :
                $aSource = $xSource -> getProperties () ;
                break ;
            case 'json' :
                $aSource = json_decode ( $xSource ) ;
                break ;
            default : 
                $aSource = null;
        }

	foreach ( $aSource as $sProperty => $sValue ) {
		$this -> set ( $sProperty, $sValue ) ;
	}
        
        return ( $this ) ;
    }

    public function mapList ( $xSource, $sStringSourceType = 'json' )
    {
        if ( is_object ( $xSource ) ) $sSourceType = 'object' ;
        else if ( is_array ( $xSource ) ) $sSourceType = 'array' ;
        else $sSourceType = $sStringSourceType ;

        switch ( $sSourceType ) {
            case 'array' :
                $aSource = $xSource ;
                break ;
            case 'object' :
                $aSource = $xSource -> getList() ;
                break ;
            case 'json' :
                $aSource = json_decode ( $xSource ) ;
                break ;
            default : 
                $aSource = null;
        }

	$sLocal = "\\soredi\\Persistence\\" . $this -> aTConfig [ 'ObjectName' ] ;
	$oLocal = new $sLocal ( $this -> xDBConnection ) ;

	foreach ( $aSource as $iKey => $oSource ) {
		$oLocal -> map ( $oSource -> getProperties () ) ;
		$this -> aTList [] = clone ($oLocal) ;
	}

        return ( $this ) ;
    }

    public function castinto ( $oTObject )
    {
	foreach ( $this -> aTProperties as $sProperty => $sValue )
		$oTObject -> set ( $sProperty, $sValue ) ;

	return ( $oTObject ) ;
    }

    public function addToList ( $oTObject )
    {
        $this -> aTList [] = clone ( $oTObject ) ;
    }

    public function getFirst ()
    {
	if ( count ( $this -> aTList ) != 0 )
		$this -> aTList [0] -> castinto ( $this ) ;
		
	return ( $this ) ;
    }

    public function getLast ()
    {
	if ( count ( $this -> aTList ) != 0 )
		$this -> aTList [count ( $this -> aTList )] -> castinto ( $this ) ;
		
	return ( $this ) ;
    }

    public function getList ()
    {
	return ( $this -> aTList ) ;
    }

    public function getProperties ()
    {
	return ( $this -> aTProperties ) ;
    }

    public function displaylist ( $sMode = '' )
    {
	$aObjects = Array() ;
	foreach ( $this -> aTList as $iKey => $oLocal ) {
		$aObjects [] = $oLocal -> display ( $sMode, false ) ;
	}

        switch ( strtolower ( $sMode ) ) {
            case 'array' :
                print ( "<pre>" ) ;
                print_r ( $aObjects ) ;
                print ( "</pre>" ) ;
                break ;
            case 'json' :
            case 'json-educated' :
            case 'json-utf8' :
//				$aJSON = [ "List" => $this -> sName, "Objects" => $aObjects ] ; 
//				$this -> setHeaders() ;
				print ( json_encode ($aObjects, JSON_PRETTY_PRINT ) ) ;
			break ;
		}
    }

    public function display ( $sMode = '', $iAlone = true )
    {
        switch ( strtolower ( $sMode ) ) {
            case 'array' :
                if ( $iAlone ) {
					print ( "<pre>" ) ;
					print_r ( $this -> aTProperties ) ;
					print ( "</pre>" ) ;
                } else {
                    return ( $this -> aTProperties ) ;
                }
                break ;
            case 'json' :
            case 'json-educated' : 
				$aJSON = [ "Object" => $this -> sName, "Properties" => $this -> aTProperties ] ;
                if ( $iAlone ) {
                    print ( json_encode ( $aJSON , JSON_PRETTY_PRINT ) ) ;
                } else {
                    return ( $aJSON ) ;
                }
                break ;
            case 'json-utf8' :
		//$aJSON = [ "Object" => $this -> sName, "Properties" => array_map ( "utf8_encode", $this -> aTProperties) ] ;
		if ( ! isset ( $this -> xDBConnection ) || !preg_match ( '/^utf8.*/', $this -> xDBConnection -> getEncoding () ) )
			$aJSON = array_map ( "utf8_encode", $this -> aTProperties ) ;
		else
			$aJSON = $this -> aTProperties ;

                if ( $iAlone ) {
//			$this -> setHeaders() ;
			print ( json_encode ( $aJSON , JSON_PRETTY_PRINT ) ) ;
                } else {
                    return ( $aJSON ) ;
                }
                break ;
            case "rude" :
                print ( "<pre>" ) ;
                foreach ( $this -> aTProperties as $sProperty => $sValue )
                    echo $sProperty . " = " . $sValue . "<br />" ;
                print ( "</pre>" ) ;
                break ;
            default :
                print ( "<pre>" ) ;
                foreach ( $this -> aTProperties as $sProperty => $sValue )
                    echo $sProperty . " = " . utf8_encode ( $sValue ) . "<br />" ;
                print ( "</pre>" ) ;
        }

        return ( $this ) ;
    }
}
