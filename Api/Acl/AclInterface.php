<?php

namespace PhotoCake\Api\Acl;

interface AclInterface
{
    /**
     * @abstract
     * @param array $list
     * @return boolean
     */
    function test($list);
}
