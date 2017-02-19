<?php

namespace PHP_websocket;

final class Autoloader {

    private static $ds;
    private static $root;

    public static function register()
    {
        self::$ds = DIRECTORY_SEPARATOR;
        self::$root = dirname(__FILE__).self::$ds."..".self::$ds;
        spl_autoload_register(array(__CLASS__, 'autoload'));
    }

    private static function autoload($class)
    {
        $parts = preg_split("#\\\#", $class);
        $className = array_pop($parts);
        require_once self::$root.strtolower(implode(self::$ds, $parts)).self::$ds.$className.'.php';
    }
}
