<?php

namespace PhotoCake\Api\Method;

use \PhotoCake\Http\Response\Response;

abstract class Method
{
    /**
     * @var array
     */
    protected $arguments = array();

    /**
     * @var \PhotoCake\Api\Arguments\Filter
     */
    private $filter = NULL;

    /**
     * @var array
     */
    private $params = array();

    /**
     * @var \PhotoCake\Http\Response\Response
     */
    protected $response = NULL;

    /**
     * @param \PhotoCake\Http\Response\Response $response
     */
    public function setResponse(Response $response) {
        $this->response = $response;
        $this->filter = new \PhotoCake\Api\Arguments\Filter();
    }

    /**
     * @return void
     */
    protected function filter() {}

    /**
     * @abstract
     * @return mixed
     */
    abstract protected function apply();

    /**
     * @param array $params
     */
    final public function call(array $params) {
        $this->params = $params;

        $this->filter();

        if (!$this->response->hasErrors()) {
            $this->response->setResponse($this->apply());
        }
    }

    /**
     * @param $name
     * @return mixed
     */
    public function __get($name) {
        if (isset($this->params[$name])) {
            return $this->params[$name];
        }

        return NULL;
    }

    /**
     * @param array $messages
     * @return void
     */
    protected function applyFilter(array $messages, array $customFilters = array()) {
        foreach ($this->arguments as $name => $type) {
            $value = $this->filter->check($this->$name, $type);

            if (!is_object($value) && !is_array($value) &&
                isset($messages[$name]) &&
                isset($messages[$name][$value])) {

                $this->response->addParamError($name, $messages[$name][$value]);
            } else {
                if (isset($customFilters[$name])) {
                    $this->{$customFilters[$name]}($value);
                }

                $this->params[$name] = $value;
            }
        }
    }

}
