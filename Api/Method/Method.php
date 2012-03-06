<?php

namespace PhotoCake\Api\Method;

use PhotoCake\Api\Acl\AclInterface;
use PhotoCake\Api\Arguments\Filter;

use PhotoCake\Http\Response\Response;

abstract class Method
{
    /**
     * @var array
     */
    protected $arguments = array();

    /**
     * @var array
     */
    protected $accessList = null;

    /**
     * @var \PhotoCake\Api\Arguments\Filter
     */
    private $filter = null;

    /**
     * @var array
     */
    private $params = array();

    /**
     * @var \PhotoCake\Api\Acl\AclInterface
     */
    private $acl = null;

    /**
     * @var \PhotoCake\Http\Response\Response
     */
    protected $response = null;

    public function __construct()
    {
        $this->arguments
                = array_merge($this->arguments, $this->extendArguments());
    }

    /**
     * @param \PhotoCake\Http\Response\Response $response
     */
    public function setResponse(Response $response) {
        $this->response = $response;
        $this->filter = new Filter();
    }

    public function setAcl(AclInterface $acl)
    {
        $this->acl = $acl;
    }

    /**
     * @return void
     */
    protected function filter() {
        $this->applyFilter();
    }

    /**
     * @return void
     */
    protected function prepare() {}

    /**
     * @abstract
     * @return mixed
     */
    abstract protected function apply();

    /**
     * @param array $params
     */
    final public function call(array $params) {
        if ($this->acl === null ||
            $this->acl->test($this->accessList)) {

            $this->params = $params;

            $this->prepare();
            $this->filter();

            if (!$this->response->hasErrors()) {
                $this->response->setResponse($this->apply());
            }
        } else {
            $this->response->addError('Доступ запрещен', 403);
        }
    }

    /**
     * @param string $name
     * @return mixed
     */
    protected function getParam($name) {
        if (isset($this->params[$name])) {
            return $this->params[$name];
        }

        return null;
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    protected function setParam($name, $value) {
        $this->params[$name] = $value;
    }

    /**
     * @param array $messages
     * @return void
     */
    protected function applyFilter(array $customFilters = array()) {

        foreach ($this->arguments as $name => $options) {
            $messages = array();
            if (isset($options[1])) {
                $messages = $options[1];
            }

            $value = $this->filter->check($this->getParam($name), $options[0]);

            if (!is_object($value) && !is_array($value) &&
                isset($messages[$value])) {

                $this->response->addParamError($name, $messages[$value]);
            } else {
                $this->setParam($name, $value);

                if (isset($customFilters[$name])) {
                    $this->{$customFilters[$name]}($value);
                }
            }
        }
    }

    protected function extendArguments()
    {
        return array();
    }

}
