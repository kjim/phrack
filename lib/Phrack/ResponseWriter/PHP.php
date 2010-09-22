<?php
require_once('Phrack/ResponseWriter.php');
require_once('Phrack/Util.php');

class Phrack_ResponseWriter_PHP extends Phrack_ResponseWriter
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
            Phrack_Util::fcopy($this->output, $contents);
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
