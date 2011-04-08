<?php
require_once(dirname(__FILE__).'/lib/bootstrap.php');
require_once('Phrack/Middleware/Conditional.php');
require_once('Phrack/Util.php');

$t = new LimeTester();

class HelloMiddleware extends Phrack_Middleware
{
    public function call(&$environ)
    {
        $environ['msg'] = 'Hello';
        return $this->callApp($environ);
    }

    static public function wrap()
    {
        $args = func_get_args();
        return parent::wrap(__CLASS__, $args);
    }
}

function msg(&$environ)
{
    return array('200 OK', array(), array(isset($environ['msg']) ? $environ['msg'] : ''));
}

function enabled(&$environ)
{
    return true;
}

function disabled(&$environ)
{
    return false;
}

function builder($app)
{
    return HelloMiddleware::wrap($app);
}

function testConditionEnabled($t)
{
    $app = 'msg';
    $app = Phrack_Middleware_Conditional::wrap(
        $app, array('condition' => 'enabled', 'builder' => 'builder'));

    $environ = array();
    list($_, $_, $body) = Phrack_Util::callApp($app, $environ);
    $t->is_deeply($body, array('Hello'));
}
$t->append('testConditionEnabled');

function testConditionDisabled($t)
{
    $app = 'msg';
    $app = Phrack_Middleware_Conditional::wrap(
        $app, array('condition' => 'disabled', 'builder' => 'builder'));

    $environ = array();
    list($_, $_, $body) = Phrack_Util::callApp($app, $environ);
    $t->is_deeply($body, array(''));
}
$t->append('testConditionDisabled');

$t->execute();
