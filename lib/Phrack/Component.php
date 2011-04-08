<?php

abstract class Phrack_Component
{
    protected $args;

    public function __construct(array $args = array())
    {
        foreach ($args as $attr => $value) {
            $this->$attr = $value;
        }

        $this->args = $args;
    }

    abstract public function call(&$environ);

    protected function prepareApp()
    { }

    public function toApp()
    {
        $this->prepareApp();
        return array($this, 'call');
    }
}
