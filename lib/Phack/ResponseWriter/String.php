<?php
require_once('Phack/ResponseWriter.php');

class Phack_ResponseWriter_String implements Phack_ResponseWriter
{
    protected $h = array();
    protected $b = '';

    public function writeHeader($string, $replace = true)
    {
        $this->h[] = $string;
    }

    public function writeBody($string)
    {
        $this->b .= $string;
    }

    public function header()
    {
        return implode("\n", $this->h);
    }

    public function body()
    {
        return $this->b;
    }
}
