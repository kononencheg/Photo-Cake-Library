<?php

namespace PhotoCake\Db\Record;

interface RecordFactoryInterface
{
    /**
     * @abstract
     * @param string $collectionName
     * @return \PhotoCake\Db\Record\RecordInterface
     */
    function create($collectionName);
}
