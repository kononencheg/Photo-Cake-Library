<?php

namespace PhotoCake\Db\Record;

interface RecordInterface
{
    /**
     * @abstract
     * @return string
     */
    public function getId();

    /**
     * @abstract
     * @param mixed $data
     */
    public function populate(array $data);

    /**
     * Specify data which should be stored in data base.
     *
     * @abstract
     * @return array
     */
    public function dbSerialize();

    /**
     * Specify data which should be encoded to JSON.
     *
     * @abstract
     * @return mixed
     */
    public function jsonSerialize();

    /**
     * @abstract
     * @param string $name
     * @param mixed $value
     */
    public function set($name, $value);

    /**
     * @abstract
     * @param string $name
     */
    public function get($name);

    /**
     * @abstract
     * @param string $name
     * @param mixed $value
     */
    public function add($name, $value);

    /**
     * @abstract
     * @param string $name
     * @param mixed $value
     */
    public function remove($name, $value);
}
