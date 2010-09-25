<?php

function appSetGreeting(&$environ)
{
    $session =& $environ['phsgix.session'];
    $session['greeting_to'] = 'Foo';
    return array('200 OK', array(), array());
}

function appGreeting(&$environ)
{
    $session =& $environ['phsgix.session'];
    return array('200 OK', array(), array('Hello ', $session['greeting_to']));
}

function appExpireSession(&$environ)
{
    $environ['phsgix.session.options']['expire'] = true;
    return array('200 OK', array(), array());
}

function appGetSessionKeys(&$environ)
{
    $session =& $environ['phsgix.session'];
    return array('200 OK', array(), array_keys($session));
}
