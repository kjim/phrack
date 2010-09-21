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
    $func = array('Phack_Util', 'contentLength');
    $t->is(call($func, array(array('abcdefg', 'h', 'ij'))), 10, 'contentLenght([])');

    $file = dirname(__FILE__).'/static/404.html';
    $fh = fopen($file, 'rb');
    $t->is(call($func, array($fh)), filesize($file), 'contentLenght(file_handle)');
    fclose($fh);
}
$t->append('testContentLength');


function testHeaderIter($t)
{
    $headers = array(array('Foo', 'bar'), array('Bar', 'baz'));

    $k = '423104f2f955484d83ca555705aa087c';
    $GLOBALS[$k] = array();
    function callback($key, $value)
    {
        $GLOBALS['423104f2f955484d83ca555705aa087c'][] = "$key = $value";
    }
    Phack_Util::headerIter($headers, 'callback');

    $t->is_deeply($GLOBALS[$k], array('Foo = bar', 'Bar = baz'),
                  'headerIter()');
    unset($GLOBALS[$k]);
}
$t->append('testHeaderIter');

function testHeaderGet($t)
{
    $headers = array(array('Foo', 'bar'));
    $cl = Phack_Util::headerGet($headers, 'Foo');
    $t->is($cl, array('bar'), 'headerGet()');

    $cl = Phack_Util::headerGet($headers, 'foo');
    $t->is($cl, array('bar'), 'headerGet() is case-insensitive');
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
    $headers = array(array('Foo', 'bar'));
    Phack_Util::headerPush($headers, 'Bar', 'baz');
    Phack_Util::headerPush($headers, 'Bar', 'qux');
    $locations = Phack_Util::headerGet($headers, 'Bar');
    $t->is_deeply($locations, array('baz', 'qux'), 'headerPush()');
}
$t->append('testHeaderPush');

function testHeaderExists($t)
{
    $headers = array(array('Foo', 'bar'));
    $exists = Phack_Util::headerExists($headers, 'Foo');
    $t->ok($exists, 'headerExists() returns true value');

    $exists = Phack_Util::headerExists($headers, 'Bar');
    $t->ok(!$exists, 'headerExists() returns false value');
}
$t->append('testHeaderExists');

function testHeaderRemove($t)
{
    $headers = array(array('Foo', 'bar'));
    Phack_Util::headerRemove($headers, 'Foo');
    $t->is_deeply($headers, array(), 'headerRemove()');
}
$t->append('testHeaderRemove');

$t->execute();
