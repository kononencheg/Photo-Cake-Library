<?php

namespace PhotoCake\Http\Response\Format;

interface FormatInterface
{
    /**
     * @return string
     */
    function getMimeType();

    /**
     * @param mixed $data
     * @return string
     */
    function renderResponse($data);

    /**
     * @param array $errors
     * @return string
     */
    function renderErrors(array $errors);
}
