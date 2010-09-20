<?php
require_once('Phack/ResponseWriter.php');

class Phack_ResponseWriter_PHP implements Phack_ResponseWriter
{
    public function writeHeader($string, $replace = true)
    {
        header($string, $replace);
    }

    public function writeBody($string)
    {
        echo($string);
    }
}
