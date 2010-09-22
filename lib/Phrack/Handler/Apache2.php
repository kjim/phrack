<?php
require_once('Phrack/Handler.php');
require_once('Phrack/Util.php');

class Phrack_Handler_Apache2 implements Phrack_Handler
{
    protected $writer;

    public function __construct($writer=null)
    {
        if ($writer === null) {
            require_once('Phrack/ResponseWriter/PHP.php');
            $writer = new Phrack_ResponseWriter_PHP();
        }
        $this->writer = $writer;
    }

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
            $res = Phrack_Util::callApp($app, $env);

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
        $this->writer->close();
        fclose($env['phsgi.input']);
        fclose($env['phsgi.errors']);
    }

    protected function handleResponse(&$res, &$env)
    {
        list($status, $headers, $body) = $res;

        $writer = $this->writer;
        $writer->writeHeader($env['SERVER_PROTOCOL'] . ' ' . $status);
        foreach ($headers as $h) {
            $writer->writeHeader($h[0].': '.$h[1]);
        }

        if (is_array($body) || $body instanceof Traversable) {
            foreach ($body as $e) {
                $writer->writeBody($e);
            }
        }
        else if (is_resource($body)) {
            $writer->writeBody($body);
        }
        else {
            throw new Exception("Bad body " . (is_object($body) ? get_class($body) : $body));
        }
    }
}
