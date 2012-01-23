<?php

namespace PhotoCake\Db\Configuration;

class ConfigurationManager
{
    private function __construct() {}

    /**
     * @var \PhotoCake\Db\Configuration\AbstractConfiguration
     */
    private $defaultConfiguration = NULL;

    /**
     * @param \PhotoCake\Db\Configuration\AbstractConfiguration $connection
     */
    public function setDefaultConfiguration(AbstractConfiguration $connection)
    {
        $this->defaultConfiguration = $connection;
    }

    /**
     * @return \PhotoCake\Db\Configuration\AbstractConfiguration
     */
    public function getDefaultConfiguration()
    {
        return $this->defaultConfiguration;
    }

    /**
     * @var \PhotoCake\Db\Configuration\ConfigurationManager
     */
    private static $instance;

    /**
     * @static
     * @return ConfigurationManager
     */
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new ConfigurationManager();
        }

        return self::$instance;
    }
}
