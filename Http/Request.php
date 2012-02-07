<?php

namespace PhotoCake\Http;

class Request
{
    /**
     * @var array
     */
    private $source = null;

    /**
     *
     */
    private function __construct()
    {
        $this->source = array_merge($_GET, $_POST);
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function get($name = null)
    {
        if ($name === null) {
            return $this->source;
        } elseif (isset($this->source[$name])) {
            return $this->source[$name];
        }
        
        return null;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function fetch($name)
    {
        $result = null;

        if (isset($this->source[$name])) {
            $result = $this->source[$name];
            unset($this->source[$name]);
        }

        return $result;
    }

    /**
     * @var \PhotoCake\Http\Request
     */
    private static $instance = null;

    /**
     * @static
     * @return \PhotoCake\Http\Request
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new Request();
        }

        return self::$instance;
    }
}
