<?php
#
# Data Access Object for Song Service - uses native mysql
#

Class PlaylistService extends StreemePDODatabase
{
  /**
  * Add a song/album/artist to the playlist 
  * @param playlist_id int: playlist id  
  * @param id          str: song uniqueid | album/artist id 
  * @param type        str: song|artist|album 
  */
  public function add_to_playlist( $playlist_id, $id, $type )
  {
    $filename_list = array();
    
    $filename_list = $this->get_file_list( $id, $type );
        
    if( count( $filename_list ) > 0 )
    {
      foreach( $filename_list as $row )
      {
        $parameters = array();
        
        $query  = 'INSERT INTO ';
        $query .= ' playlist_files ';
        $query .= 'SET ';
        $query .= ' playlist_id = :playlist_id, ';
        $query .= ' filename = :filename ';
        
        $parameters[] = array( 'playlist_id', $playlist_id, 'int' );
        $parameters[] = array( 'filename', $row[ 'filename' ] );
        
        $extras = array();
      
        $result = $this->insert( $query, $parameters, $extras, get_class( $this ) . '/'. __FUNCTION__ );
      }
    }
  }
  
  /**
  * delete a song from the playlist
  * @param playlist_id int: playlist id
  * @param id          str: song_uniqueid
  */
  public function delete_from_playlist( $playlist_id, $id )
  {
    $filename_list = array();
    
    $filename_list = $this->get_file_list( $id, 'song' );
        
    if( count( $filename_list ) > 0 )
    {
      foreach( $filename_list as $row )
      {
        $parameters = array();
        
        $query  = 'DELETE FROM ';
        $query .= ' playlist_files ';
        $query .= 'WHERE ';
        $query .= ' playlist_id = :playlist_id ';
        $query .= ' AND filename = :filename ';  
        $query .= 'LIMIT 1 ';    
        
        $parameters[] = array( 'playlist_id', $playlist_id, 'int' );
        $parameters[] = array( 'filename', $row[ 'filename' ] );
        
        $extras = array();
              
        $result = $this->insert( $query, $parameters, $extras, get_class( $this ) . '/'. __FUNCTION__ );
      }
    }
  }
  
  /**
  * Add a playlist 
  * @param name        str: playlist name 
  */
  public function add_playlist( $name )
  {
    $parameters = array();
        
    $query  = 'INSERT INTO ';
    $query .= ' playlist ';
    $query .= 'SET ';
    $query .= ' name = :name ';
    
    $parameters[] = array( 'name', $name );
    
    $extras = array();
          
    $result = $this->insert( $query, $parameters, $extras, get_class( $this ) . '/'. __FUNCTION__ );
  }
  
  /**
  * Delete a Playlist 
  * @param playlist_id int: playlist id 
  */
  public function delete_playlist( $playlist_id )
  {
    $query  = 'DELETE FROM ';
    $query .= ' playlist ';
    $query .= 'WHERE ';
    $query .= ' id = :playlist_id ';
    $query .= 'LIMIT 1 ';    
    
    $parameters[] = array( 'playlist_id', $playlist_id, 'int' );
    
    $extras = array();
    
    $result = $this->insert( $query, $parameters, $extras, get_class( $this ) . '/'. __FUNCTION__ );
  }
  
  /**
  * Get file list 
  * @param id   str: song unique id | album and artist id
  * @param type str: song | album | artists
  * @return     array: list of filenames for each song 
  */
  private function get_file_list( $id, $type )
  {
    $parameters = array();
    
    $query  = 'SELECT ';
    $query .= ' song.filename ';
    $query .= 'FROM ';
    $query .= ' song ';
    $query .= 'WHERE 1 ';
    switch( $type ) 
    {
      case 'artist':
        $query .= 'AND artist_id = :unique_id';
        $parameters[] = array( 'unique_id', $id );
        break;
      case 'album':
        $query .= 'AND album_id = :id';
        $parameters[] = array( 'id', $id, 'int' );
        break;
      case 'song':
        $query .= 'AND unique_id = :id';
        $parameters[] = array( 'id', $id, 'int' );
        break;     
    }
    
    $extras       = array( 
                            'limit' => 150,
                            'offset' => 0,
                            'fetch' => 'all' 
                         );
    
    $settings     = array();
    
    $result = $this->select($query, $parameters, $extras, $settings, get_class( $this ) . '/'. __FUNCTION__ );
    
    return $result;
  }
}