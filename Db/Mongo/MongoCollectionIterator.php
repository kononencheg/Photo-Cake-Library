<?php

namespace PhotoCake\Db\Mongo;

class MongoCollectionIterator implements \Iterator
{
    /**
     * @var \SplFixedArray
     */
    private $data;

    /**
     * @var int
     */
    private $key;

    /**
     * @var \MongoCursor
     */
    private $cursor;

    /**
     * @var \PhotoCake\Db\Mongo\MongoCollection
     */
    private $collection;

    /**
     * @param \MongoCursor $cursor
     * @param MongoCollection $collection
     */
    public function __construct
        (\MongoCursor $cursor, MongoCollection $collection)
    {
        $this->cursor = $cursor;

        $this->key = 0;
        $this->data = new \SplFixedArray($this->cursor->count(true));

        $this->collection = $collection;
    }


    /**
     * @return \PhotoCake\Db\Record\RecordInterface
     */
    public function current()
    {
        if (!isset($this->data[$this->key])) {
            $data = $this->cursor->current();
            if ($data !== null) {
                $record = $this->collection->createRecord();
                $record->populate($data);

                $this->data[$this->key] = $record;
            }
        }

        return $this->data[$this->key];
    }

    /**
     * @return void
     */
    public function next()
    {
        $this->cursor->next();
        $this->key++;
    }

    /**
     * @return int
     */
    public function key()
    {
        return $this->key;
    }

    /**
     * @return bool
     */
    public function valid()
    {
        return $this->cursor->valid();
    }

    /**
     * @return void
     */
    public function rewind()
    {
        $this->cursor->rewind();
        $this->key = 0;
    }
}
