<?php
/*
* A PDO wrapper for mySQL and php 5 for batch processing
* This class is only used for media ingest eg. very noisy - it is highly recommended that you 
* use Doctrine for most of your queries - used because of memory consumption errors and lack 
* of a suitable "on duplicate key update" syntax w/ doctrine
*
* @package    streeme
* @author     Richard Hoar
*/
class StreemePDODatabase
{
	private static $connections = array();
  private static $servers;
  private $last_query_affected_row_count = 0;
  private $last_query_found_rows;
  public $results_per_page = 60;

	public function __construct()
  {
    $databaseConf = sfYaml::load( dirname( __FILE__ ) .'/../../../config/databases.yml');
        
    $MYSQL_SERVER[] = array(
                     'MYSQL_USERNAME'   => $databaseConf['all']['doctrine']['param']['username'],
                     'MYSQL_PASSWORD'   => $databaseConf['all']['doctrine']['param']['password'],
                     'MYSQL_PDO_DSN'    => $databaseConf['all']['doctrine']['param']['dsn']
                  );  
    self::$servers = $MYSQL_SERVER;
  }

	/**
	* Secure the correct database connection for this user id data
	* @param token 	the token (eg a user_id or modulus) for the data requested
	* @return 			pdo object
	*/
	final private function get_connection( $token )
	{
		return self::connect( $token );
	}

