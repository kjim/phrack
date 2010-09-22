<?php
require_once(dirname(__FILE__).'/lib/bootstrap.php');
require_once('Phrack/Util/OrderedMultiDict.php');

$t = new LimeTester();

function testDictOperations($t)
{
    $data = array(
        array('foo', 'a'),
        array('foo', 'b'),
        array('bar', 'baz'),
        array('baz', 33)
        );

    $dict = new Phrack_Util_OrderedMultiDict($data);
    $t->is_deeply($dict->keys(), array('foo', 'foo', 'bar', 'baz'));
    $t->is_deeply($dict->values(), array('a', 'b', 'baz', 33));
    $t->is_deeply($dict->items(), $data);

    $dict->add('foo', 'c');    $data[] = array('foo', 'c');
    $dict->add('qux', 'quux'); $data[] = array('qux', 'quux');

    $t->is_deeply($dict->keys(), array('foo', 'foo', 'bar', 'baz', 'foo', 'qux'));
    $t->is_deeply($dict->values(), array('a', 'b', 'baz', 33, 'c', 'quux'));
    $t->is_deeply($dict->items(), $data);

    $dict->mergeMixedDict(
        array(
            'foo' => array('d', 'e'),
            'bar' => 'z',
            ));
    $data[] = array('foo', 'd');
    $data[] = array('foo', 'e');
    $data[] = array('bar', 'z');

    $t->is_deeply($dict->keys(), array('foo', 'foo', 'bar', 'baz', 'foo', 'qux', 'foo', 'foo', 'bar'));
    $t->is_deeply($dict->values(), array('a', 'b', 'baz', 33, 'c', 'quux', 'd', 'e', 'z'));
    $t->is_deeply($dict->items(), $data);

    $dict->remove('foo');
    unset($data[0], $data[1], $data[4], $data[6], $data[7]);
    $data = array_merge($data);

    $t->is_deeply($dict->keys(), array('bar', 'baz', 'qux', 'bar'));
    $t->is_deeply($dict->values(), array('baz', 33, 'quux', 'z'));
    $t->is_deeply($dict->items(), $data);
}
$t->append('testDictOperations');

$t->execute();
