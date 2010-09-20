<?php
require_once(dirname(__FILE__).'/bootstrap.php');
require_once('Phack/Handler/Apache2.php');

function environ(&$env)
{
    $body = array();
    foreach ($env as $k => $v) {
        $body[] = $k . ' => ' . (is_array($v) ? '('. implode(', ', $v) . ')' : $v) . "\n";
    }
    return array('200 OK', array(array('Content-Type', 'text/plain')), $body);
}

$app = 'environ';

$handler = new Phack_Handler_Apache2();
$handler->run($app);
