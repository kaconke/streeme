<?php
include( dirname(__FILE__) . '/../bootstrap/doctrine.php' );

// Initialize the test object
$t = new lime_test( 6, new lime_output_color() );

$playlist_table = Doctrine_Core::getTable('Playlist');
Doctrine::loadData(sfConfig::get('sf_test_dir').'/fixtures/70_PlaylistTable');

$t->comment( '->addPlaylist' );
$first_insert_id  = $playlist_table->addPlaylist('A Playlist');
$t->is( $first_insert_id, 5, 'Successfully added a playlist entry.' );
$t->comment( '->deletePlaylist' );
$deleted_row_count = $playlist_table->deletePlaylist( PlaylistFilesTable::getInstance(), $first_insert_id );
$t->is( $deleted_row_count, 1, 'Successfully deleted a playlist entry' );
$t->comment( '->getList' );
$list  = $playlist_table->getList();
$count = count( $list );
$t->is( $count, 2, 'Correct list size' );
$t->comment( '->updateScanId' );
$playlist_table->updateScanId('itunes', 'Itunes 90\'s Playlist', 'B16E9C5DFFC4695D', 2);
$updated_record = $playlist_table->find(2);
$t->is($updated_record->scan_id, 2, 'Record updated to correct scan id');
$id = $playlist_table->updateScanId('itunes', 'Itunes Don\'t Exist', 'AC29CC9100DF56F', 2);
$t->is(id, 0, 'Correct Id for missing playlist');
$playlist_table->updateScanId('wjukebox', 'WJukebox Retro Playlist', null, 3);
$updated_record = $playlist_table->find(4);
$t->is($updated_record->scan_id, 3, 'Record updated to correct scan id');
