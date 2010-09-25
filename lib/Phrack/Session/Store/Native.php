<?php
require_once('Phrack/Session/Store.php');

class Phrack_Session_Store_Native extends Phrack_Session_Store
{
    protected function setSessionHandler($onOpen, $onClose, $onFetch, $onStore, $onPurge, $onGC)
    {
        session_set_save_handler($onOpen, $onClose, $onFetch, $onStore, $onPurge, $onGC);
    }

    public function fetch($sessionId)
    {
        return $_SESSION;
    }

    public function store($sessionId, &$session)
    {
        $_SESSION = $session;
    }

    public function remove($sessionId)
    {
        $_SESSION = array();
    }

    public function commit()
    {
    }
}
