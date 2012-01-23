<?php

namespace PhotoCake\Db\Mongo;

use \PhotoCake\Db\Record\RecordFactoryInterface;
use PhotoCake\Db\Configuration\AbstractConfiguration;

class MongoConfiguration extends AbstractConfiguration
{
    /**
     * @var string
     */
    private $server = NULL;

    /**
     * @var array
     */
    private $options = NULL;

    /**
     * @param string $server
     * @param array $options
     */
    public function __construct($server = 'mongodb://localhost:27017',
                                array $options = array( 'connect' => TRUE ))
    {
        $this->server = $server;
        $this->options = $options;
    }

    public function getCollectionFactory()
    {
        if ($this->collectionFactory === NULL) {
            $mongo = new \Mongo($this->server, $this->options);

            $this->collectionFactory
                    = new MongoCollectionFactory($mongo->selectDB($this->db));
            $this->collectionFactory->setRecordFactory($this->recordFactory);
        }

        return $this->collectionFactory;
    }
}
