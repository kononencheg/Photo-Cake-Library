<?php

namespace PhotoCake\App;

class Config
{
    /**
     * @var array
     */
    private $config = NULL;
    
    private function __construct() {}

    /**
     * @var \PhotoCake\App\Config
     */
    private static $instance = NULL;

    /**
     * @static
     * @return \PhotoCake\App\Config
     */
    private static function getInstance()
    {
        if (self::$instance === NULL) {
            self::$instance = new Config();
        }

        return self::$instance;
    }


    public static function get($name)
    {
        return self::getInstance()->config[$name];
    }

    public static function load($file)
    {
        $config = parse_ini_file($file, true);
        $env = $_SERVER['APPLICATION_ENV'];

        self::getInstance()->config = $config[$env];
    }

}
