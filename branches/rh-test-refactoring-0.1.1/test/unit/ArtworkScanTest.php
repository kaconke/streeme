<?php
include( dirname(__FILE__) . '/../bootstrap/doctrine.php' );

// Initialize the test object
$t = new lime_test( 1, new lime_output_color() );

$t->pass();