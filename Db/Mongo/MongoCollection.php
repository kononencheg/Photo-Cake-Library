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
    public function updateAll($condition, $data)
    {
        $this->collection->update($condition, $data, array(
            'multiple' => true,
            'safe' => true,
        ));
    }

    /**
     * @param string $id
     * @return \PhotoCake\Db\Record\RecordInterface
     */
    public function fetch($id)
    {
        $data = $this->collection->findOne(array(
            '_id' => $this->mongoID($id)
        ));

        if ($data !== null) {
            return $this->createRecord($data);
        }

        return null;
    }

    /**
     * @param mixed $condition
     * @param mixed $offset
     * @return \PhotoCake\Db\Record\RecordInterface
     */
    public function fetchOne($condition = array(), $offset = null)
    {
        $cursor = $this->collection->find($condition)
                                    ->limit(1)
                                    ->skip($offset);

        $data = $cursor->getNext();

        if ($data !== null) {
            return $this->createRecord($data);
        }

        return null;
    }

    /**
     * @param mixed $condition
     * @param mixed $sort
     * @param integer $offset
     * @param integer $limit
     * @return \Iterator
     */
    public function fetchAll($condition = array(),
                             $sort = array(), $offset = null, $limit = null)
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
     * @param array $condition
     * @return mixed
     */
    public function removeAll($condition)
    {
        return $this->collection->remove($condition);
    }

    /**
     * @param array $condition
     * @param int $limit
     * @param int $offset
     * @return int
     */
    public function count($condition = null, $limit = null, $offset = null)
    {
        return $this->collection->count($condition, $limit, $offset);
    }

    /**
     * @param MongoRecord $record
     */
    protected function prepareRecord(MongoRecord $record)
    {
        $record->setRecordFactory($this->recordFactory);
    }

    /**
     * @param mixed $id
     * @return \MongoId
     */
    private function mongoID($id)
    {
        return new \MongoId($id);
    }
}
