<?php

namespace Pis\Framework\Router;

use Pis\Framework\Controller\BaseController;

class BaseRouter
{

    protected $controller;

    /** @var RouteCollection */
    protected $routeCollection;

    public function __construct($controller) {
        $this->controller = BaseController::createInstanceWithoutConstructor($controller);
        //$reflect = new \ReflectionClass($controller);
        //$this->controller = $reflect->newInstanceWithoutConstructor();
        $this->routeCollection = new \Pis\Framework\Router\RouteCollection();
    }

    /**
     * @param $function Route
     */
    protected function AddRoute(Route $function) {
        $callers = debug_backtrace();
        $controllerName = array_slice(explode('\\', $callers[1]['class']), -1);
        $controllerName = preg_replace('/Router$/', '', $controllerName[0]);
        $actionName = preg_replace('/Action$/', '', from($function->getDefault('_controller'))->elementAt(1));
        $this->routeCollection->add($controllerName . '::' . $actionName, $function);
    }

    /**
     * @return RouteCollection
     */
    public function GetRouteCollections() {
        return $this->routeCollection;
    }

}