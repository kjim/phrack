<?php

class Phrack_Session
{
    protected $session;
    protected $options;

    public function __construct(&$environ)
    {
        $this->session =& $environ['phsgix.session'];
        $this->options =& $environ['phsgix.session.options'];
    }

    public function getId()
    {
        return $this->options['id'];
    }

    public function get($key, $default = null)
    {
        return array_key_exists($key, $this->session) ? $this->session[$key] : $default;
    }

    public function set($key, $value)
    {
        $this->session[$key] = $value;
    }

    public function remove($key)
    {
        unset($this->session[$key]);
    }

    public function keys()
    {
        return array_keys($this->session);
    }

    /**
     * Lifecycle Management
     */
    public function expire()
    {
        foreach ($this->keys() as $key) {
            unset($this->session[$key]);
        }
        $this->options['expire'] = true;
    }
}
