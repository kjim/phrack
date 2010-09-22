<?php
require_once('Phrack/ResponseWriter.php');

class Phrack_ResponseWriter_String extends Phrack_ResponseWriter
{
    protected $h = array();
    protected $b = '';

    public function writeHeader($string, $replace = true)
    {
        $this->h[] = $string;
    }

    public function writeBody($contents)
    {
        if (is_resource($contents)) {
            $this->b .= fread($contents, 8192);
            fclose($contents);
        }
        else {
            $this->b .= $contents;
        }
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
