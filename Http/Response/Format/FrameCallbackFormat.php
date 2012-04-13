<?php

namespace PhotoCake\Http\Response\Format;

class FrameCallbackFormat implements FormatInterface
{
    /**
     * @var string
     */
    private $callback = null;

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
        if ( $this->callback !== null) {
            echo '<script> ' .
                    'if (parent.' . $this->callback . ' !== undefined) { '.
                       ' parent.' . $this->callback . '(' . json_encode($argument) . ');'.
                    '} ' .
                 '</script>';
        } else {
            echo '<script> ' .
                    'alert("Frame callback don\'t set!");' .
                '</script>';
        }
    }


}
