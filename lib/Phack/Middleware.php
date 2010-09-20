<?php
require_once('Phack/Component.php');
require_once('Phack/Util.php');

abstract class Phack_Middleware extends Phack_Component
{
    static protected function wrap($class, $app, array $args = array())
    {
        $self = new $class($app, $args);
        return $self->toApp();
    }

    protected function responseCb($res, $cb)
    {
        return Phack_Util::responseCb($res, $cb);
    }
}
