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
     * @param int $offset
     * @return \PhotoCake\Db\Record\RecordInterface
     */
    function fetchOne($condition = null, $offset = null);

    /**
     * @abstract
     * @param mixed $condition
     * @param mixed $sort
     * @param int $offset
     * @param int $limit
     * @return \Iterator
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
     * @param array $condition
     * @return void
     */
    function removeAll($condition);

    /**
     * @abstract
     * @param array $condition
     * @param int $limit
     * @param int $offset
     * @return int
     */
    function count($condition = null, $limit = null, $offset = null);

    /**
     * @abstract
     * @return \PhotoCake\Db\Record\RecordInterface
     */
    function createRecord(array $data);
}
