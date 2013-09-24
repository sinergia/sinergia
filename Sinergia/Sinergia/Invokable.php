<?php

namespace Sinergia\Sinergia;

use ReflectionMethod,
    ReflectionFunction,
    BadFunctionCallException,
    ReflectionFunctionAbstract,
    Closure;

/**
 * Wrap something callable into an Invokable
 * Class Invoker
 * @package Sinergia
 */
class Invokable
{
    /**
     * @var ReflectionFunction|ReflectionMethod
     */
    protected $reflection;
    protected $callable;
    protected $params = array();

    /**
     * 'function_name'
     * 'Static::method'
     * array($object, 'method')
     * array('Class', 'method')
     * $Invokable
     * function() {} closure
     *
     * @param $callable
     */
    public function __construct($callable, $params = array())
    {
        $this->callable = $callable;
        $this->setParams($params);
    }

    public function getParams()
    {
        return $this->params ?:
            $this->params = static::getReflectionParams($this->getReflection());
    }

    public function setParams($params)
    {
        if ($params) {
            $this->params = array_merge($this->getParams(), $params);
        }

        return $this;
    }

    public function getReflection()
    {
        return $this->reflection ?:
            $this->reflection = static::callableToReflection($this->callable);
    }

    public function __invoke($args = array())
    {
        $args = array_merge($this->getParams(), $args);

        return $this->getReflection()->invokeArgs($args);
    }

    public function __toString()
    {
        $reflection = $this->getReflection();

        if ($reflection instanceof ReflectionMethod) {
            $separator = $reflection->isStatic() ? '::' : '->';
            $name = sprintf("%s%s%s", $reflection->class, $separator, $reflection->getName());
        } else {
            $name = $this->getReflection()->getName();
        }

        return (string) $name;
    }

    /**
     * @param $callable
     * @param  array $params
     * @return mixed
     */
    public static function run($callable, $params = array())
    {
        $invokable = new static($callable);

        return $invokable($params);
    }

    /**
     * Reflects anything that is callable
     * 'function_name'
     * 'Static::method'
     * array($object, 'method')
     * array('Class', 'method')
     * $Invokable
     * function() {} closure
     * @param $callable
     * @return ReflectionFunction|ReflectionMethod
     */
    public static function callableToReflection($callable)
    {
        if ( is_string($callable) ) {
            if ( strpos($callable, "::") ) {
                list($class, $method) = explode("::", $callable);

                return new ReflectionMethod($class, $method);

            } elseif ( function_exists($callable) ) {
                return new ReflectionFunction($callable);

            } else {
                $fakeCallback = function() use ($callable) {
                    throw new BadFunctionCallException("function '$callable' not found");
                };

                return new ReflectionFunction($fakeCallback);
            }
        }

        if ( is_array($callable) ) {
            list($class, $method) = $callable;

            return new ReflectionMethod($class, $method);
        }

        if ( is_object($callable) ) {
            if ($callable instanceof Closure) {
                return new ReflectionFunction($callable);
            }

            return new ReflectionMethod($callable, '__invoke');
        }
    }

    /**
     * Returns the function parameters as a dictionary with name => default value
     * @param  ReflectionFunctionAbstract $r
     * @return array
     */
    public static function getReflectionParams(ReflectionFunctionAbstract $r)
    {
        $parameters = array();
        foreach ($r->getParameters() as $p) {
            $parameters[$p->getName()] = $p->isDefaultValueAvailable() ? $p->getDefaultValue() : null;
        }

        return $parameters;
    }
}
