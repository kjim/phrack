<?php
require_once('Phrack/Component.php');
require_once('Phrack/Util.php');

abstract class Phrack_Middleware extends Phrack_Component
{
    protected $app;

    public function __construct($app, array $args = array())
    {
        $this->app = $app;
        parent::__construct($args);
    }

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

    protected function callApp(&$environ)
    {
        return Phrack_Util::callApp($this->app, $environ);
    }

    protected function responseCb(&$res, $cb, array $args = array())
    {
        return Phrack_Util::responseCb($res, $cb, $args);
    }
}
