<?php
/**
 * the catalog model for Streeme's remote echonest database communication for API V4
 *
 * This class is responsible for communication with catalog functions of the remote
 * echonest database. It requires an echonest API key and DOES share song data with outside
 * data systems. Please review the Echonest's terms carefully before uploading your data.
 *
 * @package streeme
 * @author Richard Hoar
 * @depends Echonest Developer API Version 4
 * @see http://developer.echonest.com/docs/v4/catalog.html
 */
class EchonestCatalog
{
  /**
   * Constructor
   *
   * @param echonest obj: a StreemeEchonestConsumer Instance
   */
  public function __construct(StreemeEchonestConsumer $echonest)
  {
    $this->echonest = $echonest;
  }
  
  /**
   * Create a new catalog on the remote API database
   *
   * @param catalog_name str: the name of the new catalog
   * @return             obj: SimpleXMLElement containing the API response
   */
  public function create($catalog_name)
  {
    $this->echonest->setParameter('name', $catalog_name);
    $this->echonest->setParameter('type','song');
    $this->echonest->setHeader('Content-Type', 'multipart/form-data');
    
    return $this->echonest->query('catalog', 'create', 'POST')->fetchResult();
  }
  
  /**
   * Get a catalog id by playlist name on this api key
   *
   * @param catalog_name str: the catalog name
   * @return             obj: the echonest response object
   */
  public function getProfileInfo($catalog_name)
  {
    $this->echonest->setParameter('name', $catalog_name);
    
    return $this->echonest->query('catalog', 'profile')->fetchResult();
  }
  
  /**
   * Get a catalog id by name
   *
   * @param catalog_name str: the catalog name
   * @return             str: an echonest catalog id
   */
  public function getIdByName($catalog_name)
  {
    if(isset($this->getProfileInfo($catalog_name)->catalog->{0}->id))
    {
      return (string) $this->getProfileInfo($catalog_name)->catalog->{0}->id;
    }
    else
    {
      return null;
    }
  }
  
  /**
   * Get updates on an upload job. Large libaries may take some time.
   *
   * @param ticket_id str: the ticket id of the job to get status on
   * @return          obj: the echonest response object
   */
  public function status($ticket_id)
  {
    $this->echonest->setParameter('ticket', $ticket_id);
    
    return $this->echonest->query('catalog','status')->fetchResult();
  }
  
  /**
   * Synchronize a catalog file with the remot API
   *
   * @param catalog_id     str: the catalog id of the item to update
   * @param json_file_path str: the full path to the json object to be uploaded
   * @return               obj: the echonest response object
   */
  public function update($catalog_id, $json_file_path)
  {
    $this->echonest->setParameter('id', $catalog_id);
    $this->echonest->setParameter('data-type', 'json');
    $this->echonest->setParameter('data', $json_file_path);
    
    return $this->echonest->query('catalog', 'update', 'POST')->fetchResult();
  }
  
  /**
   * Read the contents of a catalog
   *
   * @param catalog_id str: the catalog id of the catalog to read
   * @return           obj: the echonest response object
   */
  public function read($catalog_id)
  {
    $this->echonest->setParameter('id', $catalog_id);
    $this->echonest->setParameter('bucket', 'audio_summary');
    $this->echonest->setParameter('bucket', 'song_hotttnesss');
    $this->echonest->setParameter('bucket', 'artist_hotttnesss');
    $this->echonest->setParameter('bucket', 'artist_location');
    $this->echonest->setParameter('bucket', 'artist_familiarity');
    
    return $this->echonest->query('catalog', 'read')->fetchRawResult();
  }
  
  /**
   * Permanently delete a catalog
   *
   * @param catalog_id str: the catalog id of the item to delete
   * @return           obj: the echonest response object
   */
  public function delete($catalog_id)
  {
    $this->echonest->setParameter('id', $catalog_id);
    $this->echonest->setHeader('Content-Type', 'multipart/form-data');
    
    return $this->echonest->query('catalog', 'delete', 'POST')->fetchResult();
  }
  
  /**
   * Create catalog update wrapper
   *
   * @param action     str: the action - one of ("delete","update","play","skip". Default is "update")
   * @param parameters arr: a list of parameters to send to the server as a key value array
   * @return           str: a json representation of an array.
   * @see              http://developer.echonest.com/docs/v4/catalog.html#update
   */
  public function getUpdateWrapper($action, $parameters)
  {
    $parameter_wrapper = new stdClass();
    foreach($parameters as $key=>$value)
    {
      $parameter_wrapper->$key = $value;
    }
    
    $wrapper = new stdClass();
    $wrapper->action = $action;
    $wrapper->item = $parameter_wrapper;
    
    return json_encode($wrapper);
  }
  
  /**
   * Create a json file consisting of multiple wrapper json objects
   *
   * @param entries  arr: an array of updatewrappers
   * @param filename str: the filename to write
   * @return         bol: true on success
   */
  public function writeCatalogEntries($entries, $filename)
  {
    return file_put_contents($filename, sprintf('[%s]', join(',', $entries)));
  }
  
  /**
   * Write catalog update ticket information to a file for future
   * update polling
   *
   * @param ticket_id    str: echonest ticket id
   * @param filename str: the path to the ticket text file
   */
  public function writeCatalogUpdateTicket($ticket_id, $filename)
  {
    return file_put_contents($filename, $ticket_id);
  }
  
  /**
   * Read catalog update ticket information to a file for future
   * update polling
   *
   * @param filename str: the path to the ticket text file
   */
  public function readCatalogUpdateTicket($filename)
  {
    return file_get_contents($filename);
  }
}