<?php

abstract class Phrack_ResponseWriter
{
    abstract public function writeHeader($string, $replace = true);
    abstract function writeBody($string);

    public function close()
    { }
}
