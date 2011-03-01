<?php

/**
 * ArtistTable
 *
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class ArtistTable extends Doctrine_Table
{
  /**
   * Returns an instance of this class.
   *
   * @return object ArtistTable
   */
  public static function getInstance()
  {
      return Doctrine_Core::getTable('Artist');
  }
    
    
  /**
   * Add an Artist to the database or get its key if it exists
   * @param name str: the name of the artist to add
   * @return     int: the primary key added or found
   */
  public function addArtist( $name )
  {
    //is this name already in the collection?
    $q = Doctrine_Query::create()
      ->select( 'a.id' )
      ->from( 'Artist a' )
      ->where( 'a.name = ?', $name);
    $result = $q->fetchOne();
     
    if ( is_object( $result ) && $result->id > 0 )
    {
      return $result->id;
    }
    else
    {
      $item = new Artist;
      $item->name = $name;
      $item->save();
      return $item->getId();
    }
  }
    
  /**
   * Fetch the artist list
   * @param alpha str: the alphabetical grouping
   * @return      array: of all artist entries
   */
  public function getList( $alpha = 'all' )
  {
    //get the song from the database
    $q = Doctrine_Query::create()
      ->select( 'a.id, a.name' )
      ->from( 'Artist a' );
    if( $alpha !== 'all' )
    {
      $q->where( 'upper( a.name ) LIKE ?', strtoupper( substr( $alpha, 0, 1 ) ) . '%' );
    }
    $q->orderBy( 'a.name ASC' );
    return $q->fetchArray();
  }
  
  /**
   * Remove album records with no song relation
   *
   * @param last_scan_id int: this should be the id of the latest library scan
   * @return             array: number of records removed
   */
  public function finalizeScan()
  {
    $q = Doctrine_Query::create()
      ->delete('Artist a')
      ->where('a.id NOT IN (SELECT s.artist_id FROM song AS s)')
      ->execute();
    return $q;
  }
}