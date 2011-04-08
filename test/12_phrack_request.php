<?php
require_once(dirname(__FILE__).'/lib/bootstrap.php');
require_once('Phrack/Request.php');

$t = new LimeTester();

function testNewRequest($t)
{
    $_GET    = array();
    $_POST   = array();
    $_FILES  = array();
    $_COOKIE = array();

    $environ = array(
        'REQUEST_METHOD'    => 'GET',
        'SERVER_PROTOCOL'   => 'HTTP/1.1',
        'SERVER_PORT'       => 80,
        'SERVER_NAME'       => 'example.com',
        'SCRIPT_NAME'       => '/foo',
        'REMOTE_ADDR'       => '127.0.0.1',
        'phsgi.version'     => array(1, 0),
        'phsgi.input'       => null,
        'phsgi.errors'      => null,
        'phsgi.url_scheme'  => 'http',
        );
    $req = new Phrack_Request($environ);

    $t->is($req->getAddress(), '127.0.0.1', 'getAddress()');
    $t->is($req->getMethod(), 'GET', 'getMethod()');
    $t->is($req->getProtocol(), 'HTTP/1.1', 'getProtocol()');
    $t->is($req->getUri(), 'http://example.com/foo', 'getRequestUri()');
    $t->is($req->getPort(), 80, 'getPort()');
    $t->is($req->getScheme(), 'http', 'getScheme()');
}
$t->append('testNewRequest');

function testConvertFileInformation($t)
{
    $files = array(
        'foo' => array(
            'name' => 'foo.txt',
            'type' => 'text/plain',
            'tmp_name' => '/tmp/php/php1h4j1o',
            'error' => UPLOAD_ERR_OK,
            'size' => 123
            ),
        'bar' => array(
            'name' => 'bar.txt',
            'type' => 'text/plain',
            'tmp_name' => '/tmp/php/php6hst32',
            'error' => UPLOAD_ERR_OK,
            'size' => 98174
            ),
        );

    $environ = array();
    $req = new Phrack_Request($environ);
    $uploads = $req->convertFileInformation($files);

    $foo = $uploads['foo'];
    $t->ok($foo instanceof Phrack_Request_Upload);
    $t->is($foo->getFileName(), 'foo.txt');
    $t->is($foo->getSize(), 123);
    $t->is($foo->getTempName(), '/tmp/php/php1h4j1o');
    $t->is($foo->getPath(), $foo->getTempName());

    $bar = $uploads['bar'];
    $t->ok($bar instanceof Phrack_Request_Upload);
}
$t->append('testConvertFileInformation');

function testConvertFileInformationWithHashArraySyntax($t)
{
    $files = array(
        'upload_files' => array(
            'name' => array('foo.txt', 'bar.jpg'),
            'type' => array('text/plain', 'image/mpg'),
            'tmp_name' => array('/tmp/php/php1h4j1o', '/tmp/php/php6hst32'),
            'error' => array(UPLOAD_ERR_OK, UPLOAD_ERR_OK),
            'size' => array(123, 98174)
            )
        );

    $environ = array();
    $req = new Phrack_Request($environ);
    $uploads = $req->convertFileInformation($files);

    $uploadFiles = $uploads['upload_files'];
    $t->is(count($uploadFiles), 2);

    $foo = $uploadFiles[0];
    $t->is($foo->getFileName(), 'foo.txt');
    $t->is($foo->getFileName(), 'foo.txt');
    $t->is($foo->getSize(), 123);
    $t->is($foo->getTempName(), '/tmp/php/php1h4j1o');
    $t->is($foo->getPath(), $foo->getTempName());

    $bar = $uploadFiles[1];
    $t->is($bar->getFileName(), 'bar.jpg');
    $t->is($bar->getTempName(), '/tmp/php/php6hst32');
    $t->is($bar->getPath(), $bar->getTempName());
}
$t->append('testConvertFileInformationWithHashArraySyntax');

function testGetUploads($t)
{
    $_FILES = array(
        'foo' => array(
            'name' => 'foo.txt',
            'type' => 'text/plain',
            'tmp_name' => '/tmp/php/php1h4j1o',
            'error' => UPLOAD_ERR_OK,
            'size' => 123
            ),
        'bar' => array(
            'name' => 'bar.txt',
            'type' => 'text/plain',
            'tmp_name' => '/tmp/php/php6hst32',
            'error' => UPLOAD_ERR_OK,
            'size' => 98174
            ),
        );

    $environ = array();
    $req = new Phrack_Request($environ);
    $uploads = $req->getUploads();

    $t->is($uploads->get('foo')->getFileName(), 'foo.txt');
    $t->is(count($uploads->getAll('foo')), 1);
    $t->is($uploads->get('bar')->getFileName(), 'bar.txt');
    $t->is(count($uploads->getAll('bar')), 1);
    
    $_FILES = array(
        'upload_files' => array(
            'name' => array('foo.txt', 'bar.jpg'),
            'type' => array('text/plain', 'image/mpg'),
            'tmp_name' => array('/tmp/php/php1h4j1o', '/tmp/php/php6hst32'),
            'error' => array(UPLOAD_ERR_OK, UPLOAD_ERR_OK),
            'size' => array(123, 98174)
            )
        );

    $environ = array();
    $req = new Phrack_Request($environ);
    $uploads = $req->getUploads();

    $bar = $uploads->get('upload_files');
    $t->is($bar->getFileName(), 'bar.jpg');

    $files = $uploads->getAll('upload_files');
    $t->is(count($files), 2);
    $t->is($files[0]->getFileName(), 'foo.txt');
    $t->is($files[1]->getFileName(), 'bar.jpg');
}
$t->append('testGetUploads');

$t->execute();
