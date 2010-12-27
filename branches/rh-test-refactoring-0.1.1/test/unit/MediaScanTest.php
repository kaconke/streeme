<?php
include( dirname(__FILE__) . '/../bootstrap/doctrine.php' );

// Initialize the test object
$t = new lime_test( 2, new lime_output_color() );

$test_construction = new MediaScan();
$t->pass('This test always passes.');
$t->like( $test_construction->get_last_scan_id(), '/\d+/', 'Entered a new scan id successfully.' );