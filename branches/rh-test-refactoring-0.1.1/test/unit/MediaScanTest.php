<?php
include( dirname(__FILE__) . '/../bootstrap/doctrine.php' );

// Initialize the test object
$t = new lime_test( 2, new lime_output_color() );

$test_construction = new MediaScan();
$t->comment( '->construct()');
$t->like( $test_construction->get_last_scan_id(), '/\d+/', 'Entered a new scan id successfully.' );
$t->comment( '->is_scanned()');
$t->is( $test_construction->is_scanned( 'd:\music\test\music.mp3', '1273082828' ), false, 'Song should not exist yet' );
$t->comment( '->add_song' );