<?php
require_once('Phrack/Component.php');
require_once('Phrack/Util.php');

abstract class Phrack_Middleware extends Phrack_Component
{
    static protected function wrap(/* $class, array $args... */)
    {
        list($class, $args) = func_get_args();
        $app  = array_shift($args);
        $args = array_shift($args);
        if (is_array($args)) {
            $self = new $class($app, $args);
        }
        else {
            $self = new $class($app);
        }
        return $self->toApp();
    }

    protected function responseCb(&$res, $cb, array $args = array())
    {
        return Phrack_Util::responseCb($res, $cb, $args);
    }
}
