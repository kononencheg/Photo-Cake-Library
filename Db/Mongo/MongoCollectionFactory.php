<?php

namespace PhotoCake\Db\Mongo;

use PhotoCake\Db\Collection\CollectionFactoryInterface;
use PhotoCake\Db\Record\RecordFactoryInterface;

class MongoCollectionFactory implements CollectionFactoryInterface
{
    /**
     * @var \MongoDB
     */
    private $db = null;

    /**
     * @var \PhotoCake\Db\Record\RecordFactoryInterface
     */
    private $recordFactory = null;

    /**
     * @var array
     */
    private $collections = array();


    public function __construct(\MongoDB $db) {
        $this->db = $db;
    }

    /**
     * @param string $name
     * @return \PhotoCake\Db\Mongo\MongoCollection
     */
    public function create($name) {
        if (!isset($this->collections[$name])) {
            $collection = new MongoCollection($this->db, $name);
            $collection->setRecordFactory($this->recordFactory);

            $this->collections[$name] = $collection;
        }

        return $this->collections[$name];
    }

    /**
     * @param \PhotoCake\Db\Record\RecordFactoryInterface $recordFactory
     */
    public function setRecordFactory(RecordFactoryInterface $recordFactory) {
        $this->recordFactory = $recordFactory;
    }
}
