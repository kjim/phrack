<?php
require_once(dirname(__FILE__).'/lib/bootstrap.php');

$t = new LimeTester();

function testRun($t)
{
    $t->ok(true, 'test running');
}
$t->append('testRun');

$t->execute();
