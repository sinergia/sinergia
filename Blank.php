<?php

namespace Sinergia;

use ArrayAccess,
    IteratorAggregate,
    ArrayIterator;

class Blank implements ArrayAccess, IteratorAggregate
{
    public function __call($name, $args = array()) { return $this; }
    public static function __callStatic($name, $args = array()) { return new static(); }
    public function __invoke() { return $this; }
    public function __get($name) { return $this; }
    public function __set($name, $value) { $this->$name = $value; }
    public function __unset($name) { unset($this->$name); }
    public function offsetExists($offset) { return false; }
    public function offsetGet($offset) { return $this; }
    public function offsetSet($offset, $value) { return $this; }
    public function offsetUnset($offset) { return $this; }
    public function getIterator() { return new ArrayIterator(); }
    public function __toString() { return get_called_class(); }
}
