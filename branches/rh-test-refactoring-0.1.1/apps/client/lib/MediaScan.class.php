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
  /*
    
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
  */
}
