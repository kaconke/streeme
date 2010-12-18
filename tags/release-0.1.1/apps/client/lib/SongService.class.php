<?php
#
# Data Access Object for Song Service - uses native mysql
#

Class SongService extends StreemePDODatabase
{
  /**
  * Gets a list of songs and metadata 
  * Todo: convert to Doctrine Tables and cleanup the conditional soup
  * @param $args array
  *                    int offset 
  *                    int limit
  *                    str order {asc|desc}
  *                    str search {keyword(s)}
  * @return      mysql associative array of artist names and ids for specified args
  */
  public function get_song_list( $args = array() )
  {
    //list defaults 
    $settings = array( 
                         'offset'         => '0',
                         'limit'          => 50,
                         'order'          => 'desc',
                         'get_found_rows' => true,
                         'search'         => null,
                         'artist_id'      => null,
                         'album_id'       => null,
                         'song_id'        => null,
                         'genre_id'       => null,
                         'playlist_id'    => null,
                         'sortcolumn'     => 0,
                         'sortdirection'  => 'desc',
                         'random'         => false,
                         'by_alpha'       => null,
                         'by_number'      => null,
                      );
  
    //import user settings
    foreach ( $args as $k => $v )
    {
       $settings[ $k ] = $v;
    }
  
    //check for special search syntax
    $components = explode ( ' ', $settings[ 'search' ] );
    foreach( $components as $k=>$v )
    {
       //if playlistid: is set, change to a playlist songlist
       if ( stristr( $v, 'playlistid:' ) )
       {
         $match = explode( ':', $v );
         if ( is_array( $match ) )
         {
            $settings[ 'playlist_id' ] = $match[1]; 
            unset( $components[ $k ] ); 
         }
       }
    
       //if artistid: is set, add artistid to the where clause
       if ( stristr( $v, 'artistid:' ) )
       {
         $match = explode( ':', $v );
         if ( is_array( $match ) )
         {
            $settings[ 'artist_id' ] = $match[1]; 
            unset( $components[ $k ] ); 
         }
       }
    
       //if albumid: is set, add albumid to the where clause
       if ( stristr( $v, 'albumid:' ) )
       {
         $match = explode( ':', $v );
         if ( is_array( $match ) )
         {
            $settings[ 'album_id' ] = $match[1]; 
            unset( $components[ $k ] ); 
         }
       }
    
       //if genreid: is set, add genreid to the where clause
       if ( stristr( $v, 'genreid:' ) )
       {
         $match = explode( ':', $v );
         if ( is_array( $match ) )
         {
            $settings[ 'genre_id' ] = $match[1]; 
            unset( $components[ $k ] ); 
         }
       } 
        
       //if by_alpha: is set, add an alpha LIKE to the where clause
       if ( stristr( $v, 'by_alpha:' ) )
       {
         $match = explode( ':', $v );
         if ( is_array( $match ) )
         {
            if ( $match[1] != "#" )
            {
               $settings[ 'by_alpha' ] = $match[1]; 
               unset( $components[ $k ] ); 
            }
            else
            {
               $settings[ 'by_number' ] = $match[1]; 
               unset( $components[ $k ] );
            }
         }
       } 
  
       //if shuffle: is set, add genreid to the where clause
       if ( stristr( $v, 'shuffle:' ) )
       {
         $match = explode( ':', $v );
         if ( is_array( $match ) )
         {
            $settings[ 'random' ] = true; 
            unset( $components[ $k ] ); 
         }
       } 
  
    }
    $settings[ 'search' ] = join( ' ', $components );
  
    if ( $settings['random'] )
    {
       $settings[ 'sortcolumn' ] = 8;
    }
  
    //this array contains the decoded sort information
    $order_by = ( $settings['sortdirection'] == 'asc') ? ' ASC ' : ' DESC ';
    $column_sql = array( 
                        0 => ' song.id ' . $order_by,
                        1 => ' song.name ' . $order_by,
                        2 => ' album.name ' . $order_by . ', song.tracknumber ASC ', 
                        3 => ' artist.name ' . $order_by . ', album.name DESC, song.tracknumber ASC ',
                        4 => ' song.mtime ' . $order_by .  ', album.name DESC, song.tracknumber ASC ',
                        5 => ' song.yearpublished ' . $order_by . ', album.name DESC, song.tracknumber ASC ',
                        6 => ' song.length ' . $order_by, 
                        7 => ' song.tracknumber ' . $order_by,
                        8 => ' RAND() '
                     );
    $order_by_string = $column_sql[ (int) $settings[ 'sortcolumn' ] ];
    
    $parameters = array();
  
    $query  = 'SELECT SQL_CALC_FOUND_ROWS ';
    $query .= ' song.unique_id, song.name, album.name as album_name, artist.name as artist_name, FROM_UNIXTIME( song.mtime, "%Y-%m-%d" ) as date_modified, song.yearpublished, song.length, song.tracknumber ';
    $query .= 'FROM ';
    if( !is_null( $settings['playlist_id'] ) )
    {
      $query .= ' playlist_files, ';
    } 
    $query .= ' song ';
    $query .= 'LEFT JOIN ';
    $query .= ' artist ';
    $query .= 'ON song.artist_id = artist.id '; 
    $query .= 'LEFT JOIN ';
    $query .= ' album ';
    $query .= 'ON song.album_id = album.id '; 
    $query .= 'LEFT JOIN ';
    $query .= ' genre ';
    $query .= 'ON song.genre_id = genre.id '; 
    $query .= 'WHERE 1 ';
    if( !is_null( $settings['playlist_id'] ) )
    {
      $query .= ' AND playlist_files.playlist_id = :playlist_id ';
      $query .= ' AND playlist_files.filename = song.filename ';
      $parameters[] = array( 'playlist_id', $settings[ 'playlist_id' ] );
    }
    if ( !is_null(  $settings[ 'song_id' ] ) )
    {
      $query .= ' AND song.id = :song_id ';
      $parameters[] = array( 'song_id', $settings[ 'song_id' ] );
    } 
    if ( !is_null(  $settings[ 'album_id' ] ) )
    {
      $query .= ' AND song.album_id = :album_id ';
      $parameters[] = array( 'album_id', $settings[ 'album_id' ] );
    } 
    if ( !is_null(  $settings[ 'artist_id' ] ) )
    {
      $query .= ' AND song.artist_id = :artist_id ';
      $parameters[] = array( 'artist_id', $settings[ 'artist_id' ] );
    } 
    if ( !is_null(  $settings[ 'genre_id' ] ) )
    {
      $query .= ' AND song.genre_id = :genre_id ';
      $parameters[] = array( 'genre_id', $settings[ 'genre_id' ] );
    } 
    if ( !is_null(  $settings[ 'by_alpha' ] ) )
    {
      $query .= ' AND song.name LIKE :by_alpha ';
      $parameters[] = array( 'by_alpha', $settings[ 'by_alpha' ] . '%' );
    } 
    if ( !is_null(  $settings[ 'by_number' ] ) )
    {
      $query .= ' AND ( song.name LIKE "0%" ';
      $query .= ' OR song.name LIKE "1%" ';
      $query .= ' OR song.name LIKE "2%" ';
      $query .= ' OR song.name LIKE "3%" ';
      $query .= ' OR song.name LIKE "4%" ';
      $query .= ' OR song.name LIKE "5%" ';
      $query .= ' OR song.name LIKE "6%" ';
      $query .= ' OR song.name LIKE "7%" ';
      $query .= ' OR song.name LIKE "8%" ';
      $query .= ' OR song.name LIKE "9%" ) ';
    } 
    if ( !is_null(  $settings[ 'search' ] ) && ( !empty( $settings[ 'search' ] ) || $settings[ 'search' ] === '0'  ) )
    {
      $query .= ' AND ( song.name LIKE :search OR album.name LIKE :search OR artist.name LIKE :search ) ';
      $parameters[] = array( 'search', '%' . join('%', explode(' ', $settings[ 'search' ] ) ) . '%' );
    } 
    $query .= 'ORDER BY ';
    $query .= $order_by_string;
    
    $extras = array(
                     'limit'  => $settings[ 'limit' ],
                     'offset' => $settings[ 'offset' ],
                     'get_found_rows' => $settings[ 'get_found_rows' ],
                     'fetch'  => 'all'
                   );
    if ( $settings['disable_limiting'] ) $extras['disable_limiting'] = true;
    $result = $this->select($query, $parameters, $extras, $settings, get_class( $this ) . '/'. __FUNCTION__ );
     
    return $result;
  }
  
  /**
  * Gets the file info of a song by its database key
  * @param song_id  int: database key for the song
  * @return         str: itunes style filename
  *                 int: file m time
  *                 int: filesize
  */
  public function get_fileinfo_by_id( $song_id )
  {
    $parameters = array();
    
    $query  = 'SELECT ';
    $query .= ' song.filename, song.mtime, song.filesize, song.bitrate, song.accurate_length ';
    $query .= 'FROM ';
    $query .= ' song ';
    $query .= 'WHERE ';
    $query .= ' song.id = :song_id ';
    
    $parameters[] = array( 'song_id', $song_id, 'int' );
    
    $extras       = array(
                            'limit' => 1,
                            'fetch' => 'row'
                         );
    $settings     = array();
    
    $result = $this->select($query, $parameters, $extras, $settings, get_class( $this ) . '/'. __FUNCTION__ );
    
    if( $result ) 
    {
      return $result[0];
    }
    else
    {
      return false;
    }
  }
  
  /**
  * Gets the total library metrics for this user
  * @param song_id  int: database key for the song
  * @return         array song_count: int count all songs in library
  *                       total_time: str total time of all songs
  */
  public function get_library_metrics()
  {
    $parameters = array();
    
    $query  = 'SELECT ';
    $query .= ' distinct count(*) as song_count, ( SELECT SUM(s.length) from song as s ) as total_time ';
    $query .= 'FROM ';
    $query .= ' song ';
    
    $extras       = array(
                            'limit' => 1,
                            'fetch' => 'row'
                         );
    $settings     = array();
    
    $result = $this->select($query, $parameters, $extras, $settings, get_class( $this ) . '/'. __FUNCTION__ );
    
    return $result;
  }
}
?>