<?php

/**
 * mediaScanFilesystem
 *
 * Filesystem media ingest process
 *
 * package    streeme
 * author     Richard Hoar
 */
require_once( dirname(__FILE__) . '/../../vendor/getid3-1.9.0/getid3/getid3.php' );
   
$watched_folers         = sfConfig::get( 'app_wf_watched_folders' );
$mapped_drive_locations = sfConfig::get( 'app_mdl_mapped_drive_locations' );
$allowed_filetypes      = array_map( 'strtolower', sfConfig::get( 'app_aft_allowed_file_types' ) );
$media_scanner          = new MediaScan();
$id3_scanner            = new getID3();
$id3_scanner->encoding  = 'UTF-8';

foreach ( $watched_folders as $key => $path )
{
  scan_directory( $path, $allowed_filetypes, $media_scanner, $id3_scanner );
}

$media_scanner->finalize_scan();

echo "\r\n";
echo $media_scanner->get_summary();

/**
 * Recursive directory scanner
 *
 * @param path - str: the path to scan with no trailing slash
 * @param allowed_filetypes  arr: mp3, ogg etc
 * @param media_scanner str: the media scanner object
 */
function scan_directory( $path, $allowed_filetypes, $media_scanner, $id3_scanner )
{
  $dp = opendir( $path );

  while($filename = readdir($dp))
  {
    //create the full pathname and streeme pathname
    $full_file_path = $path . '/' . $filename;
    
    //skip hidden files/folders
    if ($filename{0} === '.')
    {
      continue;
    }
    
    //it's a directory, recurse from this level
    if( is_dir( $full_file_path ) )
    {
      scan_directory( $full_file_path, $allowed_filetypes, $media_scanner, $id3_scanner );
      continue;
    }

    $file_stat = stat( $full_file_path );

    //is it a usable file?
    if ( $file_stat['size'] === 0 || !in_array( strtolower( substr( $filename, -3 ) ), $allowed_filetypes ) ) continue;
      
    $streeme_path_name = iconv( sfConfig::get( app_filesystem_encoding, 'ISO-8859-1' ), 'UTF-8//TRANSLIT', $full_file_path );
    
    //has it been scanned before?
    if ( $media_scanner->is_scanned(  $streeme_path_name, $file_stat[ 'mtime' ] ) ) continue;

    echo "Scanning " . $filename . "\n";

    //get the file information from pathinfo in case we need a substitute song name
    $pinfo = pathinfo( $full_file_path );
    
    /**
     * Process the files using the following criteria
     * high    high    medium    none
     * apex -> id3v2 -> id3v1 -> null
     */
    $value = $id3_scanner->analyze( $full_file_path );
    
    $tags = $value[ 'tags' ];
    
    if( isset( $tags[ 'id3v1' ][ 'track' ][0] ) && is_int( $tags[ 'id3v1' ][ 'track' ][0] ) )
    {
      //could be an int
      $tracknumber = $tags[ 'id3v1' ][ 'track' ][0];
    }
    else if( isset( $tags[ 'id3v2' ][ 'track_number' ][0] ) && !empty( $tags[ 'id3v2' ][ 'track_number' ][0] ) )
    {
      //or it could be 5/12
      $temp = explode( '/', $tags[ 'id3v2' ][ 'track_number' ][0] );
      $tracknumber = $temp[0];
    }
    else if( isset( $tags[ 'ape' ][ 'track_number' ][0] ) && !empty( $tags[ 'ape' ][ 'track_number' ][0] ) )
    {
      //or it could be 5/12 APEX
      $temp = explode( '/', $tags[ 'ape' ][ 'track_number' ][0] );
      $tracknumber = $temp[0];
    }
    else
    {
      //or it's missing
      $tracknumber = 0;
    }
    
    //get the set numbers in cases of multi album collections
    $set_index = $set_total = 1;
    $rawSet = ( $tags['ape'][ 'part_of_a_set' ][0] )   ? $tags['ape'][ 'part_of_a_set' ][0]   : ( ($tags['id3v2'][ 'part_of_a_set' ][0] ) ? $tags['id3v2'][ 'part_of_a_set' ][0] : ( ( $tags['id3v1'][ 'part_of_a_set' ][0] )  ? $tags['id3v1'][ 'part_of_a_set' ][0]  : null ) );
    if(strlen($rawSet) > 0)
    {
      $parts = explode('/', $rawSet);
      $set_index = (int) @$parts[0];
      $set_total = (int) @$parts[1];
    }

    $song_array = array();
    $song_array[ 'artist_name' ]      = StreemeUtil::xmlize_utf8_string( ( $tags['ape'][ 'artist' ][0] ) ? $tags['ape'][ 'artist' ][0] : ( ( $tags['id3v2'][ 'artist' ][0] ) ? $tags['id3v2'][ 'artist' ][0] : ( ( $tags['id3v1'][ 'artist' ][0] ) ? $tags['id3v1'][ 'artist' ][0] : null ) ) );
    $song_array[ 'album_name' ]       = StreemeUtil::xmlize_utf8_string( ( $tags['ape'][ 'album' ][0] )  ? $tags['ape'][ 'album' ][0]  : ( ( $tags['id3v2'][ 'album' ][0] )  ? $tags['id3v2'][ 'album' ][0]  : ( ( $tags['id3v1'][ 'album' ][0] )  ? $tags['id3v1'][ 'album' ][0]  : null ) ) );
    $song_array[ 'song_name' ]        = StreemeUtil::xmlize_utf8_string( ( $tags['ape'][ 'title' ][0] )  ? $tags['ape'][ 'title' ][0]  : ( ( $tags['id3v2'][ 'title' ][0] )  ? $tags['id3v2'][ 'title' ][0]  : ( ( $tags['id3v1'][ 'title' ][0] )  ? $tags['id3v1'][ 'title' ][0]  : $pinfo['filename'] ) ) );
    $song_array[ 'song_length' ]      = $value[ 'playtime_string' ] ;
    $song_array[ 'accurate_length' ]  = ( floor( ( (float) $value[ 'playtime_seconds' ] ) * 1000 ) );
    $song_array[ 'genre_name' ]       = ( $tags['ape'][ 'genre' ][0] )  ? $tags['ape'][ 'genre' ][0]  : ( ( $tags['id3v2'][ 'genre' ] ) ? $tags['id3v2'][ 'genre' ][0] :  ( ( $tags['id3v1'][ 'genre' ][0] )  ? $tags['id3v1'][ 'genre' ][0]  : null ) );
    $song_array[ 'filesize' ]         = $file_stat[ 'size' ];
    $song_array[ 'bitrate' ]          = ( floor ( ( (int) $value[ 'audio' ][ 'bitrate' ] ) / 1000 ) );
    $song_array[ 'yearpublished' ]    = ( $tags['ape'][ 'year' ][0] )   ? $tags['ape'][ 'year' ][0]   : ( ($tags['id3v2'][ 'year' ][0] ) ? $tags['id3v2'][ 'year' ][0] : ( ( $tags['id3v1'][ 'year' ][0] )  ? $tags['id3v1'][ 'year' ][0]  : null ) );
    $song_array[ 'tracknumber']       = $tracknumber;
    $song_array[ 'label' ]            = StreemeUtil::xmlize_utf8_string( ( $tags['ape'][ 'label' ][0] )  ? $tags['ape'][ 'label' ][0]  : ( ( $tags['id3v2'][ 'label' ][0] ) ? $tags['id3v2'][ 'label' ][0] : null ) ); //not available in V1
    $song_array[ 'mtime' ]            = $file_stat[ 'mtime' ];
    $song_array[ 'atime' ]            = $file_stat[ 'atime' ];
    $song_array[ 'filename' ]         = $streeme_path_name;
    $song_array[ 'set_index' ]        = $set_index;
    $song_array[ 'set_total' ]        = $set_total;
    
    unset( $value, $tags, $file_stat, $temp ); //free the RAM used by the temp containters
      
    $media_scanner->add_song( $song_array );
  }
  
  closedir($dp);
}
?>