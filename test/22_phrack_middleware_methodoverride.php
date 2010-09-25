<?php
require_once(dirname(__FILE__).'/lib/bootstrap.php');
require_once('Phrack/Middleware/MethodOverride.php');
require_once('Phrack/Util.php');

$t = new LimeTester();

function methodoverride(&$environ)
{
    return array('200 OK', array(), array($environ['REQUEST_METHOD']));
}

function testMethodOverride($t)
{
    $app = 'methodoverride';
    $app = Phrack_Middleware_MethodOverride::wrap($app);

    $environ = array('REQUEST_METHOD' => 'GET');
    list($_, $_, $body) = Phrack_Util::callApp($app, $environ);
    $t->is_deeply($body, array('GET'));

    $environ = array('REQUEST_METHOD' => 'GET', 'HTTP_X_HTTP_METHOD_OVERRIDE' => 'PUT');
    list($_, $_, $body) = Phrack_Util::callApp($app, $environ);
    $t->is_deeply($body, array('PUT'));
}
$t->append('testMethodOverride');

$t->execute();
