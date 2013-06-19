<?php

namespace Sinergia;

use SplFileInfo, ArrayAccess;

/**
 * Class Paths to store relative paths to a root one.
 *
 * @package Sinergia
 * @property SplFileInfo $tpl
 * @property SplFileInfo $sqlite
 * @property SplFileInfo $php
 * @property SplFileInfo $bin
 * @property SplFileInfo $assets
 * @property
 *
 */
class Paths implements ArrayAccess
{
    protected $paths = array();

    public function __construct($root)
    {
        $this->root = $root;
    }

    /**
     * @param  string      $name
     * @param  string      $path
     * @return SplFileInfo
     */
    public function set($name, $path)
    {
        return $this->paths[$name] = $this->forceSplInfo($path);
    }

    /**
     * @param $name
     * @return SplFileInfo
     */
    public function get($name)
    {
        return @$this->paths[$name] ?: $this->set($name, $name);
    }

    /**
     * @param  string      $name
     * @param  string      $path
     * @return SplFileInfo
     */
    public function __set($name, $path)
    {
        return $this->set($name, $path);
    }

    /**
     * @param $name
     * @return SplFileInfo
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     * @param  string      $method
     * @param  string      $args
     * @return SplFileInfo
     */
    public function __call($method, $args = array())
    {
        if (substr($method, 0, 3) == 'get') {
            $method = strtolower(substr($method, 3));

            return $this->get($method);
        } elseif (substr($method, 0, 3) == 'set') {
            $method = strtolower(substr($method, 3));
            $this->set($method, reset($args));
        } else {
            return $method;
        }
    }

    public function toArray()
    {
        return $this->paths;
    }

    protected function forceSplInfo($path)
    {
        if (! $path instanceof SplFileInfo) {
            $path = $this->replaceTemplate($path);
            if ($path[0] != '/') {
                $path = "{$this->root}/$path";
            }
            $path = new SplFileInfo($path);
        }

        return $path;
    }

    protected function replaceTemplate($path)
    {
        $paths = $this;
        $replacer = function($matches) use ($paths) {
            $name = end($matches);

            return $paths->get($name);
        };

        return preg_replace_callback("!\\{(.+)\\}!", $replacer, $path);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists($offset)
    {
        return isset($this->paths[$offset]);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return SplFileInfo
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->paths[$offset]);
    }
}
