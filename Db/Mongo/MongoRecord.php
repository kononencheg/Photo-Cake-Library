<?php

namespace PhotoCake\Db\Mongo;

use PhotoCake\Db\Record\AbstractRecord;
use PhotoCake\Db\Record\RecordFactoryInterface;

abstract class MongoRecord extends AbstractRecord
{
    /**
     * @const
     * @var boolean
     */
    const RELATION_MANY = true;

    /**
     * @const
     * @var boolean
     */
    const RELATION_ONE = false;

    /**
     * @const
     * @var boolean
     */
    const VISIBILITY_VISIBLE = true;

    /**
     * @const
     * @var boolean
     */
    const VISIBILITY_HIDDEN = false;

    /**
     * @static
     * @param mixed $object
     * @return bool
     */
    public static function isMongoRecord($object)
    {
        return is_subclass_of($object, __CLASS__);
    }

    /**
     * @var \MongoId
     */
    private $id = null;

    /**
     * @var array
     */
    protected $spanFields = array();

    /**
     * @var array
     */
    private $changedFields = array();

    /**
     * @var array
     */
    private $defaultSpanFields = null;

    /**
     * @var RecordFactoryInterface
     */
    private $recordFactory = null;

    /**
     *
     */
    public function __construct()
    {
        $this->options = array_merge($this->options, $this->extendOptions());

        foreach ($this->options as $name => $option) {
            $type = $option;
            $keyField = null;
            $relation = MongoRecord::RELATION_ONE;
            $visibility = MongoRecord::VISIBILITY_VISIBLE;

            if (is_array($option)) {
                $type = $option['type'];

                if (isset($option['key_field'])) {
                    $keyField = $option['key_field'];
                }

                if (isset($option['relation'])) {
                    $relation = $option['relation'];
                }

                if (isset($option['visibility'])) {
                    $visibility = $option['visibility'];
                }
            }

            $this->options[$name] = array();
            $this->options[$name]['type'] = $type;
            $this->options[$name]['relation'] = $relation;
            $this->options[$name]['key_field'] = $keyField;
            $this->options[$name]['visibility'] = $visibility;
        }

        $this->fields = array_keys($this->options);
        $this->defaultSpanFields  = array_merge($this->fields, array('_ref'));
    }

    /**
     * @return array
     */
    protected function extendOptions()
    {
        return array();
    }

    /**
     * @param RecordFactoryInterface $factory
     */
    public function setRecordFactory(RecordFactoryInterface $factory)
    {
        $this->recordFactory = $factory;
    }

    /**
     * @return string
     */
    public function getId()
    {
        if ($this->id !== null) {
            return $this->id->{'$id'};
        }

        return null;
    }

    /**
     * @param \MongoId $id
     */
    public function setMongoId(\MongoId $id) {
        $this->id = $id;
    }

