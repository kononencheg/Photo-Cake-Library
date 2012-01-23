<?php

namespace PhotoCake\Api\Method;

interface MethodFactoryInterface
{
    /**
     * @param string $name
     * @return \PhotoCake\Api\Method\Method
     */
    function create($name);
}