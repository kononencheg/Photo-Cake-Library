<?php

namespace PhotoCake\Db\Record;

interface RecordFactoryInterface
{
    /**
     * @abstract
     * @param string $collection
     * @param array $value
     * @return \PhotoCake\Db\Record\RecordInterface
     */
    function createForCollection($collection, array $value);

    /**
     * @abstract
     * @param string $name
     * @param array $value
     * @return \PhotoCake\Db\Record\RecordInterface
     */
    function createByName($name, array $value);

    /**
     * @param string $name
     * @return boolean
     */
    function isRecordExist($name);
}