    /**
     * @return \MongoId
     */
    public function getMongoId() {
        return $this->id;
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    protected function set($name, $value)
    {
        if (!isset($this->data[$name]) || $this->data[$name] !== $value) {
            $type = $this->getType($name);

            $this->data[$name] = $this->filterValue($type, $value);

            if (!$this->isRecord($type)) {
                $this->changedFields[$name] = true;
            }
        }
    }

    /**
     * @param string $name
     * @return mixed
     */
    protected function get($name)
    {
        if (isset($this->data[$name])) {
            return $this->data[$name];
        }

        return null;
    }

    /**
     * @param string $name
     * @param string $key
     * @return mixed
     */
    protected function getByKey($name, $key) {
        if ($this->isMany($name) &&
            isset($this->data[$name]) &&
            isset($this->data[$name][$key])) {

            return $this->data[$name][$key];
        }

        return null;
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    protected function add($name, $value)
    {
        if ($this->isMany($name)) {
            if (!isset($this->data[$name])) {
                $this->data[$name] = array();
            }

            $type = $this->getType($name);
            $value = $this->filterValue($type, $value);

            if ($this->isRecord($type)) {
                $key = $this->getKey($name, $value);

                if ($key !== null) {
                    $key = str_replace('.', '_', $key);
                    $this->data[$name][$key] = $value;
                }
            } else {
                array_push($this->data[$name], $value);
                $this->changedFields[$name] = true;
            }
        }
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    protected function remove($name, $value)
    {
        if ($this->isMany($name) && isset($this->data[$name])) {
            $key = array_search($value, $this->data[$name]);
            if ($key !== false) {
                $this->data[$name][$key] = null;

                $type = $this->getType($name);
                if (!$this->isRecord($type)) {
                    $this->changedFields[$name] = true;
                }
            }
        }
    }

    /**
     * @param array $data
     */
    public function populate(array $data)
    {
        foreach ($data as $name => $value) {
            if (isset($this->options[$name])) {
                $type = $this->getType($name);

                if ($this->isMany($name)) {
                    $data = array();

                    foreach ($value as $key => $val) {
                        $data[$key] = $this->populateValue($type, $val);
                    }

                    $this->data[$name] = $data;
                } else {
                    $this->data[$name] = $this->populateValue($type, $value);
                }

            } elseif ($name === '_id' || $name === '_ref') {
                $this->id = $value;
            }
        }
    }

    /**
     * @param string $name
     * @param array $value
     * @return \PhotoCake\Db\Mongo\MongoRecord
     */
    private function createRecord($name, array $value)
    {
        $record = $this->recordFactory->createByName($name, $value);
        $this->initRecord($record);

        if ($record !== null) {
            $record->populate($value);
        }

        return $record;
    }

    /**
     * @param string|null $collection
     * @return array
     */
    public function insertSerialize($collection = null)
    {
        $result = array();

        $fields = $this->fields;
        if ($collection !== null) {
            $fields = $this->getSpanFields($collection);
        }

        foreach ($fields as $name) {
            if (isset($this->data[$name])) {
                $type = $this->getType($name);
                $value = $this->data[$name];

                if ($this->isRecord($type)) {
                    if ($this->isMany($name)) {
                        $result[$name] = array();

                        foreach ($value as $key => $record) {
                            $result[$name][$key] =
                                    $record->insertSerialize($this->collection);
                        }
                    } else {
                        $result[$name] =
                                $value->insertSerialize($this->collection);
                    }
                } else {
                    $result[$name] = $value;
                }
            } elseif ($name === '_ref' && $this->id !== null) {
                $result['_ref'] = $this->id;
            }
        }

        $this->changedFields = array();

        return $result;
    }

    /**
     * @param string $collection
     * @return array
     */
    public function updateSerialize($collection = null)
    {
        $result = array( '$set' => array(), '$unset' => array() );

        $fields = $this->fields;
        if ($collection !== null) {
            $fields = $this->getSpanFields($collection);
        }

        foreach ($fields as $name) {
            if (isset($this->data[$name])) {
                $type = $this->getType($name);
                $value = $this->data[$name];

                if ($this->isRecord($type)) {
                    if ($this->isMany($name)) {
                        foreach ($value as $key => $record) {
                            $prefix = $name . '.' . $key;

                            if ($record !== null) {
                                $this->serializeRecord
                                            ($result, $prefix, $record);
                            } else {
                                $result['$unset'][$prefix] = 1;
                            }
                        }

                    } else {
                        $this->serializeRecord($result, $name, $value);
                    }

                } elseif (isset($this->changedFields[$name])) {
                    $result['$set'][$name] = $value;
                }

            }
        }

        $this->changedFields = array();

        if (empty($result['$set'])) {
            unset($result['$set']);
        }

        if (empty($result['$unset'])) {
            unset($result['$unset']);
        }

        return $result;
    }

    /**
     * @param array $result
     * @param string $prefix
     * @param MongoRecord $record
     * @return array
     */
    private function serializeRecord(&$result, $prefix, MongoRecord $record)
    {
        $data = $record->updateSerialize($this->collection);

        foreach ($data as $name => $array) {
            foreach ($array as $key => $value) {
                $result[$name][$prefix . '.' . $key] = $value;
            }
        }
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        $result = array();

        foreach ($this->data as $name => $value) {
            if ($this->isVisibile($name)) {
                $result[$name] = $this->getJsonValue($name, $value);
            }
        }

        if ($this->id !== null) {
            $result['id'] = $this->id->{'$id'};
        }

        return $result;
    }

    private function getJsonValue($name, $value)
    {
        $type = $this->getType($name);

        if ($this->isRecord($type)) {
            if ($this->isMany($name)) {
                $result = array();

                foreach ($value as $key => $record) {
                    $result[$key] = $record->jsonSerialize();
                }

                return $result;
            } else {
                return $value->jsonSerialize();
            }
        }

        return $value;
    }

    /**
     * @param string $parent
     * @return array
     */
    private function getSpanFields($parent)
    {
        if (isset($this->spanFields[$parent])) {
            return $this->spanFields[$parent];
        }

        return $this->defaultSpanFields;
    }

    /**
     * @param string $type
     * @param mixed $value
     * @return mixed
     */
    private function populateValue($type, $value)
    {
        if ($this->isRecord($type)) {
            return $this->createRecord($type, $value);
        }

        settype($value, $type);
        return $value;
    }

    /**
     * @param string $type
     * @param mixed $value
     * @return mixed
     */
    private function filterValue($type, $value)
    {
        if ($this->isRecord($type)) {
            if (MongoRecord::isMongoRecord($value)) {
                return $value;
            }
        } else {
            settype($value, $type);
            return $value;
        }

        return null;
    }

    /**
     * @param MongoRecord $record
     */
    private function initRecord(MongoRecord $record)
    {
        $record->setRecordFactory($this->recordFactory);
    }

    /**
     * @param string $name
     * @return boolean
     */
    private function isRecord($name)
    {
        return $this->recordFactory->isRecordExist($name);
    }


    /**
     * @param string $name
     * @return string
     */
    private function getType($name)
    {
        return $this->options[$name]['type'];
    }

    /**
     * @param $name
     * @param MongoRecord $value
     * @return mixed|string
     */
    private function getKey($name, MongoRecord $value)
    {
        $field = $this->options[$name]['key_field'];
        if ($field === null || $field === 'id') {
            return $value->getId();
        }

        return $value->get($field);
    }

    /**
     * @param string $name
     * @return boolean
     */
    private function isMany($name)
    {
        return $this->options[$name]['relation'];
    }

    /**
     * @param string $name
     * @return boolean
     */
    private function isVisibile($name)
    {
        return $this->options[$name]['visibility'];
    }
}
