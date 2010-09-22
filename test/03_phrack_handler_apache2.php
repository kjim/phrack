<?php
require_once(dirname(__FILE__).'/lib/bootstrap.php');
require_once('Phrack/Handler/Apache2.php');
require_once('Phrack/ResponseWriter/String.php');

$t = new LimeTester();

function newWriter($class='Phrack_ResponseWriter_String')
{
    return new $class();
}

function testRunSimpleApp($lime)
{
    function simpleApp(&$env)
    {
        return array('200 OK', array(array('Content-Type', 'text/plain')), array('Hello World!'));
    }

    $app = 'simpleApp';

    $writer = newWriter();
    $handler = new Phrack_Handler_Apache2($writer);
    $handler->run($app);

    $lime->is($writer->header(), "HTTP/1.0 200 OK\nContent-Type: text/plain");
    $lime->is($writer->body(), 'Hello World!', 'sent body to client');
}
$t->append('testRunSimpleApp');

function testResponseBodyIsResource($t)
{
    function app(&$env)
    {
        $fpread = fopen(dirname(__FILE__).'/static/404.html', 'rb');
        return array('200 OK', array(), $fpread);
    }

    $app = 'app';

    $writer = newWriter();
    $handler = new Phrack_Handler_Apache2($writer);
    $handler->run($app);

    $t->is($writer->body(), file_get_contents(dirname(__FILE__).'/static/404.html'));
}
$t->append('testResponseBodyIsResource');

$t->execute();
