<?php

namespace PhotoCake\Db\Record;

interface RecordFactoryInterface
{
    /**
     * @abstract
     * @param string $collection
     * @return \PhotoCake\Db\Record\RecordInterface
     */
    function create($collection);
}
