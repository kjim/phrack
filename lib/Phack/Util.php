<?php

class Phack_Util
{
    static private $STATUS_TEXTS = array(
        100 => 'Continue',
        101 => 'Switching Protocols',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        );

    static public function statusText($status)
    {
        return self::$STATUS_TEXTS[$status];
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
}
