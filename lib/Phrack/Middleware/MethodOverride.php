<?php
require_once('Phrack/Middleware.php');

class Phrack_Middleware_MethodOverride extends Phrack_Middleware
{
    static protected $ALLOWED_METHOD = array(
        'GET' => 1, 'HEAD' => 1, 'POST' => 1, 'PUT' => 1, 'DELETE' => 1);

    public function call(&$environ)
    {
        $key = isset($this->args['header']) ? $this->args['header'] : 'X-HTTP-Method-Override';
        $key = strtr($key, '-', '_');

        $key = 'HTTP_' . strtoupper($key);
        $method = isset($environ[$key]) ? $environ[$key] : false;
        if ($method && array_key_exists($method, self::$ALLOWED_METHOD)) {
            $environ['REQUEST_METHOD'] = $method;
        }

        return $this->callApp($environ);
    }

    static public function wrap()
    {
        $args =& func_get_args();
        return parent::wrap(__CLASS__, $args);
    }
}
