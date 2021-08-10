<?php

 namespace Torpeedo\Databases ;
 
 class DatabaseFactory
{
    public static function open ( $sConfigFile )
    {
		/* load config file and get database properties in an array */
		if ( ! $aDBConfigArray = \Torpeedo\Configs\ConfigFactory :: load ( $sConfigFile ) ) throw ( 'Unavailable config file : ' . $sConfigFile ) ;

		$sDBObject = '\\' . __NAMESPACE__ . '\\' . $aDBConfigArray [ 'Engine' ] . "Database" ;

		if ( ! $xLocalDB = new $sDBObject ( $aDBConfigArray ) ) throw ( 'Unavailable database engine : ' . $aDBConfigArray [ 'Engine' ] ) ;

		return ( $xLocalDB ) ;
    }
}