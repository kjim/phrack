<?php
require_once('Phrack/HTTP/Headers.php');
require_once('Phrack/Util.php');

class Phrack_Response
{
    protected $status;
    protected $headers;
    protected $body;
    protected $cookies;

    public function __construct($status = 200, array $headers = array(), $content = '')
    {
        $this->setStatus($status);
        $this->setHeaders($headers);
        $this->setBody($content);

        $this->cookies = array();
    }

    public function setStatus($status)
    {
        $this->status = (int) $status;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setHeaders($headers)
    {
        $this->headers = new Phrack_HTTP_Headers($headers);
    }

    public function addHeader($key, /* scalar or array*/$values)
    {
        $this->headers->addHeader($key, $values);
    }

    public function getHeader($key)
    {
        return $this->headers->getHeader($key);
    }

    public function setCookie($name, array $attributes)
    {
        $this->cookies[$name] =& $attributes;
    }

    public function getCookies()
    {
        return $this->cookies;
    }

    public function setBody($body)
    {
        $this->body = $body;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function redirect($location, $status = 302)
    {
        $this->setLocation($location);
        $this->setStatus($status);
        return $this->getLocation();
    }

    public function finalize()
    {
        if (!$this->getStatus()) {
            throw new RuntimeException("missing status");
        }

        $this->finalizeCookies();

        return array(Phrack_Util::statusText($this->status), $this->headers->items(), $this->_body());
    }

    protected function finalizeCookies()
    {
        foreach ($this->cookies as $name => $value) {
            $cookie = $this->bakeCookie($name, $value);
            $this->headers->addHeader('Set-Cookie', $cookie);
        }
    }

    protected function bakeCookie($name, $value)
    {
        if (empty($value)) {
            return '';
        }

        if (!is_array($value)) {
            $value = array('value' => $value);
        }

        $cookie = array(rawurlencode($name) . '=' . rawurlencode($value['value']));
        if (isset($value['domain']))   { $cookie[] = 'domain=' . $value['domain']; }
        if (isset($value['path']))     { $cookie[] = 'path=' . $value['path']; }
        if (isset($value['expires']))  { $cookie[] = 'expires=' . $this->_date($value['expires']); }
        if (isset($value['secure']))   { $cookie[] = 'secure'; }
        if (isset($value['HttpOnly'])) { $cookie[] = 'HttpOnly'; }

        return implode('; ', $cookie);
    }

    protected function _date($expires)
    {
        if (preg_match('/^\d+$/', $expires)) {
            $expires = gmdate('D, d-M-Y H:i:s', $expires) . ' GMT';
        }
        return $expires;
    }

    protected function _body()
    {
        if (is_scalar($this->body) ||
            is_object($this->body) && method_exists($this->body, '__toString')) {
            if ($this->body === '') {
                return array();
            }
            else {
                return array($this->body);
            }
        }
        else {
            return $this->body;
        }
    }

    /* CONVENIENCE METHODS */
    public function setContentEncoding ($v) { $this->headers->setContentEncoding($v); }
    public function setContentLanguage ($v) { $this->headers->setContentLanguage($v); }
    public function setContentType     ($v) { $this->headers->setContentType($v); }
    public function setContentLength   ($v) { $this->headers->setContentLength($v); }
    public function setUserAgent       ($v) { $this->headers->setUserAgent($v); }
    public function setServer          ($v) { $this->headers->setServer($v); }
    public function setLocation        ($v) { $this->headers->setLocation($v); }

    public function getContentEncoding () { return $this->headers->getContentEncoding(); }
    public function getContentLanguage () { return $this->headers->getContentLanguage(); }
    public function getContentType     () { return $this->headers->getContentType(); }
    public function getContentLength   () { return $this->headers->getContentLength(); }
    public function getUserAgent       () { return $this->headers->getUserAgent(); }
    public function getServer          () { return $this->headers->getServer(); }
    public function getLocation        () { return $this->headers->getLocation(); }
}
