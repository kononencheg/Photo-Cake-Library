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
     * @param string|null $collection
     * @return array
     */
    public function insertSerialize($collection = null);

    /**
     * Specify data which should be stored in data base.
     *
     * @abstract
     * @param string $collection
     * @return array
     */
    public function updateSerialize($collection = null);

    /**
     * Specify data which should be encoded to JSON.
     *
     * @abstract
     * @return mixed
     */
    public function jsonSerialize();
}
