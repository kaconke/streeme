<?php
#
# Data Access Object for Library Scanning
# If the Database is not yet initialized, please see the DAOLibraryinit library for a setup script
#
  
class MediaScan extends StreemePDODatabase
{
  protected 
       $last_scan_id,
       $skipped_songs,
       $added_songs,
       $total_songs,
       $added_artists,
       $added_albums,
       $added_genres,
       $removed_songs,
       $removed_albums,
       $removed_artists,
       $removed_genres;
      
  /**
  * initialize the library scan by setting a new last_scan_id for the session
  */
  public function __construct()
  {
    parent::__construct();
    
    $this->total_songs = 0;
    $this->skipped_songs = 0;
    $this->added_songs = 0;
    $this->added_albums = 0;
    $this->added_artists = 0;
    $this->added_genres = 0;   
    $this->removed_songs = 0;
    $this->removed_albums = 0;
    $this->removed_artists = 0;
    $this->removed_genres = 0;
    
    $this->initialize_scan();
  }
  
  /**
  * clean up files that have moved or no longer exist in the database and summarize library changes for the user
  */
  public function __destruct()
  {
    $this->finalize_scan();
    $summary = $this->summarize();
  
    echo $summary;      
  }
  
  /**
  * initializes a scan session - insert a new entry into the scan log  
  * sets the class variable last_scan_id, which is used to syncronize 
  * the song records and speed up future scans 
  */
  private function initialize_scan()
  {
    $parameters = array();
    
    $query  = 'INSERT INTO ';
    $query .= ' scan ';
    $query .= ' SET ';
    $query .= ' scan_time = NOW(), ';
    $query .= ' scan_type = "library" ';
    
    $extras = array();
  
    $result = $this->insert( $query, $parameters, $extras, get_class( $this ) . '/'. __FUNCTION__ );
    
    $this->last_scan_id = $result;
  }
  
  
  /**
  *  return the current last_scan_id in the scanning sequence
  *  @return        int:last_scan_id
  */
  public function get_last_scan_id()
  {
    return $this->last_scan_id;
  }
  
