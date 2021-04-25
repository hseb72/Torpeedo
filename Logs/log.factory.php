<?php

namespace Torpeedo\Logs ;

class LogFactory
{
    public static function init ( $sConfigFile )
    {
		/* check if config file exists and manage the failure message */
		if ( ! is_file ( $sConfigFile ) ) throw new Exception('Unreachable config file : ' . $sConfigFile ) ;

		$sCfgObject = __NAMESPACE__ . pathinfo ( $sConfigFile, PATHINFO_EXTENSION ) . "Config" ;

		if ( ! $xLocalCfg = new $sCfgObject ( $sConfigFile ) ) throw ( 'Unavailable config reader : ' . $sCfgObject ) ;

		return ( $xLocalCfg -> init () ) ;
    }
}