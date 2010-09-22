<?php
require_once('Phrack/Middleware.php');
require_once('Phrack/Util.php');
require_once('Phrack/HTTP/Status.php');

class Phrack_Middleware_ContentLength extends Phrack_Middleware
{
    public function call(&$environ)
    {
        $res = $this->callApp($environ);
        return $this->responseCb($res, array($this, 'process'));
    }

    public function process(&$res)
    {
        $status = (int) $res[0];

        $h = Phrack_Util::headers($res[1]);
        if (!Phrack_HTTP_Status::hasNoEntityBody($status) &&
            !$h->exists('Content-Length') &&
            !$h->exists('Transfer-Encoding') &&
            ($contentLength = Phrack_Util::contentLength($res[2]))) {
            $h->push('Content-Length', $contentLength);
        }
    }

    static public function wrap()
    {
        $args =& func_get_args();
        return parent::wrap(__CLASS__, $args);
    }
}
