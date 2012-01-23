<?php

namespace PhotoCake\Http;

class Request
{
    /**
     * @var array
     */
    private $source = NULL;

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
    public function get($name = NULL)
    {
        if ($name === NULL) {
            return $this->source;
        } elseif (isset($this->source[$name])) {
            return $this->source[$name];
        }
        
        return NULL;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function fetch($name)
    {
        $result = NULL;

        if (isset($this->source[$name])) {
            $result = $this->source[$name];
            unset($this->source[$name]);
        }

        return $result;
    }

    /**
     * @var \PhotoCake\Http\Request
     */
    private static $instance = NULL;

    /**
     * @static
     * @return \PhotoCake\Http\Request
     */
    public static function getInstance()
    {
        if (self::$instance === NULL) {
            self::$instance = new Request();
        }

        return self::$instance;
    }
}
