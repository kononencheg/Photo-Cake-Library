<?php

namespace PhotoCake\Db\Collection;

use PhotoCake\Db\Collection\CollectionInterface;
use PhotoCake\Db\Record\RecordFactoryInterface;

abstract class AbstractCollection implements CollectionInterface
{
    /**
     * @var \PhotoCake\Db\Record\RecordFactoryInterface
     */
    protected $recordFactory;

    /**
     * @param \PhotoCake\Db\Record\RecordFactoryInterface $factory
     */
    public function setRecordFactory(RecordFactoryInterface $factory)
    {
        $this->recordFactory = $factory;
    }

    /**
     * @return \PhotoCake\Db\Record\RecordInterface
     */
    public function createRecord()
    {
        return $this->recordFactory->create($this->getName());
    }
}
