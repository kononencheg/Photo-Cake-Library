<?php

namespace PhotoCake\Db\Mongo;

use PhotoCake\Db\Record\AbstractRecord;

abstract class MongoRecord extends AbstractRecord
{
    /**
     * @var \MongoId
     */
    private $id = NULL;

    /**
     * @var array
     */
    protected $spanFields = array();

    /**
     * @param mixed $data
     * @return void
     */
    public function populate($data)
    {
        parent::populate($data);

        if (isset($data['_id'])) {
            $this->id = $data['_id'];
        } elseif (isset($data['_ref'])) {
            $this->id = $data['_ref']['$id'];
        }
    }

    /**
     * @return array
     */
    public function dbSerialize()
    {
        $result = parent::dbSerialize();

        if ($this->id !== NULL) {
            $result['_id'] = $this->id;
        }

        return $result;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        $result = parent::jsonSerialize();

        if ($this->id !== NULL) {
            $result['id'] = $this->id->{'$id'};
        }

        return $result;
    }

    /**
     * @param string $parent
     * @return array
     */
    protected function spanSerialize($parent)
    {
        $result = array();

        $fields = NULL;
        if (isset($this->spanFields[$parent])) {
            $fields = $this->spanFields[$parent];
        } else {
            $fields = array_keys($this->fields);
            array_push($fields, '_ref');
        }

        foreach ($fields as $i => $name) {
            if (isset($this->data[$name])) {
                $value = $this->data[$name];

                if (is_object($value) && AbstractRecord::isRecord($value)) {
                    $result[$name] = $value->spanSerialize($this->collectionName);
                } else {
                    $result[$name] = $value;
                }
            } elseif ($name === '_ref' && $this->id !== NULL) {
                $result['_ref']
                    = \MongoDBRef::create($this->collectionName, $this->id);
            }
        }

        return $result;
    }

    /**
     * @return string
     */
    public function getID()
    {
        return $this->id->{'$id'};
    }
}
