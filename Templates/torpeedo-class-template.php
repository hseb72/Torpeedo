<?php

namespace __AppName__\Persistence ;

use Torpeedo\Databases as DB ;

class __ClassNameCaps__ extends DB\Table
{
	public function __construct ($xDatabase)
	{
		parent::__construct('__ClassName__', $xDatabase, "__AppName__/conf") ;
	}
}