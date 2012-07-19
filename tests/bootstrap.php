<?php
require_once  __DIR__ . '/../vendor/autoload.php';

function autoload( $rootDir ) {
    spl_autoload_register(function( $className ) use ( $rootDir ) {        
        $file = sprintf(
            '%s/%s.php',
            $rootDir,
            str_replace( '\\', '/', $className )
        );        
        
        if ( file_exists($file) ) {
            require $file;
        }
    });
}

autoload( '/usr/share/php' );
autoload( __DIR__ . '/');
autoload( __DIR__ . '/unit/');
autoload( __DIR__ . '/functional/');