<?php

class Log
{
	var $sFile ;
	
    public function __construct ( $sFile )
    {
		echo "Log 1 :<br />" ;
		$this -> sFile = $sFile ;
		echo "Log 2 :<br />" ;
	}
	
    public function init ()
    {
		// Implémentation spécifique
        return ( array ( 'Engine' => 'mysql', 'User' => 'Toto', 'Password' => 'litoto' ) ) ;
    }
	
}

?>