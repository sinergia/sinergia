<?php

use Sinergia\Sinergia\Invokable;

class InvokableTestSample
{
    public function __invoke() { return __CLASS__; }
}

class InvokableTest  extends PHPUnit_Framework_Testcase
{
    public function testConstructor()
    {
        $closure = function($foo, $bar) { return "$foo|$bar"; };
        $invokable = new Invokable($closure, array('foo' => '@', 'bar' => '%'));
        $this->assertEquals('@|%', $invokable());
    }

    public function testGetReflectionParams()
    {
        $function = function($int = 64, $array = array(2, 1), $null = null) {};
        $reflection = new ReflectionFunction($function);
        $params = Invokable::getReflectionParams($reflection);
        $this->assertEquals(array('int' => 64, 'array' => array(2, 1), 'null' => null), $params);
    }

    public function testCallableToReflectionStringFunction()
    {
        $reflection = Invokable::callableToReflection('rand');
        $this->assertEquals('rand', $reflection->getName());
        $this->assertInstanceOf('ReflectionFunction', $reflection);
    }

    public function testCallableToReflectionStringStatic()
    {
        $reflection = Invokable::callableToReflection('ReflectionClass::export');
        $this->assertEquals('export', $reflection->getName());
        $this->assertEquals('ReflectionClass', $reflection->class);
        $this->assertInstanceOf('ReflectionMethod', $reflection);
    }

    public function testCallableToReflectionArrayStatic()
    {
        $reflection = Invokable::callableToReflection(array('ReflectionClass', 'export'));
        $this->assertEquals('export', $reflection->getName());
        $this->assertEquals('ReflectionClass', $reflection->class);
        $this->assertInstanceOf('ReflectionMethod', $reflection);
    }

    public function testCallableToReflectionArrayInstance()
    {
        $obj = new ArrayIterator(array(1, 2, 3));
        $reflection = Invokable::callableToReflection(array($obj, 'count'));
        $this->assertEquals('count', $reflection->getName());
        $this->assertEquals('ArrayIterator', $reflection->class);
        $this->assertEquals(3, $reflection->invoke($obj));
        $this->assertInstanceOf('ReflectionMethod', $reflection);
    }

    public function testCallableToReflectionClosure()
    {
        $closure = function() { return 'foo'; };
        $reflection = Invokable::callableToReflection($closure);
        $this->assertEquals('{closure}', $reflection->getName());
        $this->assertEquals('foo', $reflection->invoke());
        $this->assertInstanceOf('ReflectionFunction', $reflection);
    }

    public function testCallableToReflectionInvokable()
    {
        $invokable = new InvokableTestSample();
        $reflection = Invokable::callableToReflection($invokable);
        $this->assertEquals('__invoke', $reflection->getName());
        $this->assertEquals('InvokableTestSample', $reflection->class);
        $this->assertEquals('InvokableTestSample', $reflection->invoke($invokable));
        $this->assertInstanceOf('ReflectionMethod', $reflection);
    }

    public function testGetParams()
    {
        $closure = function($int = 64) { return $int; };
        $invokable = new Invokable($closure);
        $this->assertEquals(array('int' => 64), $invokable->getParams());
    }

    public function testInvoke()
    {
        $closure = function($int = 64) { return $int; };
        $invokable = new Invokable($closure);
        $response = $invokable(array('int' => 32));
        $this->assertEquals(32, $response);
    }

    public function testRun()
    {
        $return = Invokable::run(function($foo) {return $foo; }, array('foo' => 'bar'));
        $this->assertEquals('bar', $return);
    }

    public function testSetParam()
    {
        $closure = function($int = 64, $array = array(1, 2, 3)) { return compact('int', 'array'); };
        $invokable = new Invokable($closure);
        $params = array('array' => array(1, 2));
        $invokable->setParams($params);

        $this->assertEquals(array('int' => 64, 'array' => array(1, 2)), $invokable->getParams());

        $this->assertEquals(array('int' => 64, 'array' => array(1, 2)), $invokable());
    }

    public function testToStringClosure()
    {
        $closure = function(){};
        $invokable = new Invokable($closure);
        $this->assertEquals("{closure}", (string) $invokable);
    }

    public function testToStringFunction()
    {
        $invokable = new Invokable('rand');
        $this->assertEquals("rand", (string) $invokable);
    }

    public function testToStringStatic()
    {
        $invokable = new Invokable('ReflectionClass::export');
        $this->assertEquals('ReflectionClass::export', (string) $invokable);
    }

    public function testToStringObject()
    {
        $invokable = new Invokable(array(new ArrayIterator(), 'count'));
        $this->assertEquals('ArrayIterator->count', (string) $invokable);
    }

    /**
     * @expectedException BadFunctionCallException
     */
    public function testFunctionNotFound()
    {
        $invokable = new Invokable('function_not_found');
        $invokable();
    }
}
