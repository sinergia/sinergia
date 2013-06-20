<?php

namespace Sinergia;

use ReflectionMethod, ReflectionFunction;

/**
 * Wrap something callable into an Invokeable
 * Class Invoker
 * @package Sinergia
 */
class Invokeable
{
    /**
     * @var ReflectionFunction|ReflectionMethod
     */
    protected $reflection;
    protected $callable;

    /**
     * 'function_name'
     * 'Static::method'
     * array($object, 'method')
     * array('Class', 'method')
     * $Invokeable
     * function() {} closure
     *
     * @param $callable
     */
    public function __construct($callable)
    {
        $this->callable = $callable;
        $this->reflection = Util::callableToReflection($callable);
    }

    public function getParameters()
    {
        return Util::getParameters($this->reflection);
    }

    public function __invoke($args = array())
    {
        $args = array_merge($this->getParameters(), $args);

        return is_callable($this->callable)
               ? call_user_func_array($this->callable, $args)
               : null;
    }
}
