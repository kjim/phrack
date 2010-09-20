<?php
require_once(dirname(__FILE__).'/lib/bootstrap.php');
require_once('Phack/Util.php');

$t = new LimeTester();

function testCallApp($t)
{
    function hello(&$env)
    {
        $env['HTTP_X_TEST_CALL_APP'] = 'pass';
        return array('200 OK', array(array('Content-Type', 'text/plain')), array('Hello World'));
    }

    $app = 'hello';

    $env = array();
    list($status, $headers, $body) = Phack_Util::callApp($app, $env);
    $t->is($status, '200 OK', 'status is 200 OK');
    $t->is_deeply($headers, array(array('Content-Type', 'text/plain')), "[('Content-Type', 'text/plain')]");
    $t->is_deeply($body, array('Hello World'), "body is ('Hello World')");
    $t->is($env['HTTP_X_TEST_CALL_APP'], 'pass', 'environ is passed as reference');


    class HelloApp
    {
        public function call(&$env)
        {
            return array('200 OK', array(), array('Hello World'));
        }
    }

    $app = array(new HelloApp(), 'call');
    list($status, $headers, $body) = Phack_Util::callApp($app, $env);
    $t->is($status, '200 OK', 'status is 200 OK');
}
$t->append('testCallApp');

function testContentLength($t)
{
    $cl = Phack_Util::contentLength(array('abcdefg', 'h', 'ij'));
    $t->is($cl, 10, 'contentLenght()');
}
$t->append('testContentLength');


$headersSample = array(
    array('Content-Type', 'text/plain'),
    array('Content-Length', 11),
    );

function testHeaderIter($t)
{
    global $headersSample;
    $k = '423104f2f955484d83ca555705aa087c';
    $GLOBALS[$k] = array();
    function callback($key, $value)
    {
        $GLOBALS['423104f2f955484d83ca555705aa087c'][] = "$key = $value";
    }
    Phack_Util::headerIter($headersSample, 'callback');

    $t->is_deeply($GLOBALS[$k], array('Content-Type = text/plain', 'Content-Length = 11'),
                  'headerIter()');
    unset($GLOBALS[$k]);
}
$t->append('testHeaderIter');

function testHeaderGet($t)
{
    global $headersSample;
    $cl = Phack_Util::headerGet($headersSample, 'Content-Length');
    $t->is($cl, array(11), 'headerGet()');

    $cl = Phack_Util::headerGet($headersSample, 'CONTENT-LENGTH');
    $t->is($cl, array(11), 'headerGet() is ignore case');
}
$t->append('testHeaderGet');

function testHeaderSet($t)
{
    $headers = array(array('Foo', 'bar'));
    Phack_Util::headerSet($headers, 'Bar', 'baz');
    $t->is_deeply($headers, array(array('Foo', 'bar'), array('Bar', 'baz')));

    $headers = array(array('Foo', 'bar'));
    Phack_Util::headerSet($headers, 'Foo', 'baz');
    $t->is_deeply($headers, array(array('Foo', 'baz')));

    $headers = array(array('Foo', 'bar'));
    Phack_Util::headerSet($headers, 'foo', 'baz');
    $t->is_deeply($headers, array(array('Foo', 'baz')), 'headerSet case-insensitive');
}
$t->append('testHeaderSet');

function testHeaderPush($t)
{
    global $headersSample;

    $headers = $headersSample;
    Phack_Util::headerPush($headers, 'Location', 'http://example.com/foo/bar');
    $locations = Phack_Util::headerGet($headers, 'Location');
    $t->is_deeply($locations, array('http://example.com/foo/bar'), 'headerPush()');
}
$t->append('testHeaderPush');

function testHeaderExists($t)
{
    global $headersSample;

    $exists = Phack_Util::headerExists($headersSample, 'Content-Type');
    $t->ok($exists, 'headerExists() returns true value');

    $exists = Phack_Util::headerExists($headersSample, 'Location');
    $t->ok(!$exists, 'headerExists() returns false value');
}
$t->append('testHeaderExists');

function testHeaderRemove($t)
{
    global $headersSample;

    $headers = $headersSample;
    Phack_Util::headerRemove($headers, 'Content-Length');
    $t->is_deeply($headers, array(array('Content-Type', 'text/plain')), 'headerRemove()');
}
$t->append('testHeaderRemove');

$t->execute();
