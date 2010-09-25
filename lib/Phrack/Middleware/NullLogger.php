<?php
require_once('Phrack/Middleware.php');

class Phrack_Middleware_NullLogger extends Phrack_Middleware
{
    public function call(&$environ)
    {
        $environ['phsgix.logger'] = new Phrack_Middleware_NullLogger_Logger();
        return $this->callApp($environ);
    }

    static public function wrap()
    {
        $args =& func_get_args();
        return parent::wrap(__CLASS__, $args);
    }
}

class Phrack_Middleware_NullLogger_Logger
{
    public function debug  ($message) {}
    public function info   ($message) {}
    public function notice ($message) {}
    public function warn   ($message) {}
    public function err    ($message) {}
    public function crit   ($message) {}
    public function alert  ($message) {}
    public function emerg  ($message) {}
}
