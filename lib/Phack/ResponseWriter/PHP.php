<?php
require_once('Phack/ResponseWriter.php');
require_once('Phack/Util.php');

class Phack_ResponseWriter_PHP extends Phack_ResponseWriter
{
    protected $output;

    public function __construct($fh = null)
    {
        if ($fh === null) {
            $fh = fopen('php://output', 'wb');
        }
        $this->output = $fh;
    }

    public function writeHeader($string, $replace = true)
    {
        header($string, $replace);
    }

    public function writeBody($contents)
    {
        if (is_resource($contents)) {
            Phack_Util::fcopy($this->output, $contents);
        }
        else {
            fwrite($this->output, $contents);
        }
    }

    public function close()
    {
        fclose($this->output);
    }
}
