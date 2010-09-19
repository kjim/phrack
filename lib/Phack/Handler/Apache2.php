<?php
require_once('Phack/Handler.php');
require_once('Phack/Util.php');

class Phack_Handler_Apache2 implements Phack_Handler
{
    protected $baseUrl;
    protected $requestUri;

    public function __construct()
    { }

    public function run($app)
    {
        $env = array_merge(
            array(
                'SCRIPT_FILENAME'   => '',
                'SCRIPT_NAME'       => '',
                'PHP_SELF'          => '',
                'ORIG_SCRIPT_NAME'  => '',
                'SERVER_PROTOCOL'   => 'HTTP/1.0',
                ),
            $_SERVER,
            array(
                'phsgi.version'        => array(1, 0),
                'phsgi.url_scheme'     => (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) ? 'https' : 'http'),
                'phsgi.input'          => STDIN,
                'phsgi.errors'         => STDERR,
                'phsgi.multithread'    => false,
                'phsgi.multiprocess'   => true,
                'phsgi.run_once'       => true,
                ));

        $this->fixupPath($env);

        $res = call_user_func_array($app, array(&$env));

        if (is_array($res) || (is_object($res) && $res instanceof Traversable)) {
            $this->handleResponse($res, $env);
        }
        else {
            throw new Exception("Bad response " . (is_object($res) ? get_class($res) : $res));
        }
    }

    protected function handleResponse(&$res, &$env)
    {
        list($status, $headers, $body) = $res;

        header($env['SERVER_PROTOCOL'] . ' ' . $status);
        foreach ($headers as $header) {
            list($k, $v) = $header;
            header($k.': '.$v);
        }
        foreach ($body as $e) {
            echo($e);
        }
    }

    protected function fixupPath(&$env)
    {
        $env['PATH_INFO'] = $this->fixPathInfo($env);
    }

    protected function fixPathInfo(&$env)
    {
        $baseUrl = $this->getBaseUrl($env);

        if (null === ($requestUri = $this->getRequestUri($env))) {
            return '';
        }

        $pathInfo = '';

        // Remove the query string from REQUEST_URI
        if ($pos = strpos($requestUri, '?')) {
            $requestUri = substr($requestUri, 0, $pos);
        }

        if ((null !== $baseUrl) && (false === ($pathInfo = substr($requestUri, strlen($baseUrl))))) {
            // If substr() returns false then PATH_INFO is set to an empty string
            return '';
        } elseif (null === $baseUrl) {
            return $requestUri;
        }

        return (string) $pathInfo;
    }

    protected function getRequestUri(&$env)
    {
        if ($this->requestUri === null) {
            $this->requestUri = $this->prepareRequestUri($env);
        }
        return $this->requestUri;
    }

    protected function getBaseUrl(&$env)
    {
        if ($this->baseUrl === null) {
            $this->baseUrl = $this->prepareBaseUrl($env);
        }
        return $this->baseUrl;
    }

    /*
     * The following methods are derived from code of the Zend Framework (1.10dev - 2010-01-24)
     *
     * Code subject to the new BSD license (http://framework.zend.com/license/new-bsd).
     *
     * Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
     */
    protected function prepareRequestUri(&$env)
    {
        $requestUri = '';

        if (isset($env['REQUEST_URI'])) {
            $requestUri = $env['REQUEST_URI'];
            // HTTP proxy reqs setup request uri with scheme and host [and port] + the url path, only use url path
            $schemeAndHttpHost = $env['phsgi.url_scheme'].'://'.$env['HTTP_HOST'];
            if (strpos($requestUri, $schemeAndHttpHost) === 0) {
                $requestUri = substr($requestUri, strlen($schemeAndHttpHost));
            }
        }

        return $requestUri;
    }

    protected function prepareBaseUrl(&$env)
    {
        $baseUrl = '';

        $filename = basename($env['SCRIPT_FILENAME']);

        if (basename($env['SCRIPT_NAME']) === $filename) {
            $baseUrl = $env['SCRIPT_NAME'];
        }
        else if (basename($env['PHP_SELF']) === $filename) {
            $baseUrl = $env['PHP_SELF'];
        }
        else if (basename($env['ORIG_SCRIPT_NAME']) === $filename) {
            $baseUrl = $env['ORIG_SCRIPT_NAME'];
        }
        else {
            // Backtrack up the script_filename to find the portion matching
            // php_self
            $path    = $env['PHP_SELF'];
            $file    = $env['SCRIPT_FILENAME'];
            $segs    = explode('/', trim($file, '/'));
            $segs    = array_reverse($segs);
            $index   = 0;
            $last    = count($segs);
            $baseUrl = '';
            do {
                $seg     = $segs[$index];
                $baseUrl = '/'.$seg.$baseUrl;
                ++$index;
            } while (($last > $index) && (false !== ($pos = strpos($path, $baseUrl))) && (0 != $pos));
        }

        // Does the baseUrl have anything in common with the request_uri?
        $requestUri = $this->getRequestUri($env);

        if ($baseUrl && 0 === strpos($requestUri, $baseUrl)) {
            // full $baseUrl matches
            return $baseUrl;
        }

        if ($baseUrl && 0 === strpos($requestUri, dirname($baseUrl))) {
            // directory portion of $baseUrl matches
            return rtrim(dirname($baseUrl), '/');
        }

        $truncatedRequestUri = $requestUri;
        if (($pos = strpos($requestUri, '?')) !== false) {
            $truncatedRequestUri = substr($requestUri, 0, $pos);
        }

        $basename = basename($baseUrl);
        if (empty($basename) || !strpos($truncatedRequestUri, $basename)) {
            // no match whatsoever; set it blank
            return '';
        }

        // If using mod_rewrite or ISAPI_Rewrite strip the script filename
        // out of baseUrl. $pos !== 0 makes sure it is not matching a value
        // from PATH_INFO or QUERY_STRING
        if ((strlen($requestUri) >= strlen($baseUrl)) && ((false !== ($pos = strpos($requestUri, $baseUrl))) && ($pos !== 0))) {
            $baseUrl = substr($requestUri, 0, $pos + strlen($baseUrl));
        }

        return rtrim($baseUrl, '/');
    }
}
