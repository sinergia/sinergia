<?php

namespace Sinergia;

class Helpers
{
    public static function h($s)
    {
        return htmlspecialchars((string) $s);
    }

    /**
     * @param $__FILE__
     * @param  mixed  $__VARS__
     * @return string | null
     */
    public static function render($__FILE__, $__VARS__ = array())
    {
        // convert SplFile to string
        $__FILE__ = (string) $__FILE__;

        // add .php extensions when missing
        if ( pathinfo($__FILE__, PATHINFO_EXTENSION) != 'php') {
            $__FILE__ .= ".php";
        }

        if ( ! stream_resolve_include_path($__FILE__) ) {
            return null;
        }

        $callback = is_callable($__VARS__)
            ? $__VARS__
            : function($__FILE__, $__VARS__) {
                extract((array) $__VARS__);
                include $__FILE__;
            };

        $render = new Render();
        $return = $render($callback, $__FILE__, $__VARS__);

        return (string) $return;
    }
}
