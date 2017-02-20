<?php

namespace php_websocket;
/**
* @psr psr-4 namespace & autoload
*/
final class Autoloader {

    private static $_ds;
    private static $_root;

    public static function register()
    {
        self::$_ds = DIRECTORY_SEPARATOR;
        self::$_root = dirname(__FILE__).self::$_ds."..".self::$_ds;
        spl_autoload_register(array(__CLASS__, 'autoload'));
    }

    private static function autoload($class)
    {
        $parts = preg_split("#\\\#", $class);
        $className = array_pop($parts);
        require_once self::$_root.strtolower(implode(self::$_ds, $parts)).self::$_ds.$className.'.php';
    }
}
