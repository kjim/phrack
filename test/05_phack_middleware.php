<?php
require_once(dirname(__FILE__).'/lib/bootstrap.php');
require_once('Phack/Middleware.php');
require_once('Phack/Util.php');

$t = new LimeTester();

function testMiddlewareImplementation($t)
{
    function app(&$environ)
    {
        $body = array($environ['phsgix.foo']);
        return array('200 OK', array(), $body);
    }

    class FooMiddleware extends Phack_Middleware
    {
        public function call(&$environ)
        {
            $environ['phsgix.foo'] = 'foo';
            $res = Phack_Util::callApp($this->app, $environ);
            return $res;
        }

        /**
         * @see Phack_Middleware::wrap
         */
        static public function wrap()
        {
            $args =& func_get_args();
            return parent::wrap(__CLASS__, $args);
        }
    }

    $app = FooMiddleware::wrap('app');

    $environ = array('FOO' => 'bar', 'BAR' => 'baz');
    list($status, $headers, $body) = Phack_Util::callApp($app, $environ);
    $t->is_deeply($body, array('foo'));
}
$t->append('testMiddlewareImplementation');

$t->execute();
