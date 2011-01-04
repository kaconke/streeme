<?php
/**
 * Media Scanner
 *
 * This class manages the library scanning process for a users music library. It will scan and update/add songs
 * and cleanup old
 *
 * @package    streeme
 * @subpackage media scanner
 * @author     Richard Hoar
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
  
class MediaScan
{
  /**
   * int: Stores the last scan id for this scanning session
   */
  protected $last_scan_id = false;
  
  /**
   * int: This is a counter for songs skipped during the scan
   */
  public $skipped_songs= 0;
  
  /**
   * int: This is a counter for songs added during the scan
   */
  public $added_songs= 0;
  
  /**
   * int: This is a counter for total songs scanned
   */
  public $total_songs= 0;
  
  /**
   * int: count artists added during the scan
   */
  public $added_artists= 0;
  
  /**
   * int: count albums added during the scan
   */
  public $added_albums= 0;
  
  /**
   * int: count custom genres added during the scan
   */
  public $added_genres= 0;
  
  /**
   * int: count songs removed during the scan
   */
  public $removed_songs= 0;
  
  /**
   * int: count albums removed during the scan
   */
  public $removed_albums= 0;
  
  /**
   * int: count artists removed druing the scan
   */
  public $removed_artists= 0;
  
  /**
   * int: count genres removed during the scan
   */
  public $removed_genres= 0;
      
  /**
   * initialize the library scan by setting a new last_scan_id for the session
   */
  public function __construct()
  {
    $this->scan_id = Doctrine_Core::getTable('Scan')->addScan( 'library' );
  }
  
  /**
   *  return the current last_scan_id in the scanning sequence
   *  @return        int:last_scan_id or false
   */
  public function get_last_scan_id()
  {
    return $this->scan_id;
  }
  
  /**
   * Check if the file we're about to add is already in the database and return true if it's scanned
   *
   * @param $filename  str itunes style filename
   * @param $mtime     int time modified unix timestamp
   * $return           bool: if is scanned = true|false
   */
  public function is_scanned( $filename, $mtime )
  {
    //increment the total song count
    $this->total_songs++;

    //have we seen this song before?
    $song = Doctrine_Core::getTable( 'Song' )->findByFilenameAndMtime( $filename, $mtime );
    
    if( is_object( $song ) )
    {
      $song->last_scan_id = $this->scan_id;
      $song->save();
      $song->free();
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
    $artist_id = Doctrine_Core::getTable('Artist')->addArtist( $song_array['artist_name'] );
    $album_id = Doctrine_Core::getTable('Album')->addAlbum( $song_array['album_name'] );
    $genre_id = Doctrine_Core::getTable('Genre')->addGenre( $song_array['genre_name'] );
    $song_id = Doctrine_Core::getTable('Song')->addSong( $artist_id, $album_id, $genre_id, $this->scan_id, $song_array );
    $this->added_songs++;
   
    return $song_id;
  }
  
  /**
   * Finalize Scan - remove all out of date/missing songs and associations
   */
  public function finalize_scan()
  {
    $this->removed_songs = Doctrine_Core::getTable('Song')->finalizeScan( $this->last_scan_id );
    $this->removed_artists = Doctrine_Core::getTable('Artist')->finalizeScan();
    $this->removed_albums = Doctrine_Core::getTable('Album')->finalizeScan();
    $this->removed_genres = Doctrine_Core::getTable('Genre')->finalizeScan();
  }
}
