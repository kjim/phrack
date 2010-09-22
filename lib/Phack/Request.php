<?php
require_once('Phack/Request/Upload.php');
require_once('Phack/Util.php');
require_once('Phack/Util/MultiDict.php');

class Phack_Request
{
    protected $environ;
    protected $headers;

    public function __construct(&$environ)
    {
        $this->environ =& $environ;
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
        if (!isset($this->environ['phack.cookies'])) {
            $this->environ['phack.cookies'] = $_COOKIE;
        }
        return $this->environ['phack.cookies'];
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
        if (!isset($this->environ['phack.request.query'])) {
            $dict = new Phack_Util_MultiDict();
            $dict->mergeMixedDict($_GET);
            $this->environ['phack.request.query'] = $dict;
        }
        return $this->environ['phack.request.query'];
    }

    public function getBodyParameters()
    {
        if (!isset($this->environ['phack.request.body'])) {
            $dict = new Phack_Util_MultiDict();
            $dict->mergeMixedDict($_POST);
            $this->environ['phack.request.body'] = $dict;
        }
        return $this->environ['phack.request.body'];
    }

    public function getParameters()
    {
        if (!isset($this->environ['phack.request.merged'])) {
            $merged = new Phack_Util_MultiDict();
            $merged->merge($this->getQueryParameters()->items());
            $merged->merge($this->getBodyParameters()->items());
            $this->environ['phack.request.merged'] = $merged;
        }
        return $this->environ['phack.request.merged'];
    }

    public function getUploads()
    {
        if (!isset($environ['phack.request.upload'])) {
            $upload = new Phack_Util_MultiDict();
            $upload->mergeMixedDict($this->convertFileInformation($_FILES));
            $this->environ['phack.request.upload'] = $upload;
        }
        return $this->environ['phack.request.upload'];
    }

    /* The following methods are derived from code of the Symfony2 (preview3) */

    /**
     * Converts uploaded files to Phack_Request_Upload instances.
     *
     * @param  array $files A (multi-dimensional) array of uploaded file information
     *
     * @return array A (multi-dimensional) array of Phack_Request_Upload instances
     */
    public function convertFileInformation(array $files)
    {
        $fixedFiles = array();

        foreach ($files as $key => $data) {
            $fixedFiles[$key] = Phack_Util::fixPhpFilesArray($data);
        }

        $fileKeys = array('error', 'name', 'size', 'tmp_name', 'type');
        foreach ($fixedFiles as $key => $data) {
            if (is_array($data)) {
                $keys = array_keys($data);
                sort($keys);

                if ($keys == $fileKeys) {
                    $fixedFiles[$key] = new Phack_Request_Upload($data['tmp_name'], $data['size'], $data['name'], $data['error']);
                } else {
                    $fixedFiles[$key] = $this->convertFileInformation($data);
                }
            }
        }

        return $fixedFiles;
    }
}
