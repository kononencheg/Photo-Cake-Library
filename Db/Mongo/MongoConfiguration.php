<?php

namespace PhotoCake\Db\Mongo;

use \PhotoCake\Db\Record\RecordFactoryInterface;
use PhotoCake\Db\Configuration\AbstractConfiguration;

class MongoConfiguration extends AbstractConfiguration
{
    /**
     * @var string
     */
    private $server = null;

    /**
     * @var array
     */
    private $options = null;

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

    /**
     * @return MongoCollectionFactory
     */
    protected function createCollectionFactory()
    {
        $mongo = new \Mongo($this->server, $this->options);

        $factory = new MongoCollectionFactory($mongo->selectDB($this->db));
        $factory->setRecordFactory($this->recordFactory);

        return $factory;
    }
}
