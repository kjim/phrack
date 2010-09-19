<?php
require_once(dirname(__FILE__).'/lib/bootstrap.php');
require_once('Phack/Handler/Apache2.php');

$t = new LimeTester();

function testRunSimpleApp($lime)
{
    function simpleApp(&$env)
    {
        return array('200 OK', array(array('Content-Type', 'text/plain')), array('Hello World!'));
    }

    $app = 'simpleApp';

    $handler = new Phack_Handler_Apache2();
    ob_start();
    $handler->run($app);
    $sentbody = ob_get_contents();
    ob_end_clean();

    $lime->is($sentbody, "Hello World!", 'sent body to client');
}
$t->append('testRunSimpleApp');

$t->execute();
