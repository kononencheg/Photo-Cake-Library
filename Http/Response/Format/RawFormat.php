<?php

namespace PhotoCake\Http\Response\Format;

class RawFormat implements FormatInterface
{
    private $type = null;

    /**
     * @return string
     */
    function getMimeType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    function setMimeType($type)
    {
        $this->type = $type;
    }

    /**
     * @param mixed $errors
     * @return string
     */
    function renderErrors(array $errors)
    {
        echo var_dump($errors);
    }

    /**
     * @param mixed $data
     * @return string
     */
    function renderResponse($data)
    {
        echo $data;
    }
}
