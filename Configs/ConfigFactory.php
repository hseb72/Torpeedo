<?php

namespace Torpeedo\Configs ;

class ConfigFactory
{
    public static function load ( $sConfigFile )
    {
		/* check if config file exists and manage the failure message */
		if ( ! is_file ( $sConfigFile ) ) throw new \Exception('Unreachable config file : ' . $sConfigFile ) ;

		$sCfgObject = "\\Torpeedo\\Configs\\" . pathinfo ( $sConfigFile, PATHINFO_EXTENSION ) . "Config" ;

		if ( ! $xLocalCfg = new $sCfgObject ( $sConfigFile ) ) throw ( 'Unavailable config reader : ' . $sCfgObject ) ;

		return ( $xLocalCfg -> load () ) ;
    }
}

?>