<?php

namespace PhotoCake\Http\Response\Format;

class JSONFormat implements FormatInterface
{
    /**
     * @return string
     */
    function getMimeType()
    {
        return 'application/json';
    }

    /**
     * @param mixed $errors
     * @return string
     */
    public function renderErrors(array $errors)
    {
        echo json_encode(array( 'errors' => $errors ));
    }

    /**
     * @param mixed $data
     * @return string
     */
    public function renderResponse($data)
    {
        echo json_encode(array( 'response' => $data ));
    }


}
