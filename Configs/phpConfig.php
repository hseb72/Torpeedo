<?php

namespace Torpeedo\Configs ;

class phpConfig
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
		// File must contain an array named $aConfig
		include ( $this -> sFile ) ;

        return ( $aConfig ) ;
    }
	
}

?>