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
  public $last_scan_id = 0;
  
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
    $scan = Doctrine_Core::getTable( 'Scan' );
    $scan->scan_time = date( 'Y-m-d h:i:s' );
    $scan->scan_type = 'library';
    $scan->save();
    $this->last_scan_id = $scan->getId();
    $scan->free();
  }
  
  /**
   *  return the current last_scan_id in the scanning sequence
   *  @return        int:last_scan_id
   */
  public function get_last_scan_id()
  {
    return $this->last_scan_id;
  }
}
