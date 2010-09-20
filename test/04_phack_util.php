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

$t->execute();
