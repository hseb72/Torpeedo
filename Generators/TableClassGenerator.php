<?php

namespace Torpeedo\Generators ;

use \Torpeedo\Logs as Tlogs ;

class TableClassGenerator
{
	const ConfDir = '/conf/' ;
	const PersistDir = '/persistence/' ;
	const TemplateDir = __DIR__ . '/../Templates/' ;
	const TemplateFile = 'torpeedo-class-template.php' ;
	
	public static function build ( $xDB, $sClasses, $sAppName = '' )
	{
		if ( ! $aClasses = json_decode ( $sClasses, true ) ) { throw ( new Exception ( "Probable erreur dans le json des classes à construire." ) ) ; }
		try {
			foreach ( $aClasses [ 'Classes' ] as $iCnt => $aClass ) {
				$sClassName = strtolower($aClass['Class']);
				$sTableName = $aClass['Table'] ;
				
				self :: buildConfig ( $xDB, $sTableName, $sClassName, $sAppName ) ;
				self :: buildClass ( $sClassName, $sAppName ) ;
			}
		} catch ( \Exception $e ) { TLogs\TLog :: std ( $e ) ; }
	}

	public static function test ( $xDB, $sClasses, $sAppName = '' )
	{
		if ( ! $aClasses = json_decode ( $sClasses, true ) ) { throw ( new Exception ( "Probable erreur dans le json des classes à construire." ) ) ; }
		try {
			foreach ( $aClasses [ 'Classes' ] as $iCnt => $aClass ) {
				$sClass = $sAppName . "\\persistence\\" . $aClass['Class'] ;
				$oTest = new $sClass($xDB) ;
				$oTest -> exists (1) && $oTest -> select (1) ;
				$oTest -> display ( 'json-utf8' ) ;
			}
		} catch ( \Exception $e ) { TLogs\TLog :: std ( $e ) ; }
	}

	private static function buildConfig ( $xDB, $sTableName, $sClassName, $sAppName = '' )
	{
		if ( empty ( $sAppName ) ) $sConfDir = '.' . self :: ConfDir ;
		else $sConfDir = $sAppName . self :: ConfDir ;

		if ( ! $sJson = $xDB -> getTableStructure ( $sTableName, USE_TORPEEDO_NOTATION ) ) {
			throw ( \Exception ( 'Unable to load table structure from database' ) ) ;
			return ( false ) ;
		}
		
		if ( ! is_dir ( $sConfDir ) ) {
			throw ( new \Exception ( 'Unable to find conf directory (' . $sConfDir . ')' ) ) ;
			return ( false ) ;
		}

		if ( ! $f = fopen ( $sConfDir . $sClassName . '.json', 'w+' ) ) {
			throw ( new \Exception ( 'Unable to open conf file for saving the table structure (' . $sConfDir . $sClassName . ')' ) ) ;
			return ( false ) ;
		}

		if ( ! fwrite ( $f, $sJson, strlen ( $sJson ) ) ) {
			throw ( new \Exception ( 'Unable to save the table structure in the conf file  (' . $sConfDir . $sClassName . ')' ) ) ;
		}

		fclose ( $f ) ;
		
		return ( true ) ;
	}

	private static function buildClass ( $sClassName, $sAppName )
	{
		$sCaps =  ucfirst($sClassName) ;

		if ( ! $sPhp = file_get_contents ( self :: TemplateDir . self :: TemplateFile ) ) {
			throw ( new \Exception ( 'Unable to load class template from directory' ) ) ;
			return ( false ) ;
		}

		$sPhp = preg_replace ( '/__ClassName__/', $sClassName, preg_replace ( '/__ClassNameCaps__/', $sCaps, preg_replace ( '/__AppName__/', $sAppName, $sPhp ) ) ) ;
		
		if ( $f = fopen ( $sAppName . self :: PersistDir . $sClassName . ".php", "w+" ) ) {
			fwrite ( $f, $sPhp, strlen ( $sPhp ) ) ;
			fclose ( $f ) ;
		}
	}
}