<?php

namespace Torpeedo\Http ;

// Maybe this class name should be THttpError
// and send should be throwError
// This mean a lot to change...

class THttp
{
	// code should contain the whole data Code, Method, Error Title & Error Message
	// Then format would just take eventually a path as a parameter and maybe an alternative message
	// private static $codes = [ 
		// "200" => [ "error" => "OK", "method" => "success", "message" => "The resource has correctly been delivered" ],
		// "201" => [ "error" => "Created", "method" => "Torpeedo.Http.created", "message" => "The resource has correctly been created" ]
	// ] ;

	private static $code = [ "200" => "OK",
					 "201" => "Created",
					 "204" => "No Content",
					 "400" => "Bad Request",
					 "401" => "Unauthorized",
					 "403" => "Forbidden",
					 "404" => "Not Found",
					 "405" => "Method Not Allowed",
					 "407" => "Proxy authentication Required",
					 "418" => "I'm a teapot",
					 "500" => "Internal Server Error"
	] ;


	public function __construct () {
	}

	public static function created ( $path = '/', $altmsg = '' ) {
		$msg = self :: format ( "Torpeedo.http.created", 201, $path, "The resource has correctly been created" ) ;
		self :: send ( 201, $msg ) ;
		// self :: throwError ( 201, self :: format ( 201, $path, $altmsg ) ) ;
	}

	public static function noContent ( $path = '/' ) {
		$msg = self :: format ( "Torpeedo.http.noContent", 204, $path, "You requested a content you\'re not supposed to get" ) ;
		self :: send ( 204, $msg ) ;
	}

	public static function unauthorized ( $path = '/' ) {
		$msg = self :: format ( "Torpeedo.http.unauthorized", 401, $path, "The Token from Social Network Provider is invalid" ) ;
		self :: send ( 401, $msg ) ;
	}

	public static function forbidden ( $path = '/' ) {
		$msg = self :: format ( "Torpeedo.http.forbidden", 403, $path, "You requested a content you\'re not supposed to get" ) ;
		self :: send ( 403, $msg ) ;
	}

	public static function notFound ( $path = '/' ) {
		$msg = self :: format ( "Torpeedo.http.notFound", 404, $path, "The resource you required has not been found" ) ;
		self :: send ( 404, $msg ) ;
	}

	public static function methodNotAllowed ( $path = '/' ) {
		$msg = self :: format ( "Torpeedo.http.methodNotAllowed", 405, $path, "You requested a method you\'re not supposed to get" ) ;
		self :: send ( 405, $msg ) ;
	}

	public static function teaPot ( $path = '/' ) {
		$msg = self :: format ( "Torpeedo.http.teaPot", 405, $path, "You requested a method I\'ve not able to provide" ) ;
		self :: send ( 405, $msg ) ;
	}

	// public static function format ( $status, $path, $altmsg ) {}
	// use self :: $code [ $status ] [ 'method' ]
	// and self :: $code [ $status ] [ 'error' ]
	// and self :: $code [ $status ] [ 'message' ] || $altmsg
	public static function format ( $ex, $st, $pa, $msg ) {
		$msg = '{
	"error": {
	   "timestamp" : ' . date ( 'U' ) . ',
	   "exception" : "' . $ex . '",
	   "status" : ' . $st . ',
	   "error" : "' . self :: $code [ $st ] . '",
	   "path" : "' . $pa . '",
	   "message" : "' . $msg . '"
	}
}';

		self :: send ( 403, $msg ) ;
	}

	public static function send ( $cod, $msg ) {
		if ( self :: $code [ $cod ] ) {
			header ( 'HTTP/1.0 ' . $cod . ' ' . self :: $code [ $cod ] ) ;
			header("Content-type: application/json; charset=UTF-8") ;
			echo $msg ;
		} else {
			self :: send ( 500, "HTTP Code innerly not implemented" ) ;
		}
	}

	/* should not be used */
	/* to delete */
	public static function sendError ( $cod, $msg ) {
		self :: send ( $cod, $msg ) ;
	}
}
