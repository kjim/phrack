<?php
require_once('Phack/Component.php');

abstract class Phack_Middleware extends Phack_Component
{
    static protected function wrap(/* $class, $app, $args...*/)
    {
        $args = func_get_args();
        $class = array_shift($args);
        $app = array_shift($args);
        $self = new $class($app, $args);
        return $self->toApp();
    }
}
