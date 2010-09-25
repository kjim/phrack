<?php
require_once(dirname(__FILE__).'/bootstrap.php');
require_once('Phrack/Handler/Apache2.php');
require_once('Phrack/Middleware/Session.php');

function countup(&$environ)
{
    $session =& $environ['phsgix.session'];
    if (!isset($session['counter'])) {
        $session['counter'] = -1;
    }
    $session['counter'] += 1;

    return array('200 OK', array(array('Content-Type', 'text/plain')),
                 array((string) $session['counter']));
}

$app = 'countup';
$app = Phrack_Middleware_Session::wrap($app);

$handler = new Phrack_Handler_Apache2();
$handler->run($app);
