<?php
require_once('Phrack/Middleware.php');
require_once('Phrack/Util.php');

class Phrack_Middleware_Runtime extends Phrack_Middleware
{
    public function call(&$environ)
    {
        $start = microtime(true);
        $res = $this->callApp($environ);
        return $this->responseCb($res, array($this, 'process'), array($start));
    }

    public function process(&$res, &$args)
    {
        $reqtime = sprintf('%.6f', microtime(true) - $args[0]);
        Phrack_Util::headerSet($res[1], 'X-Runtime', $reqtime);
    }

    static public function wrap()
    {
        $args =& func_get_args();
        return parent::wrap(__CLASS__, $args);
    }
}
