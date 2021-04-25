<?php

namespace __AppName__\Persistence ;

use Torpeedo\Objects as TO ;
use Torpeedo\Databases as DB ;
use Torpeedo\Logs as TL ;
use Torpeedo\Http as TH ;
use __AppName__\Persistence as __AppName__ ;

class __ClassNameCaps__ implements TO\TObjectRouting
{
	use TO\TObject ;
	use DB\TTable ;
	__OuterObjectTraitUsage__

	private $session ;

	public function __construct ($xDatabase = '')
	{
		$this -> loadObject ("__AppName__/conf/__ClassName__.json" ) ;
		if ( ! empty ( $xDatabase ) ) {
			$this -> loadTable ($xDatabase) ;
			$this -> session = new session ( $xDatabase ) ;
			$this -> session -> detect () ;

			TL\TLog :: flog ( $this -> session -> printable () ) ;
			if ( ! $this -> session -> get ( 'wizard' ) ) { TH\THttp :: unauthorized () ; exit (0) ; }
		}
	}
}