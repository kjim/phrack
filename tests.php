<?php
require_once(dirname(__FILE__).'/test/lib/bootstrap.php');

$h = new lime_harness(new lime_output_color());
$h->register(dirname(__FILE__)."/test");
$h->run();
