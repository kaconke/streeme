<?php
include( dirname(__FILE__) . '/../bootstrap/doctrine.php' );

// Initialize the test object
$t = new lime_test( 6, new lime_output_color() );

Doctrine::loadData(sfConfig::get('sf_test_dir').'/fixtures/80_PlaylistScan');
$playlist_scan = new PlaylistScan('itunes');
$itunes_parser = new StreemeItunesPlaylistParser(  dirname(__FILE__) . '/../files/iTunes Music Library.xml' );
$playlist_name = $itunes_playlist_id = null;
$playlist_songs = array();
while($itunes_parser->getPlaylist($playlist_name, $itunes_playlist_id, $playlist_songs))
{
  echo $playlist_name;
}