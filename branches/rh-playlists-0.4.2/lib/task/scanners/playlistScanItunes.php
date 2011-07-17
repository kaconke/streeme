<?php
/**
 * playlistScanItunes
 *
 * Itunes playlist ingest process
 *
 * @package    streeme
 * @author     Richard Hoar
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
$itunes_music_library   = sfConfig::get( 'app_itunes_xml_location' );
$mapped_drive_locations = sfConfig::get( 'app_mdl_mapped_drive_locations' );
$allowed_filetypes      = array_map( 'strtolower', sfConfig::get( 'app_aft_allowed_file_types' ) );
$itunes_parser          = new StreemeItunesPlaylistParser( $itunes_music_library );

$playlist_name = $itunes_playlist_id = null;
$playlist_songs = array();
while( $itunes_parser->getPlaylist( $playlist_name, $itunes_playlist_id, $playlist_songs ) )
{
  echo $playlist_name;
}