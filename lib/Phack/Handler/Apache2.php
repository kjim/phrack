<?php
require_once('Phack/Handler.php');
require_once('Phack/Util.php');

class Phack_Handler_Apache2 implements Phack_Handler
{
    public function run($app)
    {
        $env = array_merge(
            array(
                'SERVER_PROTOCOL'   => 'HTTP/1.0',
                ),
            $_SERVER,
            array(
                'phsgi.version'        => array(1, 0),
                'phsgi.url_scheme'     => (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) ? 'https' : 'http'),
                'phsgi.input'          => fopen('php://stdin', 'r'),
                'phsgi.errors'         => fopen('php://stderr', 'w'),
                'phsgi.multithread'    => false,
                'phsgi.multiprocess'   => true,
                'phsgi.run_once'       => true,
                ));

        try {
            $res = Phack_Util::callApp($app, $env);

            if (is_array($res)) {
                $this->handleResponse($res, $env);
            }
            else {
                throw new Exception("Bad response " . (is_object($res) ? get_class($res) : $res));
            }

            $this->close($env);
        }
        catch (Exception $e) {
            $this->close($env);
            throw $e;
        }
    }

    protected function close(&$env)
    {
        fclose($env['phsgi.input']);
        fclose($env['phsgi.errors']);
    }

    protected function handleResponse(&$res, &$env)
    {
        list($status, $headers, $body) = $res;

        header($env['SERVER_PROTOCOL'] . ' ' . $status);
        foreach ($headers as $header) {
            list($k, $v) = $header;
            header($k.': '.$v);
        }
        foreach ($body as $e) {
            echo($e);
        }
    }

}
