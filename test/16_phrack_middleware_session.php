<?php
require_once(dirname(__FILE__).'/lib/bootstrap.php');
require_once('Phrack/Middleware/Session.php');
require_once('Phrack/Session/State/Native.php');
require_once('Phrack/Session/Store/Native.php');
require_once('Phrack/Session.php');
require_once('Phrack/Util.php');

$t = new LimeTester();

function setGreeting(&$environ)
{
    $session =& $environ['phsgix.session'];
    $session['greeting_to'] = 'Foo';
    return array('200 OK', array(), array());
}

function greeting(&$environ)
{
    $session =& $environ['phsgix.session'];
    return array('200 OK', array(), array('Hello ', $session['greeting_to']));
}

function appExpireSession(&$environ)
{
    $environ['phsgix.session.options']['expire'] = true;
    return array('200 OK', array(), array());
}

function appGetSessionKeys(&$environ)
{
    $session =& $environ['phsgix.session'];
    return array('200 OK', array(), array_keys($session));
}

function testNativeSessionOnPHP($t)
{
    $environ = array();
    $app = 'setGreeting';
    $app = Phrack_Middleware_Session::wrap($app);
    $_ = Phrack_Util::callApp($app, $environ);

    $environ = array();
    $app = 'greeting';
    $app = Phrack_Middleware_Session::wrap($app);
    list($_, $_, $body) = Phrack_Util::callApp($app, $environ);
    $t->is_deeply($body, array('Hello ', 'Foo'));

    $environ = array();
    $app = 'appExpireSession';
    $app = Phrack_Middleware_Session::wrap($app);
    Phrack_Util::callApp($app, $environ);

    $environ = array();
    $app = 'appGetSessionKeys';
    $app = Phrack_Middleware_Session::wrap($app);
    list($_, $_, $body) = Phrack_Util::callApp($app, $environ);
    $t->is($body, array(), 'empty session');
}
$t->append('testNativeSessionOnPHP');

function testUsingSessionObject($t)
{
    class UsingSessionApps
    {
        public function set(&$environ)
        {
            $session = new Phrack_Session($environ);
            $session->set('foo', 'Foo');
            $session->set('bar', 'Bar');
            $session->set('baz', 'Baz');
            $keys = $session->keys();
            sort($keys);
            $session->set('session_keys', implode(':', $keys));
            return array('200 OK', array(), array());
        }

        public function get(&$environ)
        {
            $session = new Phrack_Session($environ);
            $body = array();
            foreach ($session->keys() as $key) {
                $body[] = $key . '=' . $session->get($key);
            }
            return array('200 OK', array(), $body);
        }
    }

    $environ = array();
    $app = array(new UsingSessionApps(), 'set');
    $app = Phrack_Middleware_Session::wrap($app);
    Phrack_Util::callApp($app, $environ);

    $app = array(new UsingSessionApps(), 'get');
    list($_, $_, $body) = Phrack_Util::callApp($app, $environ);
    $t->is_deeply($body, array(
                      'foo=Foo',
                      'bar=Bar',
                      'baz=Baz',
                      'session_keys=bar:baz:foo'
                      ));
}
$t->append('testUsingSessionObject');

ob_start();
$t->execute();
ob_flush();
