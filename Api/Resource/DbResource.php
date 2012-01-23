<?php

namespace PhotoCake\Api\Resource;

use PhotoCake\Db\Configuration\ConfigurationManager;

class DbResource implements ResourceInterface
{
    /**
     * @var \PhotoCake\Db\Collection\CollectionFactoryInterface
     */
    protected $collectionFactory;

    public function __construct()
    {
        $connection = ConfigurationManager::getInstance()->getDefaultConfiguration();
        $this->collectionFactory = $connection->getCollectionFactory();
    }

    protected function getCollection($name) {
        return $this->collectionFactory->create($name);
    }
}
