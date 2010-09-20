<?php
require_once(dirname(__FILE__).'/lib/bootstrap.php');
require_once('Phack/Middleware/ErrorDocument.php');

$t = new LimeTester();

$file404 = (dirname(__FILE__).'/static/404.html');
$file500 = (dirname(__FILE__).'/static/500.html');

// phsgi app
function notfound(&$environ)
{
    global $file404;
    if (isset($environ['PATH_INFO']) && $environ['PATH_INFO'] === '/error/404.html') {
        return array('200 OK', array(), file($file404));
    }
    return array('404 Not Found', array(), array());
}

// phsgi app
function internalerror(&$environ)
{
    global $file500;
    if (isset($environ['PATH_INFO']) && $environ['PATH_INFO'] === '/error/500.html') {
        return array('200 OK', array(), file($file500));
    }
    return array('500 Internal Server Error', array(), array());
}

function testNoSubrequest($t)
{
    global $file404;
    global $file500;

    $app = 'notfound';
    $app = Phack_Middleware_ErrorDocument::wrap($app, array(404 => $file404));

    $environ = array();
    list($status, $_, $body) = Phack_Util::callApp($app, $environ);
    $t->is($status, '404 Not Found');
    $t->ok(is_resource($body), 'body is resource object');
    $t->is(fread($body, 8192), file_get_contents($file404), '404 body');
    fclose($body);

    $app = 'internalerror';
    $app = Phack_Middleware_ErrorDocument::wrap($app, array(500 => $file500));

    list($status, $_, $body) = Phack_Util::callApp($app, $environ);
    $t->is($status, '500 Internal Server Error');
    $t->ok(is_resource($body), 'body is resource object');
    $t->is(fread($body, 8192), file_get_contents($file500), '500 body');
    fclose($body);
}
$t->append('testNoSubrequest');

function testUseSubrequest($t)
{
    global $file404;
    global $file500;

    $path404 = '/error/404.html';
    $path500 = '/error/500.html';

    $environ = array();

    $app = 'notfound';
    $app = Phack_Middleware_ErrorDocument::wrap($app, array(404 => $path404, 'subrequest' => true));

    list($status, $_, $body) = Phack_Util::callApp($app, $environ);
    $t->is($status, '404 Not Found');
    $t->is_deeply($body, file($file404));

    $app = 'internalerror';
    $app = Phack_Middleware_ErrorDocument::wrap($app, array(500 => $path500, 'subrequest' => true));

    list($status, $_, $body) = Phack_Util::callApp($app, $environ);
    $t->is($status, '500 Internal Server Error');
    $t->is_deeply($body, file($file500));
}
$t->append('testUseSubrequest');

$t->execute();
