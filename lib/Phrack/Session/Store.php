<?php

/**
 * Basic in-memory session store
 */
class Phrack_Session_Store
{
    protected $stash;

    public function __construct(array $stash = array())
    {
        $this->stash =& $stash;
    }

    public function fetch($sessionId)
    {
        return $this->stash[$sessionId];
    }

    public function store($sessionId, &$session)
    {
        $this->stash[$sessionId] = &$session;
    }

    public function remove($sessionId)
    {
        unset($this->stash[$sessionId]);
    }
}
