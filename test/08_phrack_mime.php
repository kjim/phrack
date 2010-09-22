<?php
require_once(dirname(__FILE__).'/lib/bootstrap.php');
require_once('Phrack/MIME.php');

$t = new LimeTester();

function testMimetype($t)
{
    $func = 'Phrack_MIME::mimetype';
    $t->is(call($func, array('file.txt')), 'text/plain');
    $t->is(call($func, array('file.html')), 'text/html');
    $t->is(call($func, array('file.json')), 'application/json');
    $t->is(call($func, array('file.pdf')), 'application/pdf');
}
$t->append('testMimetype');

$t->execute();
