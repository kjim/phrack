<?php
require_once(dirname(__FILE__).'/lib/bootstrap.php');
require_once('Phrack/Middleware/Auth/Basic.php');
require_once('Phrack/Util.php');

$t = new LimeTester();

function successfully(&$environ)
{
    return array('200 OK', array(), array('successfully'));
}

function authenticate($user, $pass)
{
    return $user === 'admin' && $pass === 'no^bOv';
}

function testAutorizationRequiredResponse($t)
{
    $app = 'successfully';
    $app = Phrack_Middleware_Auth_Basic::wrap($app, array('authenticator' => 'authenticate'));

    $environ = array();
    list($status, $headers, $body) = Phrack_Util::callApp($app, $environ);
    $t->ok($status === Phrack_Util::statusText(401));
    $t->is_deeply($headers,
                  array(array('Content-Type', 'text/plain'),
                        array('Content-Length', mb_strlen($body[0])),
                        array('WWW-Authenticate', 'Basic realm="restricted area"'))
        );
    $t->is_deeply($body, array('Authorization required'));
}
$t->append('testAutorizationRequiredResponse');

function testFailedToLogin($t)
{
    $app = 'successfully';
    $app = Phrack_Middleware_Auth_Basic::wrap($app, array('authenticator' => 'authenticate'));

    $environ = array('PHP_AUTH_USER' => 'foo', 'PHP_AUTH_PW' => 'bar');
    list($status, $_, $_) = Phrack_Util::callApp($app, $environ);
    $t->ok($status === Phrack_Util::statusText(401));
}
$t->append('testFailedToLogin');

function testSuccessToLogin($t)
{
    $app = 'successfully';
    $app = Phrack_Middleware_Auth_Basic::wrap($app, array('authenticator' => 'authenticate'));

    $environ = array('PHP_AUTH_USER' => 'admin', 'PHP_AUTH_PW' => 'no^bOv');
    list($status, $headers, $body) = Phrack_Util::callApp($app, $environ);
    $t->ok($status === Phrack_Util::statusText(200));
    $t->is($body, array('successfully'));
}
$t->append('testSuccessToLogin');

$t->execute();
