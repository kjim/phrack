<?php
require_once(dirname(__FILE__).'/lib/bootstrap.php');
require_once(dirname(__FILE__).'/lib/apps.php');

require_once('Phrack/Middleware/Session.php');
require_once('Phrack/Session/State/Native.php');
require_once('Phrack/Session/Store/PDO.php');
require_once('Phrack/Util.php');

$t = new LimeTester();

function fetchSessionData($id, $pdo)
{
    $statement = $pdo->prepare("SELECT * FROM sessions WHERE id = '$id';");
    $statement->execute();
    return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function insertSessionData($id, $sessionData, $updatedAt, $pdo)
{
    $pdo->exec(
        "INSERT INTO sessions (id, session_data, updated_at)" .
        "  VALUES ('$id', '$sessionData', $updatedAt);");
}

function updateSessionData($id, $sessionData, $updatedAt, $pdo)
{
    $pdo->exec(
        "UPDATE sessions set session_data = '$sessionData', updated_at = $updatedAt" .
        "  WHERE id = '$id';");
}

function testOpenClose($t, $store, $pdo)
{
    $t->ok($store->onOpen());
    $t->ok($store->onClose());
}
$t->append('testOpenClose');

function testOperateToPurgeSessionData($t, $store, $pdo)
{
    insertSessionData('3823', 'sess', time(), $pdo);
    $t->ok($store->onPurge(3823));

    $fetched = fetchSessionData(3823, $pdo);
    $t->is(count($fetched), 0);
}
$t->append('testOperateToPurgeSessionData');

function testOperateToFetchSessionData($t, $store, $pdo)
{
    $store->onPurge(1234);
    $session = $store->onFetch(1234);
    $t->is($session, '');

    updateSessionData('1234', 'sess', time(), $pdo);
    $session = $store->onFetch(1234);
    $t->is($session, 'sess');
}
$t->append('testOperateToFetchSessionData');

function testOperateToStoreSessionData($t, $store, $pdo)
{
    $store->onPurge(2345);
    $session = $store->onFetch(2345);

    $session = 'ABCDEFG';
    $t->ok($store->onStore(2345, $session));

    $fetched = fetchSessionData(2345, $pdo);
    $t->is($fetched[0]['session_data'], 'ABCDEFG');
}
$t->append('testOperateToStoreSessionData');

function testOperateToSessionGC($t, $store, $pdo)
{
    $now = time();
    insertSessionData(1, '1', $now-1000, $pdo);
    insertSessionData(2, '2', $now-100, $pdo);
    insertSessionData(3, '3', $now-10, $pdo);

    $t->ok($store->onGC(50));
    $t->is(count(fetchSessionData(1, $pdo)), 0);
    $t->is(count(fetchSessionData(2, $pdo)), 0);
    $t->is(count(fetchSessionData(3, $pdo)), 1);

    $t->ok($store->onGC(-11));
    $t->is(count(fetchSessionData(3, $pdo)), 0);
}
$t->append('testOperateToSessionGC');

function testNativeSessionWithPDO($t, $store, $pdo)
{
    $environ = array();
    $app = 'appSetGreeting';
    $app = Phrack_Middleware_Session::wrap($app, array('store' => $store));
    $_ = Phrack_Util::callApp($app, $environ);

    $environ = array();
    $app = 'appGreeting';
    $app = Phrack_Middleware_Session::wrap($app, array('store' => $store));
    list($_, $_, $body) = Phrack_Util::callApp($app, $environ);
    $t->is_deeply($body, array('Hello ', 'Foo'));

    $environ = array();
    $app = 'appExpireSession';
    $app = Phrack_Middleware_Session::wrap($app, array('store' => $store));
    Phrack_Util::callApp($app, $environ);

    $environ = array();
    $app = 'appGetSessionKeys';
    $app = Phrack_Middleware_Session::wrap($app, array('store' => $store));
    list($_, $_, $body) = Phrack_Util::callApp($app, $environ);
    $t->is($body, array(), 'empty session');
}
$t->append('testNativeSessionWithPDO');


function setupTable($pdo)
{
    $pdo->exec(
        "CREATE TABLE sessions (id CHAR(72) PRIMARY KEY, session_data TEXT, updated_at TIMESTAMP);");
}

$pdo = new PDO('sqlite:/tmp/foo.db');
setupTable($pdo);
$store = new Phrack_Session_Store_PDO(array('pdo' => $pdo));

ob_start();
$t->execute(array($store, $pdo));
ob_flush();
