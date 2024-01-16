<?php

namespace Pis\Framework\Controller;

use Pis\Framework\Router\Router;

class BreadCrumb
{

    protected $routes = array();

    /** @var Router  */
    protected $router;

    public function __construct(Router $router) {
        $this->router = $router;
    }

    public function AddItem($text, $route, $routeParameters = null) {
        $this->routes[] = array(
            'text' => $text,
            'route' => $this->router->ParseRoute($route, $routeParameters)
        );
    }

    public function Get() {
        return $this->routes;
    }

}