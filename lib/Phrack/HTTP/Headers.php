<?php
require_once('Phrack/Util/MultiDict.php');

class Phrack_HTTP_Headers
{
    protected $headers;

    public function __construct($headers = array())
    {
        $this->_headers($headers);
    }

    public function setHeaders($headers)
    {
        $this->_headers($headers);
    }

    public function addHeader($key, /* scalar or array*/$value)
    {
        $this->headers->mergeMixedDict(array($key => $value));
    }

    public function setHeader($key, $value)
    {
        $this->headers->remove($key);
        $this->headers->add($key, $value);
    }

    public function getHeader($key)
    {
        return $this->headers->get($key);
    }

    public function getHeaderAll($key)
    {
        return $this->headers->getAll($key);
    }

    public function items()
    {
        return $this->headers->items();
    }

    protected function _headers(&$headers)
    {
        if (is_array($headers)) {
            $meth = null;
            foreach ($headers as $index => $_) {
                $meth = is_int($index) ? 'merge' : 'mergeMixedDict';
                break;
            }

            $h = $this->dict();
            if ($meth !== null) {
                $h->$meth($headers);
            }
            $this->headers = $h;
        }
        else if ($headers !== null) {
            $this->headers = $headers;
        }
        else {
            $this->headers = $this->dict();
        }
    }

    protected function dict()
    {
        return new Phrack_Util_MultiDict();
    }

    /* CONVENIENCE METHODS */
    public function setContentEncoding ($v) { $this->setHeader('Content-Encoding', $v); }
    public function setContentLanguage ($v) { $this->setHeader('Content-Language', $v); }
    public function setContentType     ($v) { $this->setHeader('Content-Type', $v); }
    public function setContentLength   ($v) { $this->setHeader('Content-Length', $v); }
    public function setUserAgent       ($v) { $this->setHeader('User-Agent', $v); }
    public function setServer          ($v) { $this->setHeader('Server', $v); }
    public function setLocation        ($v) { $this->setHeader('Location', $v); }

    public function getContentEncoding () { return $this->getHeader('Content-Encoding'); }
    public function getContentLanguage () { return $this->getHeader('Content-Language'); }
    public function getContentType     () { return $this->getHeader('Content-Type'); }
    public function getContentLength   () { return $this->getHeader('Content-Length'); }
    public function getUserAgent       () { return $this->getHeader('User-Agent'); }
    public function getServer          () { return $this->getHeader('Server'); }
    public function getLocation        () { return $this->getHeader('Location'); }
}
