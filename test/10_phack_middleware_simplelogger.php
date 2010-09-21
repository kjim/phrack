<?php
require_once(dirname(__FILE__).'/lib/bootstrap.php');
require_once('Phack/Middleware/SimpleLogger.php');

$t = new LimeTester();

function testMiddleware($t)
{
    $errors = fopen('php://memory', 'r+');
    $environ = array('phsgi.errors' => $errors);

    function app_using_logger(&$environ)
    {
        $environ['phsgix.logger']->debug('debug_1');
        $environ['phsgix.logger']->info('info_2');
        $environ['phsgix.logger']->notice('notice_3');
        $environ['phsgix.logger']->warn('warn_4');
        $environ['phsgix.logger']->err('err_5');
        $environ['phsgix.logger']->crit('crit_6');
        $environ['phsgix.logger']->alert('alert_7');
        $environ['phsgix.logger']->emerg('emerg_8');
        return array('200 OK', array(), array());
    }

    $app = 'app_using_logger';
    $app = Phack_Middleware_SimpleLogger::wrap($app, array('level' => 'warn'));

    Phack_Util::callApp($app, $environ);

    rewind($errors);
    $buf = fread($errors, 1024);
    fclose($errors);

    $lines = explode("\n", $buf);
    $t->is(count($lines), 6);
    $t->like($lines[0], '/^.+ warn_4$/');
    $t->like($lines[1], '/^.+ err_5$/');
    $t->like($lines[2], '/^.+ crit_6$/');
    $t->like($lines[3], '/^.+ alert_7$/');
    $t->like($lines[4], '/^.+ emerg_8$/');
    $t->is($lines[5], '');
}
$t->append('testMiddleware');

$t->execute();
