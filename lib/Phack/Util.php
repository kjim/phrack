<?php
require_once('Phack/HTTP/Status.php');

class Phack_Util
{
    static public function statusText($status)
    {
        return $status . ' ' . Phack_HTTP_Status::statusMessage($status);
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
        else if (self::isFileHandle($body)) {
            $stat = fstat($body);
            $size = $stat['size'];
            return $size - ftell($body);
        }

        return;
    }

    static public function isFileHandle($resource)
    {
        return is_resource($resource) && in_array(get_resource_type($resource), array('stream', 'file'));
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

    static public function fcopy($fpwrite, $fpread, $length = 8192)
    {
        while ( !feof($fpread) ) {
            fwrite($fpwrite, fread($fpread, $length));
        }
    }

    static public function responseCb(&$res, $cb, array $args = array())
    {
        if (is_array($res)) {
            self::_responseCbBodyFilter($cb, $res, $args);
        }
        return $res;
    }

    static private function _responseCbBodyFilter($cb, &$res, &$args)
    {
        call_user_func_array($cb, array(&$res, &$args));
    }

    /* The following methods are derived from code of the Symfony2 (preview3) */

    /**
     * Fixes a malformed PHP $_FILES array.
     *
     * PHP has a bug that the format of the $_FILES array differs, depending on
     * whether the uploaded file fields had normal field names or array-like
     * field names ("normal" vs. "parent[child]").
     *
     * This method fixes the array to look like the "normal" $_FILES array.
     *
     * It's safe to pass an already converted array, in which case this method
     * just returns the original array unmodified.
     *
     * @param  array $data
     * @return array
     */
    static public function fixPhpFilesArray($data)
    {
        if (!is_array($data)) {
            return $data;
        }    

        $fileKeys = array('error', 'name', 'size', 'tmp_name', 'type');
        $keys = array_keys($data);
        sort($keys);

        if ($fileKeys != $keys || !isset($data['name']) || !is_array($data['name'])) {
            return $data;
        }

        $files = $data;
        foreach ($fileKeys as $k) {
            unset($files[$k]);
        }
        foreach (array_keys($data['name']) as $key) {
            $files[$key] = self::fixPhpFilesArray(array(
                'error'    => $data['error'][$key],
                'name'     => $data['name'][$key],
                'type'     => $data['type'][$key],
                'tmp_name' => $data['tmp_name'][$key],
                'size'     => $data['size'][$key],
            ));
        }

        return $files;
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

    public function __construct(&$headers)
    {
        $this->headers =& $headers;
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
