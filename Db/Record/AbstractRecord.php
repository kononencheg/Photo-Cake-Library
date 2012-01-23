<?php

namespace PhotoCake\Db\Record;

abstract class AbstractRecord implements RecordInterface
{
    /**
     * @static
     * @param mixed $object
     * @return bool
     */
    public static function isRecord($object)
    {
        return is_subclass_of($object, __CLASS__);
    }

    /**
     * @var string
     */
    protected $collectionName = NULL;

    /**
     * @var array
     */
    protected $fields = array();

    /**
     * @var array
     */
    protected $data = array();

    /**
     * @param mixed $data
     * @return void
     */
    public function populate($data)
    {
        foreach ($this->fields as $name => $type) {
            if (isset($data[$name])) {
                $value = $data[$name];

                if (class_exists($type, true)) {
                    if (AbstractRecord::isRecord($type)) {
                        //if (!AbstractRecord::isRecord($this->data[$name])) {
                            //echo 'new' . $type;
                        //}

                        $this->data[$name] = $this->createRecord($type);
                        $this->data[$name]->populate($value);

                    } else if ($value instanceof $type) {
                        $this->data[$name] = $value;
                    }
                } else {
                    settype($value, $type);
                    $this->data[$name] = $value;
                }
            }
        }
    }

    public function __set($name, $value)
    {
        $this->set($name, $value);
    }

    public function __get($name)
    {
        return $this->get($name);
    }

    public function set($name, $value)
    {
        if (isset($this->fields[$name])) {
            $type = $this->fields[$name];

            if (class_exists($type, true)) {
                if ($value instanceof $type) {
                    $this->data[$name] = $value;
                }
            } else {
                settype($value, $type);
                $this->data[$name] = $value;
            }
        }
    }

    public function get($name)
    {
        if (isset($this->data[$name])) {
            return $this->data[$name];
        }

        return NULL;
    }

    /**
     * @param string $type
     * @return \PhotoCake\Db\Record\RecordInterface
     */
    protected function createRecord($type)
    {
        return new $type();
    }

    /**
     * Specify data which should be stored in data base.
     *
     * @return array
     */
    public function dbSerialize()
    {
        $result = array();

        foreach ($this->data as $name => $value) {
            if (is_object($value) && AbstractRecord::isRecord($value)) {
                $result[$name] = $value->spanSerialize($this->collectionName);
            } else {
                $result[$name] = $value;
            }
        }

        return $result;
    }

    public function jsonSerialize()
    {
        $result = array();

        foreach ($this->data as $name => $value) {
            if (is_object($value) && AbstractRecord::isRecord($value)) {
                $result[$name] = $value->jsonSerialize();
            } else {
                $result[$name] = $value;
            }
        }

        return $result;
    }

    /**
     * @param string $parent
     */
    protected function spanSerialize($parent) {}

    /**
     * @abstract
     * @return mixed
     */
    abstract public function getId();
}
