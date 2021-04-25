<?php

namespace Torpeedo\Objects ;

use Torpeedo\Logs as TLogs ;

interface TObjectRouting
{
	public function routeget ( $aService, $aData ) ;
	public function routepost ( $aService, $aData ) ;
	public function routeput ( $aService, $aData ) ;
	public function routepatch ( $aService, $aData ) ;
	public function routedelete ( $aService, $aData ) ;
}