<?php
include( dirname(__FILE__) . '/../bootstrap/doctrine.php' );

// Initialize the test object
$t = new lime_test( 5, new lime_output_color() );

$album_table = Doctrine_Core::getTable('Album');

$t->comment( '->addAlbum' );
$first_insert_id = $album_table->addAlbum( 'með suð í eyrum við spilum endalaust' );
$t->like( $first_insert_id, '/\d+/', 'Successfully added an album entry.' );
$second_insert_id = $album_table->addAlbum( 'með suð í eyrum við spilum endalaust' );
$t->is( $first_insert_id, $second_insert_id, 'Updated an identical album entry.');
$third_insert_id = $album_table->addAlbum( 'gorillaz compilation' );
$t->like( $third_insert_id, '/\d+/', 'Successfully another album entry.' );
$fourth_insert_id = $album_table->addAlbum( 'gorillaz compilation' );
$t->is( $third_insert_id, $fourth_insert_id, 'Updated an identical album entry for second album.');

$t->comment( '->getList' );
$list = $album_table->getList( 'all', 'all' );
$count = count( $list );
$t->is( $count, '2', 'Successfully listed all albums' );