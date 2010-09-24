<?php
require_once('Phrack/Middleware.php');
require_once('Phrack/HTTP/Status.php');

class Phrack_Middleware_HTTPExceptions extends Phrack_Middleware
{
    public function call(&$environ)
    {
        try {
            $res = $this->callApp($environ);
        }
        catch (Exception $e) {
            $res = $this->transformError($e, $environ);
        }

        return $res;
    }

    protected function transformError($e, &$environ)
    {
        $message = null;
        if (is_object($e) && $status = $this->statusText($e)) {
            $message = method_exists($e, '__toString') ? (string) $e : null;
        }
        else {
            $status = Phrack_Util::statusText(500);
            fwrite($environ['phsgi.errors'], (string) $e);
        }

        if (!preg_match('/^[3-5]\d\d/', $status)) {
            throw $e; // rethrow
        }

        $body = array();
        if ($message === null) {
            $body[] = $status;
        }

        $headers = array(
            array('Content-Type', 'text/plain'),
            array('Content-Length', $this->calcContentLength($body)));

        if (preg_match('/^3/', $status) && $loc = $this->location($e)) {
            $headers[] = array('Location', $loc);
            $body = array();
        }

        return array($status, $headers, $body);
    }

    protected function calcContentLength($body)
    {
        $len = 0;
        foreach ($body as $s) {
            $len += mb_strlen($s);
        }
        return $len;
    }

    protected function statusText($e)
    {
        if (method_exists($e, 'getStatus')) {
            return Phrack_Util::statusText($e->getStatus());
        }
        return null;
    }

    protected function location($e)
    {
        if (method_exists($e, 'getLocation')) {
            return $e->getLocation();
        }
        return null;
    }

    static public function wrap()
    {
        $args =& func_get_args();
        return parent::wrap(__CLASS__, $args);
    }
}
