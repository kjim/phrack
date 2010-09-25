<?php
require_once('Phrack/Middleware.php');

class Phrack_Middleware_Session extends Phrack_Middleware
{
    protected $state;
    protected $store;

    public function __construct($app, array $args = array())
    {
        $args = array_merge(array('state' => 'Native', 'store' => 'Native'), $args);
        parent::__construct($app, $args);
    }

    protected function prepareApp()
    {
        $this->state = $this->inflateBackend('Phrack_Session_State', $this->state);
        $this->store = $this->inflateBackend('Phrack_Session_Store', $this->store);
    }

    protected function inflateBackend($prefix, $backend)
    {
        if (is_object($backend)) {
            return $backend;
        }

        $class = $prefix;
        if (!empty($backend)) {
            $class .= "_$backend";
        }
        $class = Phrack_Util::loadClass($class);
        return new $class();
    }

    public function call(&$environ)
    {
        list($id, $session) = $this->getSession($environ);
        if ($id && $session) {
            $environ['phsgix.session'] =& $session;
        }
        else {
            $id = $this->generateId($environ);
            $environ['phsgix.session'] = array();
        }

        $environ['phsgix.session.options'] = array('id' => $id);

        $res = $this->callApp($environ);
        return $this->responseCb($res, array($this, 'process'), array(&$environ));
    }

    public function process(&$res, &$args)
    {
        $environ =& $args[0];
        $this->finalize($environ, $res);
    }

    protected function getSession(&$environ)
    {
        $id = $this->state->extract($environ);
        if (!$id) {
            return array(false, false);
        }

        $session =& $this->store->fetch($id);
        return array($id, &$session);
    }

    protected function generateId(&$environ)
    {
        return $this->state->generate($environ);
    }

    protected function commit(&$environ)
    {
        $options =& $environ['phsgix.session.options'];
        if (isset($options['expire']) && $options['expire']) {
            $this->store->remove($options['id']);
        }
        else {
            $this->store->store($options['id'], $environ['phsgix.session']);
        }
        $this->store->commit();
    }

    protected function finalize(&$environ, &$res)
    {
        $session = $environ['phsgix.session'];
        $options = $environ['phsgix.session.options'];

        if ( !(isset($options['no_store']) && $options['no_store']) ) {
            $this->commit($environ);
        }
        if (isset($options['expire']) && $options['expire']) {
            $this->expireSession($options['id'], $res, $environ);
        }
        else {
            $this->saveSession($options['id'], $res, $environ);
        }
    }

    protected function expireSession($id, &$res, &$environ)
    {
        $this->state->expireSessionId($id, $res, $environ['phsgix.session.options']);
    }

    protected function saveSession($id, &$res, &$environ)
    {
        $this->state->finalize($id, $res, $environ['phsgix.session.options']);
    }

    static public function wrap()
    {
        $args =& func_get_args();
        return parent::wrap(__CLASS__, $args);
    }
}
