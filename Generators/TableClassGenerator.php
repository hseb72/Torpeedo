<?php

namespace Torpeedo\Generators ;

use \Torpeedo\Logs as Tlogs ;

class TableClassGenerator
{
	const ConfDir = '/conf/' ;
	const PersistDir = '/Persistence/' ;
	const TemplateDir = '/../Templates/' ;
	const ClassTemplate = 'torpeedo-class-template.php' ;
	const TraitTemplate = 'torpeedo-trait-template.php' ;

	public static function build ( $xDB, $sClasses, $sAppName = '' )
	{
		if ( ! $aClasses = json_decode ( $sClasses, true ) ) { throw ( new Exception ( "Probable erreur dans le json des classes à construire." ) ) ; }
		try {
			foreach ( $aClasses [ 'Classes' ] as $iCnt => $aClass ) {
				$sClassName = strtolower($aClass['Class']);
				$sTableName = $aClass['Table'] ;

				self :: buildConfig ( $xDB, $sTableName, $sClassName, $sAppName ) ;
				self :: buildTrait ( $sClassName, $sAppName ) ;
				self :: buildClass ( $sClassName, $sAppName ) ;
			}
		} catch ( \Exception $e ) { TLogs\TLog :: std ( $e ) ; }
	}

	public static function test ( $xDB, $sClasses, $sAppName = '' )
	{
		if ( ! $aClasses = json_decode ( $sClasses, true ) ) { throw ( new Exception ( "Probable erreur dans le json des classes à construire." ) ) ; }
		try {
			foreach ( $aClasses [ 'Classes' ] as $iCnt => $aClass ) {
				$sClassName = strtolower($aClass['Class']);

				$sClass = $sAppName . "\\" . preg_replace ('!/!', '', self :: PersistDir) . "\\" . $sClassName ;

				$oTest = new $sClass($xDB) ;

				$oTest -> first (10) ;

				$oTest -> displayList ( 'json-utf8' ) ;
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

		if ( ! $sPhp = file_get_contents ( __DIR__ . self :: TemplateDir . self :: ClassTemplate ) ) {
			throw ( new \Exception ( 'Unable to load class template from directory' ) ) ;
			return ( false ) ;
		}

		$sPhp = preg_replace ( '/__ClassName__/', $sClassName, preg_replace ( '/__ClassNameCaps__/', $sCaps, preg_replace ( '/__AppName__/', $sAppName, $sPhp ) ) ) ;
		
		if ( is_file ( $sAppName . self :: PersistDir . $sClassName . 'Methods.php' ) )
			$sPhp = preg_replace ( '/__OuterObjectTraitUsage__/', 'use ' . $sClassName . 'Methods ;', $sPhp ) ;
		else
			$sPhp = preg_replace ( '/__OuterObjectTraitUsage__/', '', $sPhp ) ;
		
		if ( $f = fopen ( $sAppName . self :: PersistDir . $sClassName . ".php", "w+" ) ) {
			fwrite ( $f, $sPhp, strlen ( $sPhp ) ) ;
			fclose ( $f ) ;
		}
	}

	private static function buildTrait ( $sClassName, $sAppName )
	{
		$sCaps =  ucfirst($sClassName) ;

		$sTraitFile = $sAppName . self :: PersistDir . $sClassName . "Methods.php" ;

		if ( file_exists ( $sTraitFile ) ) {
			echo "Trait already exists. To replace it with a new empty template, remove it manually first (" . $sTraitFile . ").<br />\n" ;
			return ( false ) ;
		}

		if ( ! $sPhp = file_get_contents ( __DIR__ . self :: TemplateDir . self :: TraitTemplate ) ) {
			throw ( new \Exception ( 'Unable to load class template from directory' ) ) ;
			return ( false ) ;
		}

		$sPhp = preg_replace ( '/__ClassName__/', $sClassName, preg_replace ( '/__ClassNameCaps__/', $sCaps, preg_replace ( '/__AppName__/', $sAppName, $sPhp ) ) ) ;

		if ( $f = fopen ( $sTraitFile, "w+" ) ) {
			fwrite ( $f, $sPhp, strlen ( $sPhp ) ) ;
			fclose ( $f ) ;
		}
	}
}