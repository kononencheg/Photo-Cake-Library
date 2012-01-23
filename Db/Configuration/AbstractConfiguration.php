<?php

namespace PhotoCake\Db\Configuration;

use \PhotoCake\Db\Record\RecordFactoryInterface;

abstract class AbstractConfiguration
{
    /**
     * @var \PhotoCake\Db\Collection\CollectionFactoryInterface
     */
    protected $collectionFactory = NULL;

    /**
     * @var \PhotoCake\Db\Record\RecordFactoryInterface
     */
    protected $recordFactory = NULL;

    /**
     * @var string
     */
    protected $db = NULL;

    /**
     * @param \PhotoCake\Db\Record\RecordFactoryInterface $recordFactory
     */
    public function setRecordFactory(RecordFactoryInterface $recordFactory)
    {
        $this->recordFactory = $recordFactory;
    }

    /**
     * @return \PhotoCake\Db\Collection\CollectionFactoryInterface
     */
    abstract public function getCollectionFactory();

    /**
     * @param string $db
     */
    public function setDb($db)
    {
        $this->db = $db;
    }
}
