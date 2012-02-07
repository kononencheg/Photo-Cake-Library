<?php

namespace PhotoCake\Db\Record;

abstract class AbstractRecord implements RecordInterface
{
    /**
     * @var string
     */
    protected $collection = null;

    /**
     * @var array
     */
    protected $fields = array();

    /**
     * @var array
     */
    protected $data = array();
}
