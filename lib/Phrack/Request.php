<?php
require_once('Phrack/Request/Upload.php');
require_once('Phrack/Util.php');
require_once('Phrack/Util/MultiDict.php');

class Phrack_Request
{
    protected $environ;
    protected $headers;

    public function __construct(&$environ)
    {
        $this->environ =& $environ;
        $this->getHeaders();
        $this->initialize();
    }

    protected function initialize()
    { }

    public function environ()
    {
        return $this->environ;
    }

    protected function getenv($key, $default = null)
    {
        return isset($this->environ[$key]) ? $this->environ[$key] : $default;
    }

    protected function getheader($key, $default = null)
    {
        return isset($this->headers[$key]) ? $this->headers[$key] : $default;
    }

    public function getServerName() { return $this->getenv('SERVER_NAME'); }
    public function getServerAddr() { return $this->getenv('SERVER_ADDR'); }
    public function getServerPort() { return $this->getenv('SERVER_PORT'); }
    public function getHost()       { return $this->getheader('HTTP_HOST'); }

    public function getAddress()    { return $this->getenv('REMOTE_ADDR'); }
    public function getRemoteHost() { return $this->getenv('REMOTE_HOST'); }
    public function getProtocol()   { return $this->getenv('SERVER_PROTOCOL'); }
    public function getMethod()     { return $this->getenv('REQUEST_METHOD'); }
    public function getPort()       { return $this->getenv('SERVER_PORT'); }
    public function getUser()       { return $this->getenv('REMOTE_USER',
                                                           $this->getenv('PHP_AUTH_USER',
                                                                         $this->getenv('PHP_AUTH_DIGEST'))); }
    public function getRequestUri() { return $this->getenv('REQUEST_URI'); }
    public function getPathInfo()   { return $this->getenv('PATH_INFO'); }
    public function getPath()       { return $this->getenv('PATH_INFO', '/'); }
    public function getScriptName() { return $this->getenv('SCRIPT_NAME'); }
    public function getScheme()     { return $this->getenv('phsgi.url_scheme'); }
    public function isSecure()      { return $this->getScheme() === 'https'; }
    public function getBody()       { return $this->getenv('phsgi.input'); }
    public function getInput()      { return $this->getenv('phsgi.input'); }

    public function getContentLength() { return $this->getenv('CONTENT_LENGTH'); }
    public function getContentType()   { return $this->getenv('CONTENT_TYPE'); }

    public function getSession()        { return $this->getenv('phsgi.session'); }
    public function getSessionOptions() { return $this->getenv('phsgix.session.options'); }
    public function getLogger()         { return $this->getenv('phsgix.logger'); }

    public function getCookies()
    {
        if (!isset($this->environ['phrack.cookies'])) {
            $this->environ['phrack.cookies'] = $_COOKIE;
        }
        return $this->environ['phrack.cookies'];
    }

    public function getHeaders()
    {
        if (!$this->headers) {
            $headers = array();
            foreach ($this->environ as $key => $value) {
                if ('HTTP_' === substr($key, 0, 5)) {
                    $headers[$key] = $value;
                }
            }
            $this->headers = $headers;
        }
        return $this->headers;
    }

    public function getContentEncoding() { return $this->getheader('HTTP_CONTENT_ENCODING'); }
    public function getReferer()         { return $this->getheader('HTTP_REFERER'); }
    public function getUserAgent()       { return $this->getheader('HTTP_USER_AGENT'); }

    public function getQueryParameters()
    {
        if (!isset($this->environ['phrack.request.query'])) {
            $dict = new Phrack_Util_MultiDict();
            $dict->mergeMixedDict($_GET);
            $this->environ['phrack.request.query'] = $dict;
        }
        return $this->environ['phrack.request.query'];
    }

    public function getBodyParameters()
    {
        if (!isset($this->environ['phrack.request.body'])) {
            $dict = new Phrack_Util_MultiDict();
            $dict->mergeMixedDict($_POST);
            $this->environ['phrack.request.body'] = $dict;
        }
        return $this->environ['phrack.request.body'];
    }

    public function getParameters()
    {
        if (!isset($this->environ['phrack.request.merged'])) {
            $merged = new Phrack_Util_MultiDict();
            $merged->merge($this->getQueryParameters()->items());
            $merged->merge($this->getBodyParameters()->items());
            $this->environ['phrack.request.merged'] = $merged;
        }
        return $this->environ['phrack.request.merged'];
    }

    public function param($key)
    {
        return $this->getParameters()->get($key);
    }

    public function paramAll($key)
    {
        return $this->getParameters()->getAll($key);
    }

    public function getUploads()
    {
        if (!isset($environ['phrack.request.upload'])) {
            $upload = new Phrack_Util_MultiDict();
            $upload->mergeMixedDict($this->convertFileInformation($_FILES));
            $this->environ['phrack.request.upload'] = $upload;
        }
        return $this->environ['phrack.request.upload'];
    }

    public function getURI()
    {
        $base = $this->_getBaseURI();
        $path = rawurlencode($this->getenv('PATH_INFO', ''));
        if ($this->getenv('QUERY_STRING', '') !== '') {
            $path .= '?' . $this->getenv('QUERY_STRING');
        }

        return rtrim($base . $path, '/');
    }

    private function _getBaseURI()
    {
        $uri = $this->getenv('phsgi.url_scheme', 'http') .
            '://' .
            $this->getenv('HTTP_HOST',
                          $this->getenv('SERVER_NAME', '') .
                          (in_array($this->getenv('SERVER_PORT', 80), array(80, 443))
                           ? '' : ':' . $this->getenv('SERVER_PORT', 80))
                ) .
            $this->getenv('SCRIPT_NAME', '/');

        return $uri;
    }

    public function newResponse($status = 200, array $headers = array(), $content = array())
    {
        require_once('Phrack/Response.php');
        return new Phrack_Response($status, $headers, $content);
    }

    /* The following methods are derived from code of the Symfony2 (preview3) */

    /**
     * Converts uploaded files to Phrack_Request_Upload instances.
     *
     * @param  array $files A (multi-dimensional) array of uploaded file information
     *
     * @return array A (multi-dimensional) array of Phrack_Request_Upload instances
     */
    public function convertFileInformation(array $files)
    {
        $fixedFiles = array();

        foreach ($files as $key => $data) {
            $fixedFiles[$key] = Phrack_Util::fixPhpFilesArray($data);
        }

        $fileKeys = array('error', 'name', 'size', 'tmp_name', 'type');
        foreach ($fixedFiles as $key => $data) {
            if (is_array($data)) {
                $keys = array_keys($data);
                sort($keys);

                if ($keys == $fileKeys) {
                    $fixedFiles[$key] = new Phrack_Request_Upload($data['tmp_name'], $data['size'], $data['name'], $data['error']);
                } else {
                    $fixedFiles[$key] = $this->convertFileInformation($data);
                }
            }
        }

        return $fixedFiles;
    }
}
