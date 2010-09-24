<?php
require_once(dirname(__FILE__).'/lib/bootstrap.php');
require_once('Phrack/Session.php');

$t = new LimeTester();

function testSessionOperate($t)
{
    $environ = array(
        'phsgix.session' => array(),
        'phsgix.session.options' => array('id' => '6f8e77cc403b4e5f84f4baacb4b1da8a'),
        );

    $session = new Phrack_Session($environ);
    $t->is($session->getId(), '6f8e77cc403b4e5f84f4baacb4b1da8a');
    $t->is($session->keys(), array());

    $session->set('foo', 'Foo');
    $session->set('bar', 'Bar');
    $session->set('baz', 'Baz');

    $keys = $session->keys();
    sort($keys);
    $t->is($keys, array('bar', 'baz', 'foo'));

    $session->remove('foo');
    $session->remove('bar');
    $t->is($session->keys(), array('baz'));

    $session->expire();
    $t->is($session->keys(), array());
    $t->ok($environ['phsgix.session.options']['expire']);
}
$t->append('testSessionOperate');

$t->execute();
