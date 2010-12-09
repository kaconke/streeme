<?php
#
# Data Access Object for Artwork Scan
# If the Database is not yet initialized, please see the DAOLibraryinit library for a setup script
#

Class ArtworkScan extends StreemePDODatabase
{
  protected 
     $scan_id,
     $total_artwork,
     $skipped_artwork,
     $added_artwork,
     $source;
    
  /**
  * initialize the library scan by setting a new scan_id for the session
  */
  public function __construct( $source )
  {
    parent::__construct();
    
    $this->source = $source;
    
    $this->total_artwork = 0;
    $this->skipped_artwork = 0;
    $this->added_artwork = 0;
    
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
  * sets the class variable scan_id, which is used to syncronize 
  * the artwork records and speed up future scans 
  */
  private function initialize_scan()
  {
    $parameters = array();
    
    $query  = 'INSERT INTO ';
    $query .= ' scan ';
    $query .= ' SET ';
    $query .= ' scan_time = NOW(), ';
    $query .= ' scan_type = "artwork" ';
    
    $extras = array();
    
    $result = $this->insert( $query, $parameters, $extras, get_class( $this ) . '/'. __FUNCTION__ );
    
    $this->scan_id = $result;
  }
  
  
  /**
  *  return the current scan_id in the scanning sequence
  *  @return        int:scan_id
  */
  public function get_scan_id()
  {
    return $this->scan_id;
  }
  
  /**
  *  return a pair of artist and album for scanning - skip previously flagged scans
  *  @return        array: artist and song names
  */
  public function get_unscanned_artwork_list()
  {
    $parameters = array();
    
    $query  = 'SELECT DISTINCT ';
    $query .= ' album.id as album_id, album.name as album_name, artist.name as artist_name, song.filename as song_filename ';
    $query .= 'FROM ';
    $query .= ' song ';
    $query .= 'LEFT JOIN '; 
    $query .= ' album ON song.album_id = album.id ';
    $query .= 'LEFT JOIN '; 
    $query .= ' artist ON song.artist_id = artist.id ';
    $query .= 'WHERE ';
    $query .= ' album.id IS NOT NULL ';
    switch ( $this->source )
    {
      case 'amazon':
        $query .= ' AND album.amazon_flagged != 1 ';
        break;
      
      case 'meta':
        $query .= ' AND album.meta_flagged != 1 ';
        break;
        
      case 'folders':
        $query .= ' AND album.folders_flagged != 1 ';
        break;
        
      case 'service':
        $query .= ' AND album.service_flagged != 1 ';
        break;
    }
    $query .= ' AND album.has_art != 1 '; 
    $query .= ' ORDER BY album.id ASC ';
    
    $extras = array( 
                'fetch' => 'all',
                'disable_limiting' => true
               );
    $settings = array();
    $result = $this->select( $query, $parameters, $extras, $settings, get_class( $this ) . '/'. __FUNCTION__ );
    return $result;
  }
  
  /**
  * flag an album as skipped for album art - the source images were not available
  * @param album_id  int: the album's database ID
  */
  public function flag_as_skipped( $album_id )
  {
    if ( empty( $this->scan_id ) ) return false; 
    
    $parameters = array();
    
    $query  = 'UPDATE ';
    $query .= ' album ';
    $query .= 'SET ';
    switch ( $this->source )
    {
      case 'amazon':
        $query .= ' amazon_flagged = 1, ';
        break;
      
      case 'meta':
        $query .= ' meta_flagged = 1, ';
        break;
        
      case 'folders':
        $query .= ' folders_flagged = 1, ';
        break;
      
      case 'service':
        $query .= ' service_flagged = 1, ';
        break;
    }
    $query .= ' album.scan_id = :scan_id ';
    $query .= 'WHERE ';
    $query .= ' album.id = :album_id ';
    
    $extras     = array();
    
    $parameters[] = array( 'scan_id', $this->scan_id, 'int' );
    $parameters[] = array( 'album_id', $album_id );
    
    $result = $this->update( $query, $parameters, $extras, get_class( $this ) . '/'. __FUNCTION__ );
  
    if ( $this->get_affected_row_count() )
    {
      $this->skipped_artwork++;
      return true;
    }
    
    return false;
  }
  
  /**
  * Artwork files were successfully added for this album, so flag an album as having album art to speed up future scans
  * @param album_id  int: the album's database ID
  */
  public function flag_as_added( $album_id )
  {
    if ( empty( $this->scan_id ) ) return false; 
    
    $parameters = array();
    
    $query  = 'UPDATE ';
    $query .= ' album ';
    $query .= 'SET ';
    switch ( $this->source )
    {
      case 'amazon':
        $query .= ' amazon_flagged = 1, ';
        break;
      
      case 'meta':
        $query .= ' meta_flagged = 1, ';
        break;
        
      case 'folders':
        $query .= ' folders_flagged = 1, ';
        break;
      
      case 'service':
        $query .= ' service_flagged = 1, ';
        break;
    }
    $query .= ' has_art = 1, ';
    $query .= ' album.scan_id = :scan_id ';
    $query .= 'WHERE ';
    $query .= ' album.id = :album_id ';
    
    $extras     = array();
    
    $parameters[] = array( 'scan_id', $this->scan_id, 'int' );
    $parameters[] = array( 'album_id', $album_id );
    
    $result = $this->update( $query, $parameters, $extras, get_class( $this ) . '/'. __FUNCTION__ );
    
    if ( $this->get_affected_row_count() )
    {
      $this->added_artwork++;
      return true;
    }
    return false;
  }
  
  /**
  * Finalizes the scan - removes old artists/albums/songs that the user has removed 
  * from the library
  */
  private function finalize_scan()
  {
  //disable database I/U/D auto limiting for the following operations
  $extras     = array( 'disable_limiting' => true );
  
  //becuase of the I/U/D activity in this script, we'll need to remove table overhead/
  //defragment at the end of the scan
  $parameters = array();
  
  $query = 'OPTIMIZE TABLE `album`';
  
  $result = $this->update( $query, $parameters, $extras,  get_class( $this ) . '/'. __FUNCTION__ );      
  }
  
  /**
  * Get the final counts of total albums and the ones that have art for the summary
  * @return array
  */
  private function get_total_art_counts()
  {
    $parameters = array();
    
    $query  = 'SELECT ';
    $query .= ' ( SELECT count(*) from album WHERE 1 ) as total_albums, ';
    $query .= ' ( SELECT count(*) from album WHERE has_art = 1 ) as has_artwork ';
    
    $extras     = array( 
                            'fetch' => 'all',
                            'disable_limiting' => true
                       );
    $settings = array();
    $result = $this->select( $query, $parameters, $extras, $settings, get_class( $this ) . '/'. __FUNCTION__ );
    return $result;
  }
  
  
  /**
  * Summarizes Details from the Scan
  * @return         str summary log string
  */
  public function summarize()
  {
    $totals = $this->get_total_art_counts();
    $string  = null;
    $string .= 'Total Albums: ' . (string) $totals[0]['total_albums'] . " \r\n";
    $string .= 'Total Albums with Art: ' .  (string) $totals[0]['has_artwork'] . ' (' . @( floor ( ( $totals[0]['has_artwork'] / $totals[0]['total_albums'] ) * 100 ) ) . '%)' . "\r\n";
    $string .= 'Artwork Unavailable this Scan: ' . (string) $this->skipped_artwork . " \r\n";
    $string .= 'Artwork Added this Scan: ' . (string) $this->added_artwork . " \r\n";
    
    return $string; 
  }
}
?>