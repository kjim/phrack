<?php
require_once('Phack/Component.php');
require_once('Phack/Util.php');

abstract class Phack_Middleware extends Phack_Component
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

    protected function responseCb(&$res, $cb)
    {
        return Phack_Util::responseCb($res, $cb);
    }
}
