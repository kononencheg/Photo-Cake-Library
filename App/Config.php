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
    public static function getApplicationEnv()
    {
        return $_SERVER['APPLICATION_ENV'];
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
        $config = self::parseFile($file);
        $env = $_SERVER['APPLICATION_ENV'];

        self::getInstance()->config = $config[$env];
    }

    public static function parseFile($file)
    {
        $result = array();

        $ini = parse_ini_file($file, true);
        foreach($ini as $namespace => $properties) {
            $parsedNamespace = explode(':', $namespace);

            $name = array_shift($parsedNamespace);
            if(!isset($config[$name])) {
                $result[$name] = array();
            }

            $extends = array_shift($parsedNamespace);
            if($extends !== null && isset($ini[$extends])) {
                foreach($ini[$extends] as $prop => $val) {
                    $result[$name][$prop] = $val;
                }
            }

            foreach($properties as $prop => $val) {
                $result[$name][$prop] = $val;
            }
        }

        return $result;
    }
}
