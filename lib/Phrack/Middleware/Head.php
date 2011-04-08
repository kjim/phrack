<?php
require_once('Phrack/Middleware.php');

class Phrack_Middleware_Head extends Phrack_Middleware
{
    public function call(&$environ)
    {
        $res = $this->callApp($environ);
        if ($environ['REQUEST_METHOD'] !== 'HEAD') {
            return $res;
        }

        return $this->responseCb($res, array($this, 'process'));
    }

    public function process(&$res)
    {
        $res[2] = array();
    }

    static public function wrap()
    {
        $args = func_get_args();
        return parent::wrap(__CLASS__, $args);
    }
}
