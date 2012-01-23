<?php

namespace PhotoCake\Db\Mongo;

use \PhotoCake\Db\Record\RecordInterface;

class MongoCollection extends \PhotoCake\Db\Collection\AbstractCollection
{
    /**
     * @var \MongoCollection
     */
    private $collection;

    /**
     * @param \MongoDB $db
     * @param string $collection
     */
    public function __construct(\MongoDB $db, $name)
    {
        $this->collection = $db->selectCollection($name);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->collection->getName();
    }

    /**
     * @param \PhotoCake\Db\Record\RecordInterface $record
     */
    public function update(RecordInterface $record)
    {
        $data = $record->dbSerialize();
        $this->collection->save($data);
        $record->populate($data);
    }

    /**
     * @param mixed $data
     * @param mixed $condition
     * @return void
     */
    public function updateAll($data, $condition = NULL)
    {
        $this->collection->update($condition, $data);
    }

    /**
     * @param mixed $id
     * @return \PhotoCake\Db\Record\RecordInterface
     */
    public function fetch($id)
    {
        $data = $this->collection->findOne(array(
            '_id' => $this->mongoID($id)
        ));

        if ($data !== NULL) {
            $record = $this->createRecord();
            $record->populate($data);

            return $record;
        }

        return NULL;
    }

    /**
     * @param mixed $condition
     * @param mixed $offset
     * @return \PhotoCake\Db\Record\PhotoCake\Db\Record\RecordInterface
     */
    public function fetchOne($condition = NULL, $offset = NULL)
    {
        $record = NULL;
        
        $cursor = $this->collection->find($condition)
                                    ->limit(1)
                                    ->skip($offset);

        $data = $cursor->getNext();
        if ($data !== NULL) {
            $record = $this->createRecord();
            $record->populate($data);
        }

        return $record;
    }

    /**
     * @param mixed $condition
     * @param mixed $sort
     * @param integer $offset
     * @param integer $limit
     * @return \Iterator
     */
    public function fetchAll($condition = array(),
                             $sort = array(), $offset = NULL, $limit = NULL)
    {
        
        $cursor = $this->collection->find($condition)
                                    ->sort($sort)
                                    ->limit($limit)
                                    ->skip($offset);

        return new MongoCollectionIterator($cursor, $this);
    }

    /**
     * @param \PhotoCake\Db\Record\RecordInterface $record
     * @return mixed
     */
    public function remove(RecordInterface $record)
    {
        return $this->collection->remove(array(
            '_id' => $this->mongoID($record->getID())
        ));
    }

    /**
     * @param mixed $condition
     * @return mixed
     */
    public function removeAll($condition)
    {
        return $this->collection->remove($condition);
    }

    /**
     * @param null $condition
     * @param null $limit
     * @param null $offset
     * @return int
     */
    public function count($condition = NULL, $limit = NULL, $offset = NULL)
    {
        return $this->collection->count($condition, $limit, $offset);
    }

    /**
     * @param mixed $id
     * @return \MongoId
     */
    private function mongoID($id)
    {
        if ($id instanceof \MongoId) {
            return $id;
        }

        return new \MongoId($id);
    }
}