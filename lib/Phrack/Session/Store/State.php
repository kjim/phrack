<?php

class Phrack_Session_State
{
    protected $sessionName;
    protected $options;

    public function __construct(array $options = array())
    {
        $options = array_merge(
            array('session_name' => 'phrack_session'),
            $options);

        $this->sessionName = $options['session_name'];
        $this->options =& $options;
    }

    public function getSessionId(&$environ)
    { }

    public function extract(&$environ)
    { }

    public function generate(&$environ)
    { }

    public function expireSessionId($id, &$res, &$options)
    { }

    public function finalize($id, &$res, &$options)
    { }
}
