<?php
require_once(dirname(__FILE__).'/bootstrap.php');
require_once('Phrack/Handler/Apache2.php');
require_once('Phrack/Middleware/Auth/Basic.php');

function successfully(&$environ)
{
    return array('200 OK', array(), array('successfully'));
}

function authen($user, $pass)
{
    return $user === 'admin' && $pass === 'pass';
}

$app = 'successfully';
$app = Phrack_Middleware_Auth_Basic::wrap($app, array('authenticator' => 'authen'));

$handler = new Phrack_Handler_Apache2();
$handler->run($app);
