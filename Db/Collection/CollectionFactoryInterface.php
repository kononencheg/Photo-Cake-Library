<?php

namespace PhotoCake\Db\Collection;

use \PhotoCake\Db\Record\RecordFactoryInterface;

interface CollectionFactoryInterface
{
    /**
     * @abstract
     * @param string $name
     * @return \PhotoCake\Db\Mongo\MongoCollection
     */
    function create($name);

    /**
     * @abstract
     * @param \PhotoCake\Db\Record\RecordFactoryInterface $recordFactory
     */
    function setRecordFactory(RecordFactoryInterface $recordFactory);
}
