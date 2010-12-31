<?php
include( dirname(__FILE__) . '/../bootstrap/doctrine.php' );

// Initialize the test object
$t = new lime_test( 11, new lime_output_color() );

$album_table = Doctrine_Core::getTable('Album');

$t->comment( '->addAlbum' );
$first_insert_id = $album_table->addAlbum( 'með suð í eyrum við spilum endalaust' );
$t->like( $first_insert_id, '/\d+/', 'Successfully added an album entry.' );
$second_insert_id = $album_table->addAlbum( 'með suð í eyrum við spilum endalaust' );
$t->is( $first_insert_id, $second_insert_id, 'Updated an identical album entry.');
$third_insert_id = $album_table->addAlbum( 'gorillaz compilation' );
$t->like( $third_insert_id, '/\d+/', 'Successfully added another album entry.' );
$fourth_insert_id = $album_table->addAlbum( 'gorillaz compilation' );
$t->is( $third_insert_id, $fourth_insert_id, 'Updated an identical album entry for second album.');

$t->comment( '->getList' );
//add minimal song data to test the relationship
$artist_table = Doctrine_Core::getTable('Artist');
$id1 = $artist_table->addArtist('Sigur Ros');
$id2 = $artist_table->addArtist('Gorillaz');
$song_table = Doctrine_Core::getTable( 'Song' );
$song_table->addSong( $id1, $first_insert_id, null, 1, array( 'filename' => 'file://localhost/file.1', 'mtime' => '0202002' ) );
$song_table->addSong( $id2, $third_insert_id, null, 1, array( 'filename' => 'file://localhost/file.2', 'mtime' => '0202020' ) );

$list = $album_table->getList( 'all', 'all' );
$count = count( $list );
$t->is( $count, 2, 'Successfully listed all albums' );
$list = $album_table->getList( 'g', 'all' );
$count2 = count( $list );
$t->is( $count2, 1, 'correct record count for alphabetical listing' );
$t->is( $list[0]['name'], 'gorillaz compilation', 'Successfully narrowed list by alphabetical character' );
$list = $album_table->getList( 'G', 'all' );
$count4 = count( $list );
$t->is( $count4, 1, 'correct record count for alphabetical listing' );
$t->is( $list[0]['name'], 'gorillaz compilation', 'Alpha char is case insensitive' );
$list = $album_table->getList( 'all', $id1 );
$count3 = count( $list );
$t->is( $count3, 1, 'correct record count for artist listing' );
$t->is( $list[0]['name'], 'með suð í eyrum við spilum endalaust', 'Successfully narrowed list by artist id' );