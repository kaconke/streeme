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
  protected $updated_playlists = 0;
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
   * @param filename  str itunes style filename
   * @param mtime     int time modified unix timestamp
   * $return           int: playlist_id
   */
  public function is_scanned( $service_name, $playlist_name, $service_unique_id = null )
  {
    //increment the total playlist count for this service
    $this->total_playlists++;
    
    //have we seen this playlist before?
    $playlist_id = Doctrine_Core::getTable( 'Playlist' )->updateScanId( $service_name, $playlist_name, $service_unique_id );
  
    return $playlist_id;
  }
  
  /**
   * Remove and replace all playlist files for a given playlist or add a new playlist
   * from scratch.
   *
   * @param playlist_name  str: playlist name
   * @param playlist_files array: a list of filenames
   * @param playlist_id    int: optional playlist id to update
   * @return               int: playlist_id
   */
  public function add_playlist($playlist_name, $playlist_files, $playlist_id=0)
  {
    if(
           isset($playlist_name)
           &&
           strlen($playlist_name) > 0
           &&
           $playlist_id === 0
           &&
           count($playlist_files) > 0
       )
    {
      $this->added_playlists++;
      $playlist_id = PlaylistTable::getInstance()->addPlaylist($playlist_name);
      PlaylistFilesTable::getInstance()->addFiles($playlist_id, $playlist_files);
    }
    else if(
           isset($playlist_name)
           &&
           strlen($playlist_name) > 0
           &&
           $playlist_id !== 0
           &&
           count($playlist_files) > 0
       )
    {
      $this->updated_playlists++;
      PlaylistFilesTable::getInstance()->deleteAllPlaylistFiles( $playlist_id );
      PlaylistFilesTable::getInstance()->addFiles($playlist_id, $playlist_files);
    }
    else
    {
      $this->skipped_playlists++;
    }
    
    return $playlist_id;
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