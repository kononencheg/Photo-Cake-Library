<?php

namespace PhotoCake\Db\Configuration;

use \PhotoCake\Db\Record\RecordFactoryInterface;

abstract class AbstractConfiguration
{
    /**
     * @var \PhotoCake\Db\Collection\CollectionFactoryInterface
     */
    protected $collectionFactory = null;

    /**
     * @var \PhotoCake\Db\Record\RecordFactoryInterface
     */
    protected $recordFactory = null;

    /**
     * @var string
     */
    protected $db = null;

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
