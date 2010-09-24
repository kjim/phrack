<?php
require_once(dirname(__FILE__).'/lib/bootstrap.php');
require_once('Phrack/Middleware/HTTPExceptions.php');
require_once('Phrack/Util.php');
require_once('Phrack/HTTP/Exception.php');

$t = new LimeTester();

function setupEnviron($override = array())
{
    $environ = array(
        'phsgi.errors' => fopen('php://memory', 'r+'),
        );

    return array_merge($environ, $override);
}

function teardownEnviron(&$environ)
{
    fclose($environ['phsgi.errors']);
}

function test3xxError($t)
{
    function throw3xxError(&$environ)
    {
        throw Phrack_HTTP_Exception::create('302', 'http://www.google.com/');
    }

    $app = 'throw3xxError';
    $app = Phrack_Middleware_HTTPExceptions::wrap($app);

    $environ = setupEnviron();
    list($status, $headers, $body) = Phrack_Util::callApp($app, $environ);
    rewind($environ['phsgi.errors']);

    $t->ok($status === '302 Found');
    $t->is(fread($environ['phsgi.errors'], 1024), '');
    $t->is(count($headers), 3);
    $t->is($headers[0], array('Content-Type', 'text/plain'));
    $t->is($headers[2], array('Location', 'http://www.google.com/'));
    $t->is($body, array());

    teardownEnviron($environ);
}
$t->append('test3xxError');

function testNotHTTPError($t)
{
    function throwError(&$environ)
    {
        throw new Exception('Error');
    }

    $app = 'throwError';
    $app = Phrack_Middleware_HTTPExceptions::wrap($app);

    $environ = setupEnviron();
    list($status, $headers, $body) = Phrack_Util::callApp($app, $environ);
    rewind($environ['phsgi.errors']);

    $t->ok($status === '500 Internal Server Error');
    $t->ok(strpos(fread($environ['phsgi.errors'], 4096), 'Stack trace') !== false);
    $t->is(count($headers), 2);

    teardownEnviron($environ);
}
$t->append('testNotHTTPError');

function testNormalResponse($t)
{
    function noexcept(&$environ)
    {
        return array('200 OK', array(), array());
    }

    $app = 'noexcept';
    $app = Phrack_Middleware_HTTPExceptions::wrap($app);

    $environ = array();
    list($status, $_, $_) = Phrack_Util::callApp($app, $environ);
    $t->is($status, '200 OK');
}
$t->append('testNormalResponse');

$t->execute();
