<?php

namespace PhotoCake\Db\Collection;

use \PhotoCake\Db\Record\RecordInterface;

interface CollectionInterface
{
    /**
     * @abstract
     * @return string
     */
    function getName();

    /**
     * @abstract
     * @param \PhotoCake\Db\Record\RecordInterface $record
     * @return void
     */
    function update(RecordInterface $record);

    /**
     * @abstract
     * @param mixed $condition
     * @param mixed $data
     * @return void
     */
    function updateAll($condition, $data);

    /**
     * @abstract
     * @param string $id
     * @return \PhotoCake\Db\Record\RecordInterface
     */
    function fetch($id);

    /**
     * @abstract
     * @param mixed $condition
     * @param integer $offset
     * @return \PhotoCake\Db\Record\RecordInterface
     */
    function fetchOne($condition = null, $offset = null);

    /**
     * @abstract
     * @param mixed $condition
     * @param mixed $sort
     * @param integer $offset
     * @param integer $limit
     * @return Iterator
     */
    function fetchAll
        ($condition = null, $sort = null, $offset = null, $limit = null);

    /**
     * @abstract
     * @param \PhotoCake\Db\Record\RecordInterface $record
     * @return void
     */
    function remove(RecordInterface $record);

    /**
     * @abstract
     * @param mixed $condition
     * @return void
     */
    function removeAll($condition);

    /**
     * @abstract
     * @param $condition
     * @param $limit
     * @param $offset
     * @return integer
     */
    function count($condition, $limit, $offset);

    /**
     * @abstract
     * @return \PhotoCake\Db\Record\RecordInterface
     */
    public function createRecord();
}
