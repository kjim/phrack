<?php
require_once(dirname(__FILE__).'/bootstrap.php');
require_once('Phrack/Handler/Apache2.php');
require_once('Phrack/Middleware.php');
require_once('Phrack/Middleware/Session.php');
require_once('Phrack/Session/Store/PDO.php');

function countup(&$environ)
{
    $session =& $environ['phsgix.session'];
    if (!isset($session['counter'])) {
        $session['counter'] = -1;
    }
    $session['counter'] += 1;

    return array('200 OK', array(array('Content-Type', 'text/plain')),
                 array((string) $session['counter']));
}

class InitializeSessionTableIfNotExists extends Phrack_Middleware
{
    public function call(&$environ)
    {
        $pdo = $this->args['pdo'];
        $stmt = $pdo->prepare("SELECT count(1) as count from sqlite_master WHERE type = 'table' AND name = 'sessions'");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result['count'] <= 0) {
            $this->initSessionTable($pdo);
        }
        return $this->callApp($environ);
    }

    function initSessionTable($pdo)
    {
        $pdo->exec("DROP TABLE IF EXISTS sessions;");
        $pdo->exec("CREATE TABLE sessions (id CHAR(72) PRIMARY KEY, session_data TEXT, updated_at TIMESTAMP);");
    }

    static public function wrap()
    {
        $args = func_get_args();
        return parent::wrap(__CLASS__, $args);
    }
}

$pdo = new PDO('sqlite:/tmp/0f5a75f765b840e4b87d413c633960ae.db');

$app = 'countup';
$app = Phrack_Middleware_Session::wrap(
    $app, array('store' => new Phrack_Session_Store_PDO(array('pdo' => $pdo))));
$app = InitializeSessionTableIfNotExists::wrap($app, array('pdo' => $pdo));

$handler = new Phrack_Handler_Apache2();
$handler->run($app);
