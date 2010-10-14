<?php
require_once('Phrack/Middleware.php');
require_once('Phrack/Util.php');

class Phrack_Middleware_Conditional extends Phrack_Middleware
{
    protected $condition;
    protected $middleware;
    protected $builder;

    protected function prepareApp()
    {
        $this->middleware = call_user_func($this->builder, $this->app);
    }

    public function call(&$environ)
    {
        $app = call_user_func($this->condition, $environ) ? $this->middleware : $this->app;
        return Phrack_Util::callApp($app, $environ);
    }

    static public function wrap()
    {
        $args =& func_get_args();
        return parent::wrap(__CLASS__, $args);
    }
}
