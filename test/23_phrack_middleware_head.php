<?php
require_once(dirname(__FILE__).'/lib/bootstrap.php');
require_once('Phrack/Middleware/Head.php');
require_once('Phrack/Util.php');

$t = new LimeTester();

function hello(&$environ)
{
    return array('200 OK', array(), array('Hello'));
}

function testHeadMiddleware($t)
{
    $app = 'hello';
    $app = Phrack_Middleware_Head::wrap($app);

    $environ = array('REQUEST_METHOD' => 'GET');
    list($_, $_, $body) = Phrack_Util::callApp($app, $environ);
    $t->is_deeply($body, array('Hello'));

    $environ = array('REQUEST_METHOD' => 'HEAD');
    list($_, $_, $body) = Phrack_Util::callApp($app, $environ);
    $t->is_deeply($body, array(), 'no body');
}
$t->append('testHeadMiddleware');

$t->execute();
