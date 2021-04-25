<?php

namespace Torpeedo\Configs ;

class jsonConfig
{
	var $sFile ;
	
    public function __construct ( $sFile )
    {
		$this -> sFile = $sFile ;
	}
	
    public function load ()
    {
		// Implémentation spécifique
		// File must contain an array named $aConfig
		$aConfig = json_decode ( file_get_contents ( $this -> sFile ), true ) ;

        return ( $aConfig ) ;
    }
}

?>