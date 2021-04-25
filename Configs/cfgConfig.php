<?php

namespace Torpeedo\Configs ;

class cfgConfig
{
	var $sFile ;
	
    public function __construct ( $sFile )
    {
		echo "Cfg 1 :<br />" ;
		$this -> sFile = $sFile ;
		echo "Cfg 2 :<br />" ;
	}
	
    public function load ()
    {
		// Implémentation spécifique
        return ( array ( 'Engine' => 'mysql', 'User' => 'Toto', 'Password' => 'litoto' ) ) ;
    }
	
}

?>