<?php
require_once(dirname(__FILE__).'/bootstrap.php');
require_once('Phrack/Handler/Apache2.php');
require_once('Phrack/Util.php');

function error(&$env)
{
    throw new Exception("some error on error app");
}

class ErrorMiddleware
{
    protected $app;

    protected function __construct($app)
    {
        $this->app = $app;
    }

    static public function wrap($app)
    {
        $wrap = new self($app);
        return array($wrap, '_call');
    }

    public function _call(&$env)
    {
        try {
            Phrack_Util::callApp($this->app, $env);
        }
        catch (Exception $e) {
            fwrite($env['phsgi.errors'], $e->getMessage() . "\n");
            return array('500 Internal Server Error',
                         array(array('Content-Type', 'text/plain')),
                         array('500 Internal Server Error: ' . $e->getMessage()));
        }
    }
}

$app = 'error';
$app = ErrorMiddleware::wrap($app);

$handler = new Phrack_Handler_Apache2();
$handler->run($app);
