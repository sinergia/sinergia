<?php

namespace Sinergia\Sinergia;

class Router
{
    public $ANY = array();
    public $GET = array();
    public $POST = array();
    public $PUT = array();
    public $DELETE = array();
    public $PATCH = array();

    /**
     * @var callable
     */
    public $closureBuilder = null;

    public function __construct()
    {
        $this->closureBuilder = function($route) {
            return $route;
        };
    }

    public function setRoutes($methodRoutes)
    {
        foreach ($methodRoutes as $method => $routes) {
            $method = strtoupper($method);
            if ( property_exists(__CLASS__, $method) ) {
                $this->{$method} = $routes;
            }
        }
    }

    /**
     * @param $path
     * @param $method
     * @return Invokable
     */
    public function route($path, $method)
    {
        $method = strtoupper($method);
        $routes = $this->{$method};
        $matcher = new RouteMatcher();
        $route_params = $matcher->findMatch($routes, $path);

        if ( is_array($route_params) ) {
            list ($route, $params) = $route_params;
            $closure = call_user_func($this->closureBuilder, $route, $method);
            return new Invokable($closure, $params);
        } else {
            return null;
        }
    }

    /**
     * @param $path
     * @param $method
     * @return Invokable
     */
    public function __invoke($path, $method)
    {
        return $this->route($path, $method);
    }
}
