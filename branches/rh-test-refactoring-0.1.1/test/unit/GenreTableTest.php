<?php
include( dirname(__FILE__) . '/../bootstrap/doctrine.php' );

// Initialize the test object
$t = new lime_test( 7, new lime_output_color() );

$genre_table = Doctrine_Core::getTable('Genre');

$t->comment( '->addGenre' );
$first_insert_id = $genre_table->addGenre( 'Electronic' );
$t->is( $first_insert_id, '53', 'Successfully added an existing genre.' );
$second_insert_id = $genre_table->addGenre( 'Electronic' );
$t->is( $first_insert_id, $second_insert_id, 'Got the same genre fixture.');
$third_insert_id = $genre_table->addGenre( 'Some Awesome Custom Genre! Woo!' );
$t->like( $third_insert_id, '/\d+/', 'Successfully another artist entry.' );
$fourth_insert_id = $genre_table->addGenre( 'Some Awesome Custom Genre! Woo!' );
$t->is( $third_insert_id, $fourth_insert_id, 'Updated an identical artist entry for second artist entry.');

$t->comment( '->getList' );
$list = $genre_table->getList( 'all' );
$count = count( $list );
$t->is( $count, '2', 'Successfully listed all genres' );
$list2 = $genre_table->getList( 'S' );
$count2 = count( $list2 );
$t->is( $list2[0]['name'], 'Sigur Rós', 'Successfully selected Letter S in alpha grouping' );
$t->is( $count2, 1, 'List narrowed to 1 result by alpha group');
