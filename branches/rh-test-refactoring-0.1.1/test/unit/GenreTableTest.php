<?php
include( dirname(__FILE__) . '/../bootstrap/doctrine.php' );

// Initialize the test object
$t = new lime_test( 11, new lime_output_color() );

$genre_table = Doctrine_Core::getTable('Genre');

$t->comment( '->addGenre' );
$first_insert_id = $genre_table->addGenre( 'Electronic' );
$t->is( $first_insert_id, '53', 'Successfully selected an existing genre.' );
$second_insert_id = $genre_table->addGenre( 'Electronic' );
$t->is( $first_insert_id, $second_insert_id, 'Got the same genre fixture.');
$third_insert_id = $genre_table->addGenre( 'Some Awesome Custom Genre! Woo!' );
$t->like( $third_insert_id, '/\d+/', 'Successfully added a new genre entry.' );
$fourth_insert_id = $genre_table->addGenre( 'Some Awesome Custom Genre! Woo!' );
$t->is( $third_insert_id, $fourth_insert_id, 'Selected retargeted the second genre entry.');
$fifth_insert_id = $genre_table->addGenre( 'Русский' );
$t->like( $fifth_insert_id, '/\d+/', 'Successfully added a new UTF-8 entry' );
$sixth_insert_id = $genre_table->addGenre( 'Русский' );
$t->is( $fifth_insert_id, $sixth_insert_id, 'Got the same genre fixture in UTF-8.');

$t->comment( '->getList' );
$song_table = Doctrine_Core::getTable( 'Song' );
$song_table->addSong( null, null, $first_insert_id, 1, array( 'filename' => 'file://localhost/file.1', 'mtime' => '0202002' ) );
$song_table->addSong( null, null, $third_insert_id, 1, array( 'filename' => 'file://localhost/file.2', 'mtime' => '0202020' ) );
$song_table->addSong( null, null, $fifth_insert_id, 1, array( 'filename' => 'file://localhost/file.3', 'mtime' => '2200200' ) );

$list = $genre_table->getList( 'all' );
$count = count( $list );
$t->is( $count, '3', 'Successfully listed all genres' );
$list2 = $genre_table->getList( 'S' );
$count2 = count( $list2 );
$t->is( $count2, 1, 'List narrowed to 1 result by alpha group');
$t->is( $list2[0]['name'], 'Some Awesome Custom Genre! Woo!', 'Successfully selected Letter S in alpha grouping' );
$list2 = $genre_table->getList( 's' );
$count3 = count( $list2 );
$t->is( $count3, 1, 'List narrowed to 1 result by alpha group');
$t->is( $list2[0]['name'], 'Some Awesome Custom Genre! Woo!', 'Alpha char is case insensitive' );