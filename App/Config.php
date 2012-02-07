<?php

namespace PhotoCake\App;

class Config
{
    /**
     * @var array
     */
    private $config = null;

    private function __construct() {}

    /**
     * @var \PhotoCake\App\Config
     */
    private static $instance = null;

    /**
     * @static
     * @return \PhotoCake\App\Config
     */
    private static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new Config();
        }

        return self::$instance;
    }

    /**
     * @static
     * @param string $name
     * @return mixed
     */
    public static function get($name)
    {
        return self::getInstance()->config[$name];
    }

    /**
     * @static
     * @param string $file
     */
    public static function load($file)
    {
        $config = parse_ini_file($file, true);
        $env = $_SERVER['APPLICATION_ENV'];

        self::getInstance()->config = $config[$env];
    }

}
