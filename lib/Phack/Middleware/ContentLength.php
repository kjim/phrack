<?php
require_once('Phack/Middleware.php');
require_once('Phack/Util.php');
require_once('Phack/HTTP/Status.php');

class Phack_Middleware_ContentLength extends Phack_Middleware
{
    public function call(&$environ)
    {
        $res = $this->callApp($environ);
        return $this->responseCb($res, array($this, 'process'));
    }

    public function process(&$res)
    {
        $status = (int) $res[0];

        $h = Phack_Util::headers($res[1]);
        if (!Phack_HTTP_Status::hasNoEntityBody($status) &&
            !$h->exists('Content-Length') &&
            !$h->exists('Transfer-Encoding') &&
            ($contentLength = Phack_Util::contentLength($res[2]))) {
            $h->push('Content-Length', $contentLength);
        }
    }

    static public function wrap()
    {
        $args =& func_get_args();
        return parent::wrap(__CLASS__, $args);
    }
}
