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
     * @return RecordFactoryInterface
     */
    public function getRecordFactory()
    {
        return $this->recordFactory;
    }

    /**
     * @return \PhotoCake\Db\Collection\CollectionFactoryInterface
     */
    public function getCollectionFactory()
    {
        if ($this->collectionFactory === null) {
            $this->collectionFactory = $this->createCollectionFactory();
        }

        return $this->collectionFactory;
    }

    /**
     * @return \PhotoCake\Db\Collection\CollectionFactoryInterface
     */
    abstract protected function createCollectionFactory();

    /**
     * @param string $db
     */
    public function setDb($db)
    {
        $this->db = $db;
    }
}
