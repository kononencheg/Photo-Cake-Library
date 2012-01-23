<?php

namespace PhotoCake\Http\Response\Format;

class FrameCallbackFormat implements FormatInterface
{
    /**
     * @var string
     */
    private $callback;

    /**
     * @param string $callbackName
     */
    function __construct($callback) {
        $this->callback = $callback;
    }

    /**
     * @return string
     */
    function getMimeType()
    {
        return 'text/html';
    }

    /**
     * @param mixed $errors
     * @return string
     */
    public function renderErrors(array $errors)
    {
        $this->printCallbackScript(array( 'errors' => $errors ));
    }

    /**
     * @param mixed $data
     * @return string
     */
    public function renderResponse($data)
    {
        $this->printCallbackScript(array( 'response' => $data ));
    }

    private function printCallbackScript($argument) {
        echo '<script> parent.' . $this->callback .
                '(' . json_encode($argument) . '); </script>';
    }


}
