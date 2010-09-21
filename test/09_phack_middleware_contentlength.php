<?php
require_once(dirname(__FILE__).'/lib/bootstrap.php');
require_once('Phack/Middleware/ContentLength.php');

$t = new LimeTester();

function testMiddleware($t)
{
    $environ = array();

    function hello(&$env)
    {
        return array('200 OK', array(), array('Hello World'));
    }

    function hello_from_file(&$env)
    {
        $mem = fopen('php://memory', 'r+');
        fwrite($mem, 'Hello World');
        rewind($mem);
        return array('200 OK', array(), $mem);
    }

    $app = 'hello';
    $app = Phack_Middleware_ContentLength::wrap($app);

    list($_, $headers, $body) = Phack_Util::callApp($app, $environ);
    $t->is_deeply($headers, array(array('Content-Length', 11)));
    $t->is_deeply($body, array('Hello World'));

    $app = 'hello_from_file';
    $app = Phack_Middleware_ContentLength::wrap($app);

    list($_, $headers, $body) = Phack_Util::callApp($app, $environ);
    $t->is_deeply($headers, array(array('Content-Length', 11)));
    $t->ok(is_resource($body));
}
$t->append('testMiddleware');

$t->execute();
