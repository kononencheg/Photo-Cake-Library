<?php

namespace PhotoCake\Db\Record;

abstract class AbstractRecord implements RecordInterface
{
    /**
     * @var string
     */
    protected $collection = '';

    /**
     * @var array
     */
    protected $fields = array();

    /**
     * @var array
     */
    protected $data = array();


    /**
     * @abstract
     * @param string $name
     * @param mixed $value
     */
    abstract protected function set($name, $value);

    /**
     * @abstract
     * @param string $name
     */
    abstract protected function get($name);

    /**
     * @abstract
     * @param string $name
     * @param mixed $value
     */
    abstract protected function add($name, $value);

    /**
     * @abstract
     * @param string $name
     * @param mixed $value
     */
    abstract protected function remove($name, $value);
}
