<?php
require_once('Phrack/Session/State.php');

class Phrack_Session_State_Native extends Phrack_Session_State
{
    static private $sessionStarted;

    public function __construct(array $options = array())
    {
        if (self::$sessionStarted) {
            return;
        }

        $defaults = session_get_cookie_params();

        $this->options = array_merge(array(
            'session_name'            => 'phrack_session',
            'session_cookie_lifetime' => $defaults['lifetime'],
            'session_cookie_path'     => $defaults['path'],
            'session_cookie_domain'   => $defaults['domain'],
            'session_cookie_secure'   => $defaults['secure'],
            'session_cookie_httponly' => isset($defaults['httponly']) ? $defaults['httponly'] : false,
            'session_cache_limiter'   => 'none',
        ), $options);

        session_name($this->options['session_name']);

        session_set_cookie_params(
            $this->options['session_cookie_lifetime'],
            $this->options['session_cookie_path'],
            $this->options['session_cookie_domain'],
            $this->options['session_cookie_secure'],
            $this->options['session_cookie_httponly']
            );

        if ($this->options['session_cache_limiter'] !== null) {
            session_cache_limiter($this->options['session_cache_limiter']);
        }

        if (!ini_get('session.use_cookies') &&
            $this->options['session_id'] &&
            $this->options['session_id'] != session_id()) {
            session_id($this->options['session_id']);
        }
    }

    protected function start()
    {
        if (self::$sessionStarted) {
            return;
        }

        self::$sessionStarted = true;
        session_start();
    }

    protected function getSessionId(&$environ)
    {
        return session_id();
    }

    public function extract(&$environ)
    {
        $this->start();
        return $this->getSessionId($environ);
    }

    public function generate(&$environ)
    {
        $this->start();
        return session_id();
    }

    public function expireSessionId($id, &$res, &$options)
    {
        $this->start();
    }

    public function finalize($id, &$res, &$options)
    {
        $this->start();
    }
}
