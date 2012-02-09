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
    public function createRecord(array $data)
    {
        $record = $this->recordFactory
                       ->createForCollection($this->getName(), $data);

        $this->prepareRecord($record);

        if ($record !== null) {
            $record->populate($data);
        }

        return $record;
    }

    /**
     * @param \PhotoCake\Db\Record\RecordInterface $record
     */
    protected function prepareRecord($record)
    {

    }
}
