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
        $this->fields = array_merge($this->fields, $this->extendFields());
        foreach ($this->fields as $name => $field) {
            $type = $field;
            $relation = MongoRecord::RELATION_ONE;
            $visibility = MongoRecord::VISIBILITY_VISIBLE;

            if (is_array($field)) {
                $type = $field['type'];

                if (isset($field['relation'])) {
                    $relation = $field['relation'];
                }

                if (isset($field['visibility'])) {
                    $visibility = $field['visibility'];
                }
            }

            $this->fields[$name] = new \stdClass();
            $this->fields[$name]->type = $type;
            $this->fields[$name]->relation = $relation;
            $this->fields[$name]->visibility = $visibility;
        }


        $this->defaultSpanFields
                = array_merge(array_keys($this->fields), array('_ref'));
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
        return $this->id->{'$id'};
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    protected function set($name, $value)
    {
        if (!$this->isMany($name)) {
            $this->data[$name] = $value;
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
     * @param mixed $value
     */
    protected function add($name, $value)
    {
        if ($this->isMany($name)) {
            if (!isset($this->data[$name])) {
                $this->data[$name] = array();
            }

            array_push($this->data[$name], $value);
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
                array_splice($this->data[$name], $key, 1);
            }
        }
    }

    /**
     * @param array $data
     */
    public function populate(array $data)
    {
        foreach ($data as $name => $value) {
            if (isset($this->fields[$name])) {
                $type = $this->getType($name);

                if ($this->isMany($name)) {
                    $this->populateMany($name, $value, $type);
                } else {
                    $this->populateOne($name, $value, $type);
                }

            } elseif ($name === '_id' || $name === '_ref') {
                $this->id = $value;
            }
        }
    }

    /**
     * @return array
     */
    public function dbSerialize()
    {
        $result = array();

        foreach ($this->data as $name => $value) {
            $result[$name] = $this->getDbValue($name, $value);
        }

        if ($this->id !== null) {
            $result['_id'] = $this->id;
        }

        return $result;
    }

    /**
     * @param string $parent
     * @return array
     */
    protected function spanDbSerialize($parent)
    {
        $result = array();

        $fields = $this->getSpanFields($parent);
        foreach ($fields as $name) {
            if (isset($this->data[$name])) {
                $result[$name] = $this->getDbValue($name, $this->data[$name]);
            } elseif ($name === '_ref' && $this->id !== null) {
                $result['_ref'] = $this->id;
            }
        }

        return $result;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return mixed
     */
    private function getDbValue($name, $value)
    {
        if (MongoRecord::isMongoRecord($value)) {
            if ($this->isMany($name)) {
                $result = array();

                foreach ($value as $record) {
                    array_push
                        ($result, $record->spanDbSerialize($this->collection));
                }

                return $result;
            } else {
                return $value->spanDbSerialize($this->collection);
            }
        }

        return $value;
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

    /**
     * @param array $fields
     */
    protected function extendFields()
    {
        return array();
    }

    private function getJsonValue($name, $value)
    {
        $type = $this->getType($name);

        if ($this->isRecordExist($type)) {
            if ($this->isMany($name)) {
                $result = array();

                foreach ($value as $record) {
                    array_push($result, $record->jsonSerialize());
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
     * @param string $name
     * @param mixed $value
     * @param string $type
     */
    private function populateOne($name, $value, $type)
    {
        if ($this->isRecordExist($type)) {
            // TODO: Do not create every time!
            $this->data[$name] = $this->createRecord($type, $value);
        } else {
            $this->data[$name] = $this->filterValue($value, $type);
        }
    }

    /**
     * @param string $name
     * @param array $values
     * @param string $type
     */
    private function populateMany($name, array $values, $type)
    {
        $array = array();

        if ($this->isRecordExist($type)) {
            foreach ($values as $value) {
                array_push($array, $this->createRecord($type, $value));
            }
        } else {
            foreach ($values as $value) {
                array_push($array, $this->filterValue($value, $type));
            }
        }

        $this->data[$name] = $array;
    }

    /**
     * @param mixed $value
     * @param string $type
     * @return mixed
     */
    private function filterValue($value, $type)
    {
        if (class_exists($type, true)) {
            if($value instanceof $type) {
                return $value;
            }
        } else {
            settype($value, $type);
            return $value;
        }

        return null;
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
    private function isRecordExist($name)
    {
        return $this->recordFactory->isRecordExist($name);
    }

    /**
     * @param string $name
     * @return string
     */
    private function getType($name)
    {
        return $this->fields[$name]->type;
    }

    /**
     * @param string $name
     * @return boolean
     */
    private function isMany($name)
    {
        return $this->fields[$name]->relation;
    }

    /**
     * @param string $name
     * @return boolean
     */
    private function isVisibile($name)
    {
        return $this->fields[$name]->visibility;
    }
}
