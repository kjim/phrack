<?php
require_once(dirname(__FILE__).'/lib/bootstrap.php');
require_once('Phrack/Middleware.php');
require_once('Phrack/Util.php');

$t = new LimeTester();

function testMiddlewareImplementation($t)
{
    function app(&$environ)
    {
        $body = array($environ['phsgix.foo']);
        return array('200 OK', array(), $body);
    }

    class FooMiddleware extends Phrack_Middleware
    {
        public function call(&$environ)
        {
            $environ['phsgix.foo'] = 'foo';
            $res = Phrack_Util::callApp($this->app, $environ);
            return $res;
        }

        /**
         * @see Phrack_Middleware::wrap
         */
        static public function wrap()
        {
            $args =& func_get_args();
            return parent::wrap(__CLASS__, $args);
        }
    }

    $app = FooMiddleware::wrap('app');

    $environ = array('FOO' => 'bar', 'BAR' => 'baz');
    list($status, $headers, $body) = Phrack_Util::callApp($app, $environ);
    $t->is_deeply($body, array('foo'));
}
$t->append('testMiddlewareImplementation');

$t->execute();
