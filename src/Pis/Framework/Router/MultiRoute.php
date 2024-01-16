<?php

namespace Pis\Framework\Router;

class MultiRoute
{

    /** @var \string[]  */
    protected $additionalPaths;

    /** @var Route  */
    protected $route;

    /**
     * MultiRoute constructor.
     * @param Route $route
     * @param $additionalPaths string[]
     */
    public function __construct(Route $route, $additionalPaths) {
        if (count(array_unique(array_keys($additionalPaths))) != count(array_keys($additionalPaths)))
            throw new \Exception("All paths need different keys!");

        $this->route = $route;
        $this->additionalPaths = $additionalPaths;
    }

    public function GetRoutes() {
        $routes = array(null => $this->route);
        foreach ($this->additionalPaths as $key => $path) {
            $function = clone $this->route;
            $function->setPath($path);
            $routes[$key] = $function;
        }
        return $routes;
    }

}