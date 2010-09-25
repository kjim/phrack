<?php
require_once(dirname(__FILE__).'/lib/bootstrap.php');
require_once('Phrack/Middleware/NullLogger.php');

$t = new LimeTester();

function testNullLoggerAPI($t)
{
    function devnull(&$environ)
    {
        $logger = $environ['phsgix.logger'];
        $logger->debug('');
        $logger->info('');
        $logger->notice('');
        $logger->warn('');
        $logger->err('');
        $logger->crit('');
        $logger->alert('');
        $logger->emerg('');
        return array('200 OK', array(), array());
    }

    $app = 'devnull';
    $app = Phrack_Middleware_NullLogger::wrap($app);

    $environ = array();
    list($status, $_, $_) = Phrack_Util::callApp($app, $environ);
    $t->is($status, '200 OK');
}
$t->append('testNullLoggerAPI');

$t->execute();
