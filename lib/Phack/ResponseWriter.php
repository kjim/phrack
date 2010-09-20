<?php

interface Phack_ResponseWriter
{
    public function writeHeader($string, $replace = true);
    public function writeBody($string);
}
