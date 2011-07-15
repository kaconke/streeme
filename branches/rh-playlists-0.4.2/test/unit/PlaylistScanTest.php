<?php
include( dirname(__FILE__) . '/../bootstrap/doctrine.php' );

// Initialize the test object
$t = new lime_test( 6, new lime_output_color() );

Doctrine::loadData(sfConfig::get('sf_test_dir').'/fixtures/80_PlaylistScan');
$playlist_scan = new PlaylistScan('itunes');
$itunes_parser = new StreemeItunesPlaylistParser(  dirname(__FILE__) . '/../files/iTunes Music Library.xml' );

$t->comment('->construct()');
$playlist_scan = new PlaylistScan('itunes');

$t->comment('->get_last_scan_id()');
$t->is($playlist_scan->get_last_scan_id(), 2, 'Got valid playlist scan id');

$t->comment('->get_service_name()');
$t->is($playlist_scan->get_service_name(), 'itunes', 'got valid source name');

$t->comment('->is_scanned');
$playlist_id = $playlist_scan->is_scanned( $playlist_scan->get_service_name(), '90\'s Rock', 'B16E9C5DFFC4695D');
$t->is($playlist_id, '1', 'Targeted the correct playlist');
$t->is($playlist_scan->get_total_playlists(), 1, 'Playlist count incremented');

/*
$playlist_name = $itunes_playlist_id = null;
$playlist_songs = array();
while($itunes_parser->getPlaylist($playlist_name, $itunes_playlist_id, $playlist_songs))
{
  echo $playlist_name;
}
*/