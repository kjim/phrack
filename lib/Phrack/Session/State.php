<?php

abstract class Phrack_Session_State
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

    abstract public function extract(&$environ);

    abstract public function generate(&$environ);

    abstract public function expireSessionId($id, &$res, &$options);

    abstract public function finalize($id, &$res, &$options);
}
