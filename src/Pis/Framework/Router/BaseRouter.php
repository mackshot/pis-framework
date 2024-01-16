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
    protected function AddRoute(Route $route) {
        $callers = debug_backtrace();
        $controllerName = array_slice(explode('\\', $callers[1]['class']), -1);
        $controllerName = preg_replace('/Router$/', '', $controllerName[0]);
        $actionName = preg_replace('/Action$/', '', $route->getDefault('_controller')[1]);
        $this->routeCollection->add($controllerName . '::' . $actionName, $route);
    }

    /**
     * @param $function MultiRoute
     * @param $routes string[]
     */
    protected function AddMultiRoute(MultiRoute $multiRoute) {
        $controllerName = null;
        $actionName = null;
        foreach ($multiRoute->GetRoutes() as $key => $route) {
            if ($controllerName == null) {
                $callers = debug_backtrace();
                $controllerName = array_slice(explode('\\', $callers[1]['class']), -1);
                $controllerName = preg_replace('/Router$/', '', $controllerName[0]);
                $actionName = preg_replace('/Action$/', '', $route->getDefault('_controller')[1]);
            };
            $postfix = $key == null ? '' : '::' . $key;
            $this->routeCollection->add($controllerName . '::' . $actionName . $key, $route);
        }
    }

    /**
     * @return RouteCollection
     */
    public function GetRouteCollections() {
        return $this->routeCollection;
    }

}