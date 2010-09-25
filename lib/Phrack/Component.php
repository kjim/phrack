<?php
require_once('Phrack/Util.php');

abstract class Phrack_Component
{
    protected $app;
    protected $args;

    public function __construct($app, array $args = array())
    {
        foreach ($args as $attr => $value) {
            $this->$attr = $value;
        }

        $this->app = $app;
        $this->args =& $args;
    }

    abstract public function call(&$environ);

    protected function callApp(&$environ)
    {
        return Phrack_Util::callApp($this->app, $environ);
    }

    protected function prepareApp()
    { }

    public function toApp()
    {
        $this->prepareApp();
        return array($this, 'call');
    }
}
