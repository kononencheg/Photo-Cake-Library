<?php

namespace PhotoCake\View;

use PhotoCake\Http\Session;

class Page
{
    /**
     * @var string
     */
    private $base = NULL;

    /**
     * @var \PhotoCake\Http\Session
     */
    private $session = NULL;

    public function __construct($base = '')
    {
        $this->session = Session::getInstance();
        $this->base = $base;

        ob_start();
    }

    public function __destruct()
    {
        echo ob_get_clean();
    }

    /**
     * @param string $path
     * @return string
     */
    public function render($path)
    {
        include $this->base . $path . '.phtml';
    }
}
