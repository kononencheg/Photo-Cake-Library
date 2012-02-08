<?php

namespace PhotoCake\Db\Mongo;

use PhotoCake\Db\Record\AbstractRecord;
use PhotoCake\Db\Record\RecordFactoryInterface;

abstract class MongoRecord extends AbstractRecord
{
    /**
     * @const
     * @var int
     */
    const RELATION_MANY = 0;

    /**
     * @const
     * @var int
     */
    const RELATION_ONE = 1;

    /**
     * @const
     * @var int
     */
    const VISIBILITY_HIDDEN = 0;

    /**
     * @const
     * @var int
     */
    const VISIBILITY_VISIBLE = 1;

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
        $this->fields = $this->extendFields($this->fields);
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
    public function set($name, $value)
    {
        if ($this->isOne($name)) {
            $this->data[$name]
                    = $this->filterValue($value, $this->getType($name));
        }
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function get($name)
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
    public function add($name, $value)
    {
        if ($this->isMany($name)) {
            if (!isset($this->data[$name])) {
                $this->data[$name] = array();
            }

            array_push($this->data[$name],
                       $this->filterValue($value, $this->getType($name)));
        }
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public function remove($name, $value)
    {
        if ($this->isMany($name) && is_array($this->data[$name])) {
            $key = array_search($value, $this->data[$name]);
            array_splice($this->data[$name], $key, 1);
        }
    }

    /**
     * @param mixed $data
     */
    public function populate(array $data)
    {
        foreach ($data as $name => $value) {
            if (isset($this->fields[$name])) {
                $type = $this->getType($name);

                switch ($this->getRelation($name)) {
                    case MongoRecord::RELATION_ONE: {
                        $this->populateOne($name, $value, $type);
                        break;
                    }

                    case MongoRecord::RELATION_MANY: {
                        $this->populateMany($name, $value, $type);
                        break;
                    }
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
        $type = $this->getType($name);
        if (MongoRecord::isMongoRecord($type)) {
            switch ($this->getRelation($name)) {
                case MongoRecord::RELATION_ONE: {
                    return $value->spanDbSerialize($this->collection);
                }

                case MongoRecord::RELATION_MANY: {
                    $result = array();

                    foreach ($value as $record) {
                        array_push($result,
                                   $record->spanDbSerialize($this->collection));
                    }

                    return $result;
                }
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
    protected function extendFields(array $fields)
    {
        return $fields;
    }

    private function getJsonValue($name, $value)
    {
        $type = $this->getType($name);

        if (MongoRecord::isMongoRecord($type)) {
            switch ($this->getRelation($name)) {
                case MongoRecord::RELATION_ONE: {
                    return $value->jsonSerialize();
                }

                case MongoRecord::RELATION_MANY: {
                    $result = array();

                    foreach ($value as $record) {
                        array_push($result, $record->jsonSerialize());
                    }

                    return $result;
                }
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
    private function populateOne($name, array $value, $type)
    {
        if (MongoRecord::isMongoRecord($type)) {
            $record = $this->createRecord($type, $value);
            $record->populate($value);

            $this->data[$name] = $record;
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

        if (MongoRecord::isMongoRecord($type)) {
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

        if ($record !== null) {
            $record->populate($value);
        }

        return $record;
    }

    /**
     * @param string $name
     * @return bool
     */
    private function isMany($name)
    {
        return isset($this->fields[$name]) &&
                $this->getRelation($name) === MongoRecord::RELATION_MANY;
    }

    /**
     * @param string $name
     * @return bool
     */
    private function isOne($name)
    {
        return isset($this->fields[$name]) &&
               $this->getRelation($name) === MongoRecord::RELATION_ONE;
    }

    private function getType($name)
    {
        return $this->fields[$name]->type;
    }

    private function getRelation($name)
    {
        return $this->fields[$name]->relation;
    }

    private function isVisibile($name)
    {
        return $this->fields[$name]->visibility ===
                MongoRecord::VISIBILITY_VISIBLE;
    }
}
