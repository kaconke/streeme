<?php
/**
 * Playlist Scanner
 *
 * This class manages the library scanning process for a user's playlists.
 *
 * @package    streeme
 * @subpackage playlist scanner
 * @author     Richard Hoar
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */

Class PlaylistScan
{
  protected $scan_id = 0;
  protected $total_playlists = 0;
  protected $skipped_playlists = 0;
  protected $added_playlists = 0;
  protected $removed_playlists = 0;
  protected $service_name = null;
    
  /**
   * initialize the library scan by setting a new scan_id for the session
   * @param source str: add a service name for each scanner
   */
  public function __construct( $service_name )
  {
    //Since this class services a batch script, stop Doctrine from leaving objects in memory
    Doctrine_Manager::connection()->setAttribute(Doctrine_Core::ATTR_AUTO_FREE_QUERY_OBJECTS, true );
    $this->service_name = $service_name;
    $this->scan_id = Doctrine_Core::getTable('Scan')->addScan('playlist');
  }
  
  /**
   *  return the current scan_id in the scanning sequence
   *  @return        int:scan_id
   */
  public function get_last_scan_id()
  {
    return $this->scan_id;
  }

  /**
   * return the source type
   */
  public function get_source()
  {
    return $this->source;
  }
  
  /**
   * Check if the file we're about to add is already in the database and return true if it's scanned
   *
   * @param $filename  str itunes style filename
   * @param $mtime     int time modified unix timestamp
   * $return           bool: if is scanned = true|false
   */
  public function is_scanned( $service_name, $playlist_name, $playlist_unique_id )
  {
    //increment the total playlist count for this service
    $this->total_playlists++;
    
    //have we seen this playlist before?
    $song = Doctrine_Core::getTable( 'Playlist' )->updateScanId( $service_name, $playlist_name, $playlist_unique_id );
    if( $song > 0 )
    {
      $this->skipped_playlists++;
      return true;
    }
    else
    {
      return false;
    }
  }
  
  /**
   * Populate the song list from an array
   * Parameter order is not important
   * @param $song_array array - contents
   *   artist_name     str name of the artist
   *   album_name      str name of the album
   *   genre_name      str genre name
   *   id3_genre_id    int id3 V1 or winamp extension ID eg. 0 - 125
   *   song_name       str name of the song
   *   song_length     str mins:secs
   *   accurate_length int milliseconds
   *   size            int file size
   *   bitrate         int bitrate
   *   year            int year
   *   track_number    int track number on the album
   *   label           str label
   *   mtime           int time modified unix timestamp
   *   atime           int time added to itunes unix timestamp
   *   filename        str itunes style filename
   *  @return          int: new song id
   */
  public function add_song( $song_array )
  {
    $artist_name = ( $song_array['artist_name'] ) ? $song_array['artist_name'] : 'Unknown Artist';
    $artist_id = Doctrine_Core::getTable('Artist')->addArtist( $artist_name );
    if( !empty( $artist_id ) )
    {
      $this->added_artists[ $artist_id ] = 1;
    }
    $album_name = ( $song_array['album_name'] ) ? $song_array['album_name'] : 'Unknown Album';
    $album_id = Doctrine_Core::getTable('Album')->addAlbum( $album_name );
    if( !empty( $album_id ) )
    {
      $this->added_albums[ $album_id ] = 1;
    }
    $song_id = Doctrine_Core::getTable('Song')->addSong( $artist_id, $album_id, $this->scan_id, $song_array );
    $this->added_songs++;
    $genre_ids = Doctrine_Core::getTable('SongGenres')->addSongGenres($song_id, $song_array['genre_name']);
    unset( $artist_name, $artist_id, $album_name, $album_id, $genre_name, $genre_ids, $song_array );
    
    return $song_id;
  }
  
  /**
   * Clean up songs that did not check in during the scan - remove their associated
   * relations to genre, albums, artists as well
   * @return           int total records removed in the cleanup
   */
  public function finalize_scan()
  {
    $this->removed_playlists = Doctrine_Core::getTable('Song')->finalizeScan( $this->scan_id, $this->service_name );
    
    return $this->removed_songs + $this->removed_artists + $this->removed_albums;
  }
  
  /**
   * Summarize changes made to a user's library at the very end of a scan
   * @return           str an summary of actions taken during scanning
   */
  public function get_summary()
  {
    $string  = null;
    $string .= 'Total Songs Scanned: ' . (string) $this->total_songs . " \r\n";
    $string .= 'Songs Skipped: ' . (string) $this->skipped_songs . " \r\n";
    $string .= 'Songs Added: ' . (string) $this->added_songs . " \r\n";
    $string .= 'Albums Added: ' . (string) count( $this->added_albums ) . " \r\n";
    $string .= 'Artists Added: ' . (string) count( $this->added_artists ) . " \r\n";
    $string .= 'Songs Removed: ' . (string) $this->removed_songs . " \r\n";
    $string .= 'Albums Removed: ' . (string) $this->removed_albums . " \r\n";
    $string .= 'Artists Removed: ' . (string) $this->removed_artists . " \r\n";
  
    return $string;
  }
}