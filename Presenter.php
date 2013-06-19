<?php

namespace Sinergia;

class Presenter extends Blank
{
    public function __invoke($__FILE__, $__VARS__ = array())
    {
        extract((array) $__VARS__);
        include $__FILE__;
    }
}
