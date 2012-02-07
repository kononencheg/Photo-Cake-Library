<?php

namespace PhotoCake\Http\Response;

use PhotoCake\Http\Request;

class Response
{
    /**
     * @var \PhotoCake\Http\Response\Format\FormatInterface
     */
    private $format = null;

    /**
     * @var array
     */
    private $response = null;

    /**
     * @var array
     */
    private $errors = array();

    /**
     * @param \PhotoCake\Http\Response\Format\FormatInterface $format
     */
    public function setFormat($format)
    {
        $this->format = $format;
    }

    public function addError($message, $code)
    {
        // TODO: Create error class
        array_push($this->errors, array(
            'message' => $message,
            'code' => $code
        ));
    }

    public function addParamError($param, $message)
    {
        array_push($this->errors, array(
            'message' => $message,
            'param' => $param
        ));
    }

    public function hasErrors()
    {
        return !empty($this->errors);
    }

    public function setResponse($response)
    {
        $this->response = $response;
    }

    public function render()
    {
        $this->setHeader('Content-Type', $this->format->getMimeType());

        if ($this->hasErrors()) {
            $this->format->renderErrors($this->errors);
        } else {
            $this->format->renderResponse($this->response);
        }
    }

    public function setHeader($name, $value)
    {
        header($name . ': ' . $value);
    }
}
