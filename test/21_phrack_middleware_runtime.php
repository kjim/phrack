<?php
require_once(dirname(__FILE__).'/lib/bootstrap.php');
require_once('Phrack/Middleware/Runtime.php');

$t = new LimeTester();

function slowapp(&$environ)
{
    usleep(3000); // 1 / 100 sec
    return array('200 OK', array(), array());
}

function testRuntime($t)
{
    $app = 'slowapp';
    $app = Phrack_Middleware_Runtime::wrap($app);

    $environ = array();
    list($_, $headers, $_) = Phrack_Util::callApp($app, $environ);

    list($key, $reqtime) = $headers[0];
    $t->is($key, 'X-Runtime');
    $t->like($reqtime, '/^\d+\.\d{6}$/');
    $t->ok($reqtime > 0.003);
}
$t->append('testRuntime');

$t->execute();
