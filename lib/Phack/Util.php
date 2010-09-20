<?php
require_once('Phack/HTTP/Status.php');

class Phack_Util
{
    static public function statusText($status)
    {
        return Phack_HTTP_Status::statusMessage($status);
    }

    static public function contentLength($body)
    {
        if ($body === null) {
            return;
        }

        if (is_array($body)) {
            $cl = 0;
            foreach ($body as $chunk) {
                $cl += mb_strlen($chunk);
            }
            return $cl;
        }

        return;
    }

    static public function callApp($app, &$env)
    {
        return call_user_func_array($app, array(&$env));
    }

    static public function headers(&$headers)
    {
        return new Phack_Util_Headers($headers);
    }

    static public function headerIter(&$headers, $callback)
    {
        foreach ($headers as &$header) {
            call_user_func_array($callback, $header);
        }
    }

    static public function headerGet(&$headers, $key)
    {
        $key = strtolower($key);

        $vals = array();
        foreach ($headers as $header) {
            if (strtolower($header[0]) === $key) {
                $vals[] = $header[1];
            }
        }
        return $vals;
    }

    static public function headerSet(&$headers, $key, $value)
    {
        $k = strtolower($key);

        $newHeaders = array();
        $set = false;
        foreach ($headers as $header) {
            if (!$set && strtolower($header[0]) === $k) {
                $header[1] = $value;
                $set = true;
            }
            $newHeaders[] = $header;
        }

        if (!$set) {
            $newHeaders[] = array($key, $value);
        }
        $headers = $newHeaders;
    }

    static public function headerPush(&$headers, $key, $value)
    {
        $headers[] = array($key, $value);
    }

    static public function headerExists(&$headers, $key)
    {
        $key = strtolower($key);
        foreach ($headers as $header) {
            if (strtolower($header[0]) === $key) {
                return true;
            }
        }
        return false;
    }

    static public function headerRemove(&$headers, $key)
    {
        $key = strtolower($key);

        $newHeaders = array();
        foreach ($headers as $header) {
            if (strtolower($header[0]) !== $key) {
                $newHeaders[] = $header;
            }
        }

        $headers = $newHeaders;
    }

    static public function responseCb($res, $cb)
    {
        if (is_array($res)) {
            self::_responseCbBodyFilter($cb, $res);
        }
        return $res;
    }

    static private function _responseCbBodyFilter($cb, &$res)
    {
        call_user_func_array($cb, array(&$res));
    }
}

class Phack_Util_Prototype
{
    protected $callbacks;

    public function __construct(array $callbacks)
    {
        $this->callbacks = $callbacks;
    }

    public function __call($attr, $args)
    {
        if (isset($this->callbacks[$attr]) && is_callable($this->callbacks[$attr])) {
            call_user_func_array($this->callbacks[$attr], $args);
        }
        else {
            throw new Exception(
                "Can't locate object method \"$attr\" via package \"Phack_Util_Prototype\"");
        }
    }
}

class Phack_Util_Headers
{
    protected $headers;

    public function __construct($headers)
    {
        $this->headers = $headers;
    }

    public function iter($callback)
    {
        return Phack_Util::headerIter($this->headers, $callback);
    }

    public function get($key)
    {
        return Phack_Util::headerGet($this->headers, $key);
    }

    public function set($key, $value)
    {
        return Phack_Util::headerSet($this->headers, $key, $value);
    }

    public function push($key, $value)
    {
        return Phack_Util::headerPush($this->headers, $key, $value);
    }

    public function exists($key)
    {
        return Phack_Util::headerExists($this->headers, $key);
    }

    public function remove($key)
    {
        return Phack_Util::headerRemove($this->headers, $key);
    }

    public function headers()
    {
        return $this->headers;
    }
}
