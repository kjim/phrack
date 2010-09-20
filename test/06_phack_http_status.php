<?php
require_once(dirname(__FILE__).'/lib/bootstrap.php');
require_once('Phack/HTTP/Status.php');

$t = new LimeTester();

function testStatusMessage($t)
{
    $func = 'Phack_HTTP_Status::statusMessage';
    $t->is(call($func, array(200)), 'OK');
    $t->is(call($func, array(500)), 'Internal Server Error');
}
$t->append('testStatusMessage');

function testIsXXX($t)
{
    $class = 'Phack_HTTP_Status';
    $t->is(call_static($class, 'isInfo',        array(100)), true,  'isInfo(100)');
    $t->is(call_static($class, 'isInfo',        array(200)), false, 'isInfo(200) false');
    $t->is(call_static($class, 'isSuccess',     array(200)), true,  'isSuccess(200)');
    $t->is(call_static($class, 'isSuccess',     array(300)), false, 'isSuccess(300) false');
    $t->is(call_static($class, 'isRedirect',    array(300)), true,  'isRedirect(300)');
    $t->is(call_static($class, 'isRedirect',    array(400)), false, 'isRedirect(400) false');
    $t->is(call_static($class, 'isError',       array(400)), true,  'isError(400)');
    $t->is(call_static($class, 'isError',       array(500)), true,  'isError(500)');
    $t->is(call_static($class, 'isError',       array(600)), false, 'isError(600) false');
    $t->is(call_static($class, 'isClientError', array(400)), true,  'isClientError(400)');
    $t->is(call_static($class, 'isClientError', array(500)), false, 'isClientError(500) false');
    $t->is(call_static($class, 'isServerError', array(500)), true,  'isServerError(500)');
    $t->is(call_static($class, 'isServerError', array(600)), false, 'isServerError(600) false');
}
$t->append('testIsXXX');

$t->execute();