	/**
	* Connect to the database and retain handle as static connection - Singleton pattern per shard 
	* @param 	the token (eg a user_id or modulus) for the data requested
	* @return          a PDO Mysql resource with config.inc.php settings;
	*/
	public static function connect( $token )
	{	
		$dsn      = self::$servers[ $token ][ 'MYSQL_PDO_DSN' ];
		$username = self::$servers[ $token ][ 'MYSQL_USERNAME' ];
		$password	= self::$servers[ $token ][ 'MYSQL_PASSWORD' ];

		if ( !isset( self::$connections[ $token ] ) || empty( self::$connections[ $token ] ) )
		{
			//create new pdo connection
			try
			{
				self::$connections[ $token ] = new PDO(
																	 $dsn,
																	 $username,
																	 $password,
																	 array( PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8" )
																 );
			}
			catch( Exception $e )
			{
			   FWError::log( get_class(), 'Could not Connect to the Database: ' . $e->getMessage() , 0 , true );
         exit;
      }
		}

		return self::$connections[ $token ];
	}

	
	/**
	* Central select mechanism to get data from the correctly identified shard
	* @param query   string: preparable sql query (anonymous parameters only!)
	* @param params  array:  an array of named replacement parameters for the query
	* @param extras  array:  
	*                fetch:       string: type { row | all } (default: row) 
	*								 offset:		  string: offset index for limit
	*								 limit:         string: limit index for limit
	*								 cache_query: bool: cache query to disk / memory
  *                disable_limiting bool: disable limits for unknown record counts
  * @param settings array: pass settings to this function for cache fingerprinting
  * @param dao_error_id  string: name of the calling class and function
	* @return        array results of select query 
	*/	
	public function select( $query, $params, $extra = array(), $settings = array(), $dao_error_id )
	{
    /*
		* Defaults 
		*/
		$offset           = 0;
		$limit            = $this->results_per_page; 
		$fetch            = 'row';
		$cache_query      = false;
    $disable_limiting = false;
    $get_found_rows   = false;
						
		/*
		* Parse and validate Extras Array
		*/
		if ( isset( $extra[ 'fetch' ] ) && !empty( $extra[ 'fetch' ] ) && !empty( $extra[ 'fetch' ] ) == 'all' ) 
		{
			$fetch = 'all';
		}
		if ( isset( $extra[ 'limit' ] ) && !empty( $extra[ 'limit' ] ) && is_numeric( $extra[ 'limit' ] ) ) 
		{
			$limit = (int) $extra[ 'limit' ];
		}
		if ( isset( $extra[ 'offset' ] ) && !empty( $extra[ 'offset' ] ) && is_numeric( $extra[ 'offset' ] ) ) 
		{
			$offset = (int) $extra[ 'offset' ];
		}
		if ( isset( $extra[ 'cache_query' ] ) && !empty( $extra[ 'cache_query' ] ) && $extra[ 'cache_query' ] == 'true' ) 
		{
			$cache_query = true;
		}
		if ( isset( $extra[ 'disable_limiting' ] ) && !empty( $extra[ 'disable_limiting' ] ) ) 
		{
			$disable_limiting = $extra[ 'disable_limiting' ];
		}
		if ( isset( $extra[ 'get_found_rows' ] ) && !empty( $extra[ 'get_found_rows' ] ) ) 
		{
			$get_found_rows = $extra[ 'get_found_rows' ];
		}	
		
      /*
		* Execute the prepared statement
		*/		
		$dbh = $this->connect( 0 );

		if ( $stmt = $dbh->prepare( $query . ( ( !$disable_limiting ) ? ' LIMIT :dblimit OFFSET :dboffset' : '' ) ) )
		{
			try
			{
				foreach ( $params as $k => $v )
				{
					$stmt->bindParam( $v[0], $v[1], ( (isset( $v[2] ) && $v[2] === "int") ? PDO::PARAM_INT : PDO::PARAM_STR ) );
				}
            if ( !$disable_limiting )
            {
               $stmt->bindParam( ':dblimit', $limit,  PDO::PARAM_INT );
				}
				$stmt->bindParam( ':dboffset', $offset, PDO::PARAM_INT );
				
				if ( $stmt->execute() === true )					
				{
               if ( $get_found_rows )
               {
                  $query = 'SELECT FOUND_ROWS() as found_rows';

                  $parameters = array();
                  $extras = array();
                  $settings = array();

                  $found_rows = $this->select($query, $parameters, $extras, $settings, get_class( $this ) . '/'. __FUNCTION__ );

                  $this->last_query_found_rows = $found_rows[ 'found_rows' ];
               }
				}
				else
				{
					$fail = $stmt->errorInfo();
          FWError::log( get_class(), 'No data selected: ' . $dao_error_id . ' ' . @$fail[2], 0 );
				}	
				
				$count = $stmt->rowCount();
			}
			catch ( Exception $e )
			{
			  FWError::log( get_class(), 'S Preparation execution failed for:' . $dao_error_id . ' extra:' . $e->getMessage() , 0 );
        return false;  
			}

			if ($count > 0 )
			{
				switch ( $fetch )
				{
					case 'all':
					return $stmt->fetchAll( PDO::FETCH_ASSOC );
					break;

					case 'row':
					return $stmt->fetch( PDO::FETCH_ASSOC );
					break;
				}
		    FWError::log( get_class(), 'Invalid fetch parameter supplied: ' . $dao_error_id, 0 );
			  return false; 
			}
			else
			{
        FWError::log( get_class(), 'No rows returned for: ' . $dao_error_id, 0 );
        return false; 
			}
		}
		else
		{
      FWError::log( get_class(), 'S Query preparation failed for: ' . $dao_error_id, 0 );
      return false; 
		}
	}

	/**
	* Central insert mechanism put data into the correctly identified shard
	* @param query   string: preparable sql query (anonymous parameters only!)
	* @param params  array:  an array of named replacement parameters for the query
	* @param extra	 array:  for handling extra functionality. 
	* @return        string: last insert id or false
	*/	
	public function insert(  $query, $params, $extra = array(), $dao_error_id  )
	{
    //Execute the prepared statement		
		$dbh = $this->connect( 0 );
		
		if ( $stmt = $dbh->prepare( $query ) )
		{
			try
			{
				foreach ( $params as $k => $v )
				{
					$stmt->bindParam( $v[0], $v[1], ( (isset( $v[2] ) && $v[2] === "int") ? PDO::PARAM_INT : PDO::PARAM_STR ) );
				}
								
				if ( $stmt->execute() === TRUE )	
				{
               $last_row_id = $dbh->lastInsertId();
               $this->last_query_affected_row_count = $stmt->rowCount();
				}
				else			
				{
					$fail = $stmt->errorInfo();
               
					FWError::log( get_class(), 'SQL Error - No Records Affected: ' . $dao_error_id . ' ' . @$fail[2] , 0 );
          return false; 
				}	
			}
			catch ( Exception $e )
			{
        FWError::log( get_class(), 'I Preparation execution failed for:' . $dao_error_id . ' extra:' . $e->getMessage() , 0 );
        return false;  
			}
			
			if ( $last_row_id > 0 )
			{
				return $last_row_id;
			}
			else
			{
				return false; 
			}
		}
		else
		{
			FWError::log( get_class(), 'I Query preparation failed for: ' . $dao_error_id, 0 );
			return false; 
		}
	}
	
	/**
	* Central update mechanism to change data on the correctly identified shard
	* @param query   string: preparable sql query (anonymous parameters only!)
	* @param params  array:  an array of named replacement parameters for the query
	* @param extras  array:  limit:         string: limit index for limit
   *                        disable_limiting bool: disable limits for unknown record counts
	* @return        string: last touched row 
	*/	
	public function update( $query, $params, $extra = array(), $dao_error_id  )
	{
    /*
		* Defaults to the strictest setting- single record 
		*/
		$limit = 1; 
    $disable_limiting = false;
						
		/*
		* Parse Extras Array
		*/
		if ( isset( $extra[ 'limit' ] ) && !empty( $extra[ 'limit' ] ) && is_numeric( $extra[ 'limit' ] ) ) 
		{
			$limit = (int) $extra[ 'limit' ];
		}
		if ( isset( $extra[ 'disable_limiting' ] ) && !empty( $extra[ 'disable_limiting' ] ) ) 
		{
			$disable_limiting = $extra[ 'disable_limiting' ];
		}

		/*
		* Execute the prepared statement
		*/		
		$dbh = $this->connect( 0 );
		
		if ( $stmt = $dbh->prepare( $query .  ( ( !$disable_limiting ) ? ' LIMIT :dblimit' : '' ) ) )
		{
			try
			{
				foreach ( $params as $k => $v )
				{
					$stmt->bindParam( $v[0], $v[1], ( (isset( $v[2] ) && $v[2] === "int") ? PDO::PARAM_INT : PDO::PARAM_STR ) );
				}
				
            if ( !$disable_limiting )
            {
               $stmt->bindParam( ':dblimit', $limit,  PDO::PARAM_INT );
				}
				
				if ( $stmt->execute() === true )
				{
               $this->last_query_affected_row_count = $stmt->rowCount();
					return true;
				}
				else
				{
					$fail = $stmt->errorInfo();
               
			    FWError::log( get_class(), 'No Records Affected: ' . $dao_error_id . ' ' . @$fail[2] , 0 );
          return false; 
				}	
			}
			catch ( Exception $e )
			{
        FWError::log( get_class(), 'UD Preparation execution failed for:' . $dao_error_id . ' extra:' . $e->getMessage() , 0 );
        return false;  
			}
			
		}
		else
		{
		   FWError::log( get_class(), 'UD Query preparation failed for: ' . $dao_error_id, 0 );
		   return false; 
		}
	}
	
	/*
	* Aliases to the update Method - extensible for the future
	*/
	public function delete( $query, $params, $extra = array(), $dao_error_id  )
	{
		return  $this->update(  $query, $params, $extra, $dao_error_id  );
	}

	/*
	* Aliases to the update Method - extensible for the future
   * passes an extra attribute to disable limiting while the schema dump proceeds
	*/
	public function dump( $query, $params, $extra = array(), $dao_error_id  )
	{
    $extra[ 'disable_limiting' ] = true;
		return  $this->update(  $query, $params, $extra, $dao_error_id  );
	}		

   /*
   * Return the affected row count from the last I/U/D 
   */
   public function get_affected_row_count()
   {
      return $this->last_query_affected_row_count;
   }


   /*
   * Return the found rows from the last select
   */
   public function get_found_rows()
   {
      return $this->last_query_found_rows;
   }

   /*
   * In case an object can't be parameterized, use this escape method 
   */
   public function escape( $string )
   {
      return mysql_escape_string( $string );
   }
		
}

class FWError
{
  /**
  * Adaptation of a logging system mechanism that is now only for error catching => symfony
  * logging system 
  * @param class   str: classname with failure
  * @param message str: throws an exception with this name 
  * @param id      int: unused
  * @paramhalt     bool: stop the app and log a critical error
  */
  public static function log( $class, $message, $id=0, $halt=false )
  {
    if ($halt)
    {
      sfContext::getInstance()->getLogger()->crit( $message );
      exit;
    }
    else
    {
      //echo $message;
      sfContext::getInstance()->getLogger()->info( $message );
    }
  }
}
?>
