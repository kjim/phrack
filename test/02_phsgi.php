<?php
require_once(dirname(__FILE__).'/lib/bootstrap.php');

$t = new LimeTester();

function makeMockEnv()
{
    $env = array(
        'REQUEST_METHOD'   => 'GET',
        'SCRIPT_NAME'      => '/index.php',
        'PATH_INFO'        => '/',
        'REQUEST_URI'      => '/',
        'QUERY_STRING'     => '',
        'SERVER_NAME'      => 'localhost',
        'SERVER_PORT'      => '10080',
        'HTTP_HOST'        => 'localhost:10080',
        'SERVER_PROTOCOL'  => 'HTTP/1.1',

        'HTTP_CONNECTION'      => 'keep-alive',
        'HTTP_CACHE_CONTROL'   => 'max-age=0',
        'HTTP_ACCEPT'          => 'application/xml,application/xhtml+xml,text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5',
        'HTTP_USER_AGENT'      => 'Mozilla/5.0 (X11; U; Linux i686; en-US) AppleWebKit/534.3 (KHTML, like Gecko) Chrome/6.0.472.55 Safari/534.3',
        'HTTP_ACCEPT_ENCODING' => 'gzip,deflate,sdch',
        'HTTP_ACCEPT_LANGUAGE' => 'ja,en-US;q=0.8,en;q=0.6',
        'HTTP_ACCEPT_CHARSET'  => 'Shift_JIS,utf-8;q=0.7,*;q=0.3',

        'phsgi.version'       => array(1, 0),
        'phsgi.url_scheme'    => 'http',
        'phsgi.input'         => null, // not available
        'phsgi.errors'        => null, // not available
        'phsgi.multithread'   => false,
        'phsgi.multiprocess'  => true,
        );
    return $env;
}

function testPhsgiApplicationExample($t)
{
    function app(&$env)
    {
        return array(200, array(array('Content-Type', 'text/plain')), array('Hello World!'));
    }

    $app = 'app';

    $env =& makeMockEnv();
    list($status, $headers, $body) = call_user_func_array($app, array(&$env));
    $t->ok(true, 'phsgi application is callable object');
    $t->is($status, 200, 'status is 200');
    $t->is($headers, array(array('Content-Type', 'text/plain')), "headers is [('Content-Type', 'text/plain')]");
    $t->is($body, array('Hello World!'), "body is ('Hello World!')");

    function anotherapp(&$env)
    {
        $body = array();
        foreach ($env as $k => $v) {
            $body[] = "$k => " . (is_array($v) ? '(' . implode(', ', $v) . ')' : $v);
        }
        return array(200, array(array('Content-Type', 'text/plain')), $body);
    }

    $app = 'anotherapp';

    $env = array(
        'REQUEST_METHOD'   => 'GET',
        'SERVER_PROTOCOL'  => 'HTTP/1.1',
        'phsgi.version'    => array(1, 0),
        );
    list($status, $headers, $body) = call_user_func_array($app, array(&$env));
    $t->is($status, 200, 'status is 200');
    $t->is($headers, array(array('Content-Type', 'text/plain')), "headers is [('Content-Type', 'text/plain')]");
    $t->is($body[0], 'REQUEST_METHOD => GET',       '[0] => REQUEST_METHOD => GET');
    $t->is($body[1], 'SERVER_PROTOCOL => HTTP/1.1', '[1] => SERVER_PROTOCOL => HTTP/1.1');
    $t->is($body[2], 'phsgi.version => (1, 0)',     '[2] => phsgi.version => (1, 0)');

    class Middleware
    {
        private $_app;
        protected function __construct($app)
        {
            $this->_app = $app;
        }
        static public function wrap($app)
        {
            $wrapapp = new self($app);
            return array($wrapapp, '_call');
        }
        public function _call(&$env)
        {
            list($status, $headers, $body) = call_user_func_array($this->_app, array(&$env));
            array_unshift($body, 'middleware_begin');
            array_push($body, 'middleware_end');

            $headers[] = array('X-TEST-PHSGI-MIDDLEWARE', 'Middleware');
            return array($status, &$headers, &$body);
        }
    }

    $app = 'app';
    $app = Middleware::wrap($app);

    $env = makeMockEnv();
    list($status, $headers, $body) = call_user_func_array($app, array(&$env));
    $t->is($status, 200, 'status is 200');
    $t->is($headers, array(array('Content-Type', 'text/plain'), array('X-TEST-PHSGI-MIDDLEWARE', 'Middleware')),
           "headers is [('Content-Type', 'text/plain'), ('X-TEST-PHSGI-MIDDLEWARE', 'Middleware')]");
    $t->is($body, array('middleware_begin', 'Hello World!', 'middleware_end'),
           "('middleware_begin', 'Hello World!', 'middleware_end')");
}
$t->append('testPhsgiApplicationExample');

$t->execute();
