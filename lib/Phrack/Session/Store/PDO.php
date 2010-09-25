<?php
require_once('Phrack/Session/Store/Native.php');

/**
 * Session store with PDO
 *
 * SYNOPSIS
 * --------
 *
 *     $store = new Phrack_Session_Store_PDO(array(
 *         'pdo' => new PDO( ... ),
 *         'table_name' => 'sessions', // `sessions` by default
 *         ));
 *
 *
 * SESSION TABLE SCHEMA
 * --------------------
 * Your session table must have at least the following schema structure:
 *
 *     CREATE TABLE sessions (
 *         id             CHAR(72) PRIMARY KEY
 *         , session_data TEXT
 *         , updated_at   TIMESTAMP
 *     );
 *
 */
class Phrack_Session_Store_PDO extends Phrack_Session_Store_Native
{
    protected $pdo;
    protected $table;

    public function __construct(array $params = array())
    {
        if (!isset($params['pdo'])) {
            throw new Exception("PDO instance was not available in the argument list");
        }

        $params = array_merge(array('table_name' => 'sessions'), $params);
        $this->pdo = $params['pdo'];
        $this->table = $params['table_name'];

        $this->setSessionHandler(
            array($this, 'onOpen'),
            array($this, 'onClose'),
            array($this, 'onFetch'),
            array($this, 'onStore'),
            array($this, 'onPurge'),
            array($this, 'onGC')
            );
    }

    public function onOpen($path = null, $name = null)
    {
        return true;
    }

    public function onClose()
    {
        return true;
    }

    public function onPurge($id)
    {
        $table = $this->table;
        try {
            $sql = "DELETE FROM $table WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(1, $id, PDO::PARAM_STR);
            $stmt->execute();
        } catch (PDOException $e) {
            $this->throwError($e);
        }

        return true;
    }

    public function onGC($lifetime)
    {
        $table = $this->table;
        try {
            $sql = "DELETE FROM $table WHERE updated_at < ".(time() - $lifetime);
            $this->pdo->query($sql);
        } catch (PDOException $e) {
            $this->throwError($e);
        }

        return true;
    }

    public function onFetch($id)
    {
        $table = $this->table;
        try {
            $sql = "SELECT session_data FROM $table WHERE id = ?";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(1, $id, PDO::PARAM_STR, 255);

            $stmt->execute();
            // it is recommended to use fetchAll so that PDO can close the DB cursor
            // we anyway expect either no rows, or one row with one column. fetchColumn, seems to be buggy #4777
            $sessionRows = $stmt->fetchAll(PDO::FETCH_NUM);

            if (count($sessionRows) == 1) {
                return $sessionRows[0][0];
            } else {
                // session does not exist, create it
                $sql = "INSERT INTO $table (id, session_data, updated_at) VALUES (?, ?, ?)";

                $stmt = $this->pdo->prepare($sql);
                $stmt->bindParam(1, $id, PDO::PARAM_STR);
                $stmt->bindValue(2, '', PDO::PARAM_STR);
                $stmt->bindValue(3, time(), PDO::PARAM_INT);
                $stmt->execute();

                return '';
            }
        } catch (PDOException $e) {
            $this->throwError($e);
        }
    }

    public function onStore($id, $data)
    {
        $table = $this->table;
        $now = time();
        try {
            $sql = "UPDATE $table SET session_data = ?, updated_at = $now WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(1, $data, PDO::PARAM_STR);
            $stmt->bindParam(2, $id, PDO::PARAM_STR);
            $stmt->execute();
        } catch (PDOException $e) {
            $this->throwError($e);
        }

        return true;
    }

    protected function throwError($e)
    {
        throw new RuntimeException(sprintf('PDOException was thrown when trying to manipulate session data: %s', $e->getMessage()), 0, $e);
    }
}
