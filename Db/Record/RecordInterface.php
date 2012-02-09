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
}
