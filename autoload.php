<?php

function __autoload( $sClassName )
{
	$sLocalFile = preg_replace ( "/\\\\/", "/", $sClassName ) ;

	$sClassSName = basename ($sLocalFile) ;

    include( $sLocalFile . '.php' ) ;

    // Vérifie si l'include a déclaré la classe
    if ( ! class_exists ($sClassName) && ! interface_exists ($sClassName) && ! trait_exists ($sClassName) ) {
        throw new Exception ("Unable to load the class : $sLocalFile" ) ;
    }
}