  /**
  * Check if the file we're about to add is already in the database and return true if it's scanned
  * this function will also increment the last scan id
  * @param $filename   str itunes style filename
  * @param $mtime      int time modified unix timestamp
  * $return         bool: if is scanned = true|false
  */
  public function is_scanned( $filename, $mtime )
  {
    if ( empty( $this->last_scan_id ) ) return false; 
  
    $this->total_songs++;       
  
    $parameters = array();
    
    $query  = 'UPDATE ';
    $query .= ' song ';
    $query .= 'SET ';
    $query .= ' last_scan_id = :last_scan_id ';
    $query .= 'WHERE ';
    $query .= ' filename = :filename ';
    $query .= ' AND mtime = :mtime ';
  
    $extras     = array();
  
    $parameters[] = array( 'last_scan_id', $this->last_scan_id, 'int' );
    $parameters[] = array( 'filename', $filename );
    $parameters[] = array( 'mtime', $mtime );
  
    $result = $this->update( $query, $parameters, $extras, get_class( $this ) . '/'. __FUNCTION__ );
  
    if ( $this->get_affected_row_count() )
    {
      $this->skipped_songs++;
      return true;
    }
  
    return false;
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
    //add artist
    $parameters = array();
    $extras     = array();
  
    $query  = 'INSERT IGNORE INTO ';
    $query .= ' artist ';
    $query .= ' SET ';
    $query .= ' name = :artist_name ';
    
    $parameters[] = array( 'artist_name', $song_array['artist_name'] );
  
    $artist_insert_id = $this->insert( $query, $parameters, $extras, get_class( $this ) . '/'. __FUNCTION__ );
  
    if ( $this->get_affected_row_count() )
    {
       $this->added_artists++;
    }
    
    //add album
    $parameters = array();
    $extras     = array();
  
    $query  = 'INSERT IGNORE INTO ';
    $query .= ' album ';
    $query .= ' SET ';
    $query .= ' name = :album_name ';
    
    $parameters[] = array( 'album_name', $song_array['album_name'] );
  
    $album_insert_id = $this->insert( $query, $parameters, $extras, get_class( $this ) . '/'. __FUNCTION__ );
  
    if ( $this->get_affected_row_count() )
    {
       $this->added_albums++;
    }
  
    //add genre
    $parameters = array();
    $extras     = array();
  
    $query  = 'INSERT IGNORE INTO ';
    $query .= ' genre ';
    $query .= ' SET ';
    $query .= ' name = :genre_name ';
    
    $parameters[] = array( 'genre_name', $song_array['genre_name'] );
  
    $genre_insert_id = $this->insert( $query, $parameters, $extras, get_class( $this ) . '/'. __FUNCTION__ );
  
    if ( $this->get_affected_row_count() )
    {
       $this->added_genres++;
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
  
  /**
  * Finalizes the scan - removes old artists/albums/songs that the user has removed 
  * from the library
  */
  private function finalize_scan()
  {
    //disable database I/U/D auto limiting for the following operations
    $extras     = array( 'disable_limiting' => true );
  
    //delete songs not found in the last scan 
    $parameters = array();
  
   	$query  = 'DELETE FROM ';
    $query .= ' song ';
    $query .= 'WHERE ';
    $query .= ' last_scan_id != :last_scan_id';
  
    $parameters[] = array( 'last_scan_id', $this->last_scan_id, 'int' );
  
    $result = $this->delete( $query, $parameters, $extras,  get_class( $this ) . '/'. __FUNCTION__ );
  
  
    if ( $this->get_affected_row_count() )
    {
       $this->removed_songs = $this->removed_songs + $this->get_affected_row_count();
    }
  
  
    //delete albums no longer associated with songs
    $parameters = array();
  
    $query  = 'DELETE FROM ';
    $query .= ' album ';
    $query .= 'WHERE ';
    $query .= ' id NOT IN (SELECT s.album_id FROM song AS s)';
  
    $result = $this->delete( $query, $parameters, $extras,  get_class( $this ) . '/'. __FUNCTION__ );
  
  
    if ( $this->get_affected_row_count() )
    {
       $this->removed_albums = $this->removed_albums + $this->get_affected_row_count();
    }
  
    //delete artists no longer associated with the songs
    $parameters = array();
  
    $query = 'DELETE FROM ';
    $query .= ' artist ';
    $query .= 'WHERE ';
    $query .= ' id NOT IN (SELECT s.artist_id FROM song AS s)';
  
    $result = $this->delete( $query, $parameters, $extras,  get_class( $this ) . '/'. __FUNCTION__ ); 
  
    if ( $this->get_affected_row_count() )
    {
       $this->removed_artists = $this->removed_artists + $this->get_affected_row_count();
    }
  
    //delete custom genres no longer associated with the songs
    $parameters = array();
  
    $query = 'DELETE FROM ';
    $query .= ' genre ';
    $query .= 'WHERE ';
    $query .= ' id NOT IN (SELECT s.genre_id FROM song AS s)';
    $query .= ' AND id > 125 ';
  
    $result = $this->delete( $query, $parameters, $extras,  get_class( $this ) . '/'. __FUNCTION__ ); 
  
    if ( $this->get_affected_row_count() )
    {
       $this->removed_genres = $this->removed_genres + $this->get_affected_row_count();
    }
  
    //becuase of the I/U/D activity in this script, we'll need to remove table overhead/
    //defragment at the end of the scan
    $parameters = array();
  
    $query = 'OPTIMIZE TABLE `album`, `artist`, `song`, `genre`';
  
    $result = $this->update( $query, $parameters, $extras,  get_class( $this ) . '/'. __FUNCTION__ );
    
  }
  
  /**
  * Summarizes Details from the Scan
  * @return         str summary log string
  */
  public function summarize()
  {
    $string  = null;
    $string .= 'Total Songs Scanned: ' . (string) $this->total_songs . " \r\n";
    $string .= 'Songs Skipped: ' . (string) $this->skipped_songs . " \r\n";
    $string .= 'Songs Added: ' . (string) $this->added_songs . " \r\n";
    $string .= 'Albums Added: ' . (string) $this->added_albums . " \r\n";
    $string .= 'Artists Added: ' . (string) $this->added_artists . " \r\n";
    $string .= 'Custom Genres Added: ' . (string) $this->added_genres . " \r\n";
    $string .= 'Songs Removed: ' . (string) $this->removed_songs . " \r\n";
    $string .= 'Albums Removed: ' . (string) $this->removed_albums . " \r\n";
    $string .= 'Artists Removed: ' . (string) $this->removed_artists . " \r\n";      
    $string .= 'Custom Genres Removed: ' . (string) $this->removed_genres . " \r\n";   
  
    return $string; 
  }
  
}
?>