<?php
require_once(dirname(__FILE__).'/lime.php');

error_reporting(-1);
date_default_timezone_set('UTC');

function appendIncludePath($path)
{
    set_include_path(get_include_path() . PATH_SEPARATOR . $path);
}
appendIncludePath(dirname(__FILE__).'/../../lib');

class LimeTester
{
    private $tests;

    public function __construct()
    {
        $this->tests = array();
    }

    public function append($function, $message="")
    {
        $this->tests[] = array($function, $message);
    }

    public function execute($extraArgs=array())
    {
        $t = new lime_test();
        foreach ($this->tests as $testinfo) {
            if (is_callable($testinfo[0])) {
                call_user_func_array($testinfo[0], array_merge(array($t), $extraArgs));
            }
            else {
                $test = is_array($testinfo[0]) ? get_class($testinfo[0]) . '.' . $testinfo[1] : $testinfo[0];
                throw new InvalidArgumentException("Test function not found: $test");
            }
        }
    }
}

function str_contains($text, $needle)
{
    return strpos($text, $needle) !== false;
}

function is_except($t, $except, $callable, array $arguments=array(), $msg=null)
{
    try {
        call_user_func_array($callable, $arguments);
        $t->fail('not raised $except');
    }
    catch (Exception $e) {
        $t->is(get_class($e), $except, "raised $except ok: $msg");
    }
}

// helper functions
function call_static($class, $method, array $args = array())
{
    return call(array($class, $method), $args);
}

function call($callback, array $args = array())
{
    return call_user_func_array($callback, $args);
}
