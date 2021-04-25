<?php

namespace Torpeedo\Logs ;

class TLog
{
	public function __construct () {
	}

	public static function init ( $f ) {
			if ( file_exists ( $f ) ) return ( true ) ;
	}

	public static function std ( $m ) {
		echo '<pre>' ;
		echo $m ;
		echo '</pre>' ;
	}
}