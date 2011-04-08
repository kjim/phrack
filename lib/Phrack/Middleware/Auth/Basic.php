<?php
require_once('Phrack/Middleware.php');

class Phrack_Middleware_Auth_Basic extends Phrack_Middleware
{
    protected $authenticator;
    protected $realm;

    protected function prepareApp()
    {
        if (!$this->authenticator) {
            throw new Exception("authenticator is not set");
        }

        $authenticator = $this->authenticator;
        if (is_object($authenticator) && method_exists($authenticator, 'authenticate')) {
            $authenticator = array($authenticator, 'authenticate');
        }
        if (!is_callable($authenticator)) {
            throw new Exception("authenticator should be a callable object or an object that respond to authenticate()");
        }

        $this->authenticator = $authenticator;
    }

    public function call(&$environ)
    {
        if ( !(isset($environ['PHP_AUTH_USER']) && isset($environ['PHP_AUTH_PW'])) ) {
            return $this->unauthorized();
        }

        $user = $environ['PHP_AUTH_USER'];
        $pass = $environ['PHP_AUTH_PW'];
        if (call_user_func($this->authenticator, $user, $pass)) {
            $environ['REMOTE_USER'] = $user;
            return $this->callApp($environ);
        }

        return $this->unauthorized();
    }

    protected function unauthorized()
    {
        $body = 'Authorization required';
        $realm = $this->realm ? $this->realm : "restricted area";
        return array(
            Phrack_Util::statusText(401),
            array(
                array('Content-Type', 'text/plain'),
                array('Content-Length', mb_strlen($body)),
                array('WWW-Authenticate', 'Basic realm="' . $realm . '"'),
                ),
            array($body),
            );
    }

    static public function wrap()
    {
        $args = func_get_args();
        return parent::wrap(__CLASS__, $args);
    }
}
