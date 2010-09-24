<?php
require_once('Phrack/Session/Store.php');

class Phrack_Session_Store_Native extends Phrack_Session_Store
{
    public function fetch($sessionId)
    {
        return $_SESSION;
    }

    public function store($sessionId, &$session)
    {
        $_SESSION =& $session;
    }

    public function remove($sessionId)
    {
        $_SESSION = array();
    }
}
