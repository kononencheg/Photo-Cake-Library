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
    protected function applyFilter(array $messages = array(),
                                   array $customFilters = array()) {

        foreach ($this->arguments as $name => $type) {
            $value = $this->filter->check($this->getParam($name), $type);

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
