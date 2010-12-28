<?php
/**
 * Media Scanner
 *
 * This class manages the library scanning process for a users music library. It will scan and update/add songs
 * and cleanup old
 *
 * @package    streeme
 * @subpackage model
 * @author     Richard Hoar
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
  
class MediaScan
{
  /**
   * int: Stores the last scan id for this scanning session
   */
  public $last_scan_id = false;
  
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
    // Begin a new scan session by writing an entry to the scan table
    // capture the scan id from this new record
    $scan = new Scan;
    $scan->scan_time = date( 'Y-m-d h:i:s' );
    $scan->scan_type = 'library';
    $scan->save();
    $id = $scan->getId();
    if( $id )
    {
      $this->last_scan_id = $id;
    }
    else
    {
      throw new Exception( 'Could not get a new scan id - please check your database is set up correctly' );
    }
    $scan->free();
  }
  
  /**
   *  return the current last_scan_id in the scanning sequence
   *  @return        int:last_scan_id or false
   */
  public function get_last_scan_id()
  {
    return $this->last_scan_id;
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
      $song->last_scan_id = $this->last_scan_id;
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
  * @param $song_array array
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
  *  @return      true on successful completion | false on failure
  */
  public function add_song( $song_array )
  {
    $artist_id = Doctrine_Core::getTable('Artist')->addArtist( $song_array['artist_name'] );
    $album_id = Doctrine_Core::getTable('Album')->addAlbum( $song_array['album_name'] );
    $genre_id = Doctrine_Core::getTable('Genre')->addGenre( $song_array['genre_name'] );
    $song_id = Doctrine_Core::getTable('Genre')->addSong( $artist_id, $album_id, $genre_id, $song_array );
    
    if ( $this->get_affected_row_count() )
    {
       $this->added_songs++;
    }
    
    
    //add song
    $parameters = array();
    $extras     = array();
  
    //catch any input problems before the query tries to execute
    if( !isset( $song_array[ 'id3_genre_id' ] ) || empty( $song_array[ 'id3_genre_id' ] ) )
    {
      $song_array[ 'id3_genre_id' ] = 0;
    }
  
    $query  = 'INSERT INTO ';
    $query .= ' song ';
    $query .= ' ( unique_id, artist_id, album_id, genre_id, last_scan_id, name, length, accurate_length, filesize, bitrate, yearpublished, tracknumber, label, mtime, atime, filename ) ';
    $query .= 'VALUES ( ';
    $query .= ' "' . sha1( uniqid( '', true ) . mt_rand( 1, 99999999 ) ) . '", '; //adds a unique id for each song
    $query .= ' COALESCE( (SELECT ar.id FROM artist AS ar WHERE ar.name = :artist_name ), 0), ';
    $query .= ' COALESCE( (SELECT al.id FROM album AS al WHERE al.name = :album_name ), 0 ), ';
    $query .= ' COALESCE( (SELECT gn.id FROM genre AS gn WHERE gn.name = :genre_name ), :id3_genre_id ), ';
    $query .= ' COALESCE( :last_scan_id, 0 ), ';
    $query .= ' :song_name, ';
    $query .= ' :song_length, ';
    $query .= ' COALESCE( :accurate_length , 0 ), ';
    $query .= ' COALESCE( :size, 0), ';
    $query .= ' COALESCE( :bitrate, 0 ), ';
    $query .= ' COALESCE( :year, 0 ), ';
    $query .= ' COALESCE( :track_number, 0 ), ';
    $query .= ' :label, ';
    $query .= ' COALESCE( :mtime, 0 ), ';
    $query .= ' COALESCE( :atime, 0 ), ';
    $query .= ' :filename ';
    $query .= ' ) ';
    $query .= 'ON DUPLICATE KEY UPDATE ';
    $query .= ' artist_id = VALUES( artist_id ), ';
    $query .= ' album_id = VALUES( album_id ), ';
    $query .= ' genre_id = VALUES( genre_id ), ';
    $query .= ' last_scan_id = VALUES( last_scan_id ),';
    $query .= ' name = VALUES( name ), ';
    $query .= ' length = VALUES( length ), ';
    $query .= ' filesize = VALUES( filesize ), ';
    $query .= ' bitrate = VALUES( bitrate ), ';
    $query .= ' yearpublished = VALUES( yearpublished ), ';
    $query .= ' tracknumber = VALUES( tracknumber ) , ';
    $query .= ' label = VALUES( label ), ';
    $query .= ' mtime = VALUES( mtime ), ';
    $query .= ' atime = VALUES( atime ), ';
    $query .= ' filename = VALUES( filename ) ';
    
    $parameters[] = array( 'artist_name', $song_array['artist_name'] );
    $parameters[] = array( 'album_name', $song_array['album_name'] );
    $parameters[] = array( 'song_name', $song_array['song_name'] );
    $parameters[] = array( 'genre_name', $song_array['genre_name'] );
    $parameters[] = array( 'id3_genre_id', $song_array['id3_genre_id'], 'int' );
    $parameters[] = array( 'last_scan_id', $this->last_scan_id, 'int' );
    $parameters[] = array( 'song_length', $song_array['song_length'], 'int' );
    $parameters[] = array( 'accurate_length', $song_array['accurate_length'], 'int' );
    $parameters[] = array( 'size', $song_array['size'], 'int' );
    $parameters[] = array( 'bitrate', $song_array['bitrate'], 'int' );
    $parameters[] = array( 'year', $song_array['year'], 'int' );
    $parameters[] = array( 'track_number', $song_array['track_number'], 'int' );
    $parameters[] = array( 'label', $song_array['label'] );
    $parameters[] = array( 'mtime', $song_array['mtime'], 'int' );
    $parameters[] = array( 'atime', $song_array['atime'], 'int' );
    $parameters[] = array( 'filename', $song_array['filename'] );
                           
    $song_insert_id = $this->insert( $query, $parameters, $extras, get_class( $this ) . '/'. __FUNCTION__ );

    if ( $this->get_affected_row_count() )
    {
       $this->added_songs++;
    }
  
    return $song_insert_id;
  }
}
