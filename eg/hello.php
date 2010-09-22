<?php
require_once(dirname(__FILE__).'/bootstrap.php');
require_once('Phrack/Handler/Apache2.php');

function hello(&$env)
{
    return array('200 OK',
                 array(
                     array('Content-Type', 'text/plain'),
                     array('Content-Length', 11)),
                 array('Hello World'));
}

$handler = new Phrack_Handler_Apache2();
$handler->run('hello');
