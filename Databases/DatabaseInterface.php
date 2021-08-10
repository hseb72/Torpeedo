<?php

namespace Torpeedo\Databases ;

interface DatabaseInterface
{
    public function connect () ;
    public function disconnect () ;
    public function query( $sQuery ) ;
    public function lastInsertedId () ;
    public function getEncoding () ;
}
