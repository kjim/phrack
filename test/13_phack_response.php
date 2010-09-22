<?php
require_once(dirname(__FILE__).'/lib/bootstrap.php');
require_once('Phack/Response.php');

$t = new LimeTester();

function testNew($t)
{
    $res = new Phack_Response(302);
    $t->is($res->getStatus(), 302);

    // array_array syntax
    $res = new Phack_Response(200, array(array('Content-Type', 'text/plain')));
    $t->is($res->getContentType(), 'text/plain');

    // hash syntax
    $res = new Phack_Response(200, array('Content-Type' => 'text/plain'));
    $t->is($res->getContentType(), 'text/plain');

    $res = new Phack_Response(200);
    $res->setContentType('image/png');
    $t->is($res->getContentType(), 'image/png');

    $res = new Phack_Response(200);
    $res->addHeader('X-Foo', 'bar');
    $t->is($res->getHeader('X-Foo'), 'bar');
}
$t->append('testNew');

function testRedirect($t)
{
    $res = new Phack_Response();
    $res->redirect('http://www.google.com/');
    $t->is($res->getLocation(), 'http://www.google.com/');
    $t->is($res->getStatus(), 302);

    $t->is_deeply($res->finalize(), array('302 Found',
                                          array(array('Location', 'http://www.google.com/')), array()));


    $res = new Phack_Response();
    $res->redirect('http://www.google.com/', 301);
    $t->is_deeply($res->finalize(), array('301 Moved Permanently',
                                          array(array('Location', 'http://www.google.com/')), array()));
}
$t->append('testRedirect');

function testBody($t)
{
    function r()
    {
        $args = func_get_args();
        $res = new Phack_Response(200);
        call_user_func_array(array($res, 'setBody'), $args);
        $fin = $res->finalize();
        return $fin[2];
    }

    $t->is_deeply(r('Hello World'), array('Hello World'));
    $t->is_deeply(r(array('Hello World')), array('Hello World'));

    $fh = fopen('php://memory', 'rb');
    $t->is_deeply(r($fh), $fh);
    fclose($fh);

    class StringifiedObject
    {
        private $s;
        public function __construct($s) { $this->s = $s; }
        public function __toString($s) { return $this->s; }
    }

    $object = new StringifiedObject('Hello World');
    $t->is_deeply(r($object), array($object));
}
$t->append('testBody');

function testCookie($t)
{
    $res = new Phack_Response(200);
    $res->setCookie('foo', array('value' => 'bar', 'domain' => '.example.com', 'path' => '/cgi-bin'));
    $res->setCookie('bar', array('value' => 'xxx yyy', 'expires' => time() + 3600));
    list($_, $headers, $_) = $res->finalize();

    $t->is(count($headers), 2);
    $t->is($headers[0][1], "foo=bar; domain=.example.com; path=/cgi-bin");
    $t->like($headers[1][1], '/bar=xxx%20yyy; expires=\w+, \d+-\w+-\d+ \d\d:\d\d:\d\d GMT/');
}
$t->append('testCookie');

function testResponse($t)
{
    function res()
    {
        $res = new Phack_Response();
        foreach (func_get_args() as $command) {
            list($k, $v) = $command;

            $meth = "set" . ucfirst($k);
            call_user_func_array(array($res, $meth), is_array($v) ? $v : array($v));
        }
        return $res->finalize();
    }

    $t->is_deeply(
        res(
            array('status', 200),
            array('body', 'hello')
            ),
        array('200 OK', array(), array('hello'))
        );

    $t->is_deeply(
        res(
            array('status', 200),
            array('cookie', array(
                      'foo_sid', array(
                          'value' => 'ASDFJKL:',
                          'expires' => 'Thu, 25-Apr-1999 00:40:33 GMT',
                          'domain'  => 'example.com',
                          'path' => '/',
                          ))),
            array('cookie', array(
                      'poo_sid', array(
                          'value' => 'QWERTYUI',
                          'expires' => 'Thu, 25-Apr-1999 00:40:33 GMT',
                          'domain'  => 'example.com',
                          'path' => '/',
                          ))),
            array('body', 'hello')
            ),
        array(
            '200 OK',
            array(
                array('Set-Cookie', 'foo_sid=ASDFJKL%3A; domain=example.com; path=/; expires=Thu, 25-Apr-1999 00:40:33 GMT'),
                array('Set-Cookie', 'poo_sid=QWERTYUI; domain=example.com; path=/; expires=Thu, 25-Apr-1999 00:40:33 GMT'),
                ),
            array('hello'),
            )
        );
}
$t->append('testResponse');

$t->execute();
