<?php

namespace PhotoCake\Db\Mongo;

use PhotoCake\Db\Record\AbstractRecord;

abstract class MongoRecord extends AbstractRecord
{
    /**
     *
     */
    const RELATION_MANY = 'many';

    /**
     *
     */
    const RELATION_ONE = 'one';

    /**
     *
     */
    const VISIBILITY_HIDDEN = 'hidden';

    /**
     *
     */
    const VISIBILITY_VISIBLE = 'visible';

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
     * @var array
     */
    protected $spanFields = array();

    /**
     * @var \MongoId
     */
    private $id = null;

    /**
     * @var array
     */
    private $defaultSpanFields = null;

    /**
     * @var array
     */
    private $types = array();

    /**
     * @var array
     */
    private $relations = array();

    /**
     * @var array
     */
    private $visibilities = array();

    /**
     * @var array
     */
    private $originalData = array();


    /**
     *
     */
    public function __construct()
    {
        $this->defaultSpanFields = array_keys($this->fields);
        array_push($this->defaultSpanFields, '_ref');

        foreach ($this->fields as $name => $field) {
            $type = 'string';
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

           } else {
                $type = $field;
           }

            $this->types[$name] = $type;
            $this->relations[$name] = $relation;
            $this->visibilities[$name] = $visibility;
        }
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
                = $this->filterValue($value, $this->types[$name]);
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
                       $this->filterValue($value, $this->types[$name]));
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
    public function populate($data)
    {
        foreach ($data as $name => $value) {
            if (isset($this->fields[$name])) {
                $type = $this->types[$name];

                switch ($this->relations[$name]) {
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
        $type = $this->types[$name];
        if (MongoRecord::isMongoRecord($type)) {
            switch ($this->relations[$name]) {
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
            if ($this->visibilities[$name]
                    === MongoRecord::VISIBILITY_VISIBLE) {

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
        $type = $this->types[$name];

        if (MongoRecord::isMongoRecord($type)) {
            switch ($this->relations[$name]) {
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
    private function populateOne($name, $value, $type)
    {
        if (MongoRecord::isMongoRecord($type)) {
            $record = $this->createRecord($type);
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
                $record = $this->createRecord($type);
                $record->populate($value);

                array_push($array, $record);
            }
        } else {
            foreach ($values as $value) {
                array_push($array, $this->filterValue($value, $type));
            }
        }

        $this->data[$name] = $array;
        $this->originalData = $array;
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
     * @param string $type
     * @return \PhotoCake\Db\Mongo\MongoRecord
     */
    private function createRecord($type)
    {
        return new $type();
    }

    /**
     * @param string $name
     * @return bool
     */
    private function isMany($name)
    {
        return isset($this->fields[$name]) &&
               $this->relations[$name] === MongoRecord::RELATION_MANY;
    }

    /**
     * @param string $name
     * @return bool
     */
    private function isOne($name)
    {
        return isset($this->fields[$name]) &&
               $this->relations[$name] === MongoRecord::RELATION_ONE;
    }

}
