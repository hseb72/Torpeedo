<?php

namespace Torpeedo\Logs ;

class TLog
{
	private static $dir = "/tmp/" ;
	private static $file = "fucking-log.txt" ;

	public function __construct () {
	}

	public static function init ( $f ) {
		self :: $file = $f ;
	}

	public static function std ( $m ) {
		echo '<pre>' ;
		echo $m ;
		echo '</pre>' ;
	}

	public function flog ($msg) {
		$msg = "[" . date ( "Y-m-d H:i:s" ) . "] " . $msg . "\n" ;
		file_put_contents(self :: $dir . self :: $file, $msg, FILE_APPEND | LOCK_EX);
	}
}
