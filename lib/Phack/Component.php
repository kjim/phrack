<?php
require_once('Phack/Util.php');

abstract class Phack_Component
{
    protected $app;
    protected $args;

    public function __construct($app, array $args = array())
    {
        $this->app = $app;
        $this->args =& $args;
    }

    abstract public function call(&$environ);

    protected function callApp(&$environ)
    {
        return Phack_Util::callApp($this->app, $environ);
    }

    protected function prepareApp()
    { }

    public function toApp()
    {
        $this->prepareApp();
        return array($this, 'call');
    }
}
