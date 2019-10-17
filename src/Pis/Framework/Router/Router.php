<?php

namespace Pis\Framework\Router;

use Pis\Framework\Router\BaseRouter;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\RequestContext;

class Router
{

    /** @var RouteCollection  */
    protected $routeCollection;
    /** @var UrlGenerator */
    public $urlGenerator;

    public function __construct($routeCollection) {
        $this->routeCollection = $routeCollection;
        $this->urlGenerator = new UrlGenerator($this->routeCollection, new RequestContext('', '', $_SERVER['SERVER_NAME'], $_SERVER['SERVER_NAME']));
    }

    public function AddRouter(BaseRouter $router) {
        $this->routeCollection->addCollection($router->GetRouteCollections());
    }

    public function GetRouteCollection() {
        return $this->routeCollection;
    }

    public function GetRoutes() {
        $routes = array();
        $it = $this->routeCollection->getIterator();
        while ($it->valid()) {
            $routes[$it->current()->GetPath()] = $it->current()->GetPath();
            $it->next();
        }
        return $routes;
    }

    /**
     * @param string $routeName
     * @return bool
     */
    public function ContainsRoute($routeName) {
        if ($this->routeCollection->get($routeName) === null) return false;
        return true;
    }

    /**
     * @param string $routeName
     * @return null|Route
     */
    public function GetRoute($routeName) {
        return $this->routeCollection->get($routeName);
    }

    /**
     * @param string $routeName
     * @param array $parameters
     * @param bool $fullUrl
     * @return string
     */
    public function ParseRoute($routeName, $parameters = array(), $fullUrl = false) {
        if ($parameters === null) $parameters = array();
        if ($fullUrl)
            return $this->urlGenerator->generate($routeName, $parameters, UrlGenerator::ABSOLUTE_URL);
        else
            return '/' . $this->urlGenerator->generate($routeName, $parameters, UrlGenerator::RELATIVE_PATH);
    }

}