<?php
require_once(dirname(__FILE__).'/lib/bootstrap.php');
require_once('Phack/Util/MultiDict.php');

$t = new LimeTester();

function testBasicUsage($t)
{
    $data = array(
        array('foo', 'a'),
        array('foo', 'b'),
        array('bar', 'baz'),
        array('baz', 33)
        );
    $dict = new Phack_Util_MultiDict($data);

    $t->is($dict->get('foo'), 'b');
    $t->is($dict->get('bar'), 'baz');

    $t->is_deeply($dict->getAll('foo'), array('a', 'b'));
    $t->is_deeply($dict->getAll('baz'), array(33));

    $t->is_deeply($dict->items(), $data);

    $dict->add('foo', 'c');
    $t->is($dict->get('foo'), 'c');
    $t->is_deeply($dict->getAll('foo'), array('a', 'b', 'c'));

    $dict->add('qux', 'quux');
    $t->is($dict->get('qux'), 'quux');

    $dict->merge(array(array('foo', 'd'), array('qux', 'quuux'), array('quux', 'quuuux')));
    $t->is_deeply($dict->getAll('foo'), array('a', 'b', 'c', 'd'));
    $t->is_deeply($dict->getAll('qux'), array('quux', 'quuux'));
    $t->is_deeply($dict->getAll('quux'), array('quuuux'));

    $dict->remove('foo');
    $t->is($dict->get('foo'), null);
    $t->is_deeply($dict->getAll('foo'), array());
}
$t->append('testBasicUsage');

function testArrayAccessAPI($t)
{
    $dict = new Phack_Util_MultiDict();
    $dict['foo'] = 'a';
    $dict['foo'] = 'b';
    $dict['bar'] = 'baz';
    $dict['baz'] = 33;

    $t->is($dict['foo'], 'b');
    $t->is($dict['bar'], 'baz');
    $t->is($dict['baz'], 33);
    $t->is_deeply($dict->getAll('foo'), array('a', 'b'));

    // undefined offset
    $t->is(isset($dict['qux']), false);

    unset($dict['foo']);
    $t->is(isset($dict['foo']), false);
}
$t->append('testArrayAccessAPI');

function testMergeMixedDict($t)
{
    $dict = new Phack_Util_MultiDict();
    $dict->mergeMixedDict(
        array(
            'foo' => array('a', 'b'),
            'bar' => 'baz',
            'baz' => 33));

    $t->is_deeply($dict->getAll('foo'), array('a', 'b'));
    $t->is_deeply($dict->getAll('bar'), array('baz'));
    $t->is_deeply($dict->getAll('baz'), array(33));
}
$t->append('testMergeMixedDict');

$t->execute();
