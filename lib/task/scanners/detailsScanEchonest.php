<?php
/**
 * detailsScanEchonest
 *
 * This scanner scans individual song tracks for details from
 * Echonest, a service for retrieving text metdata about music from
 *
 * @package streeme
 * @author Richard Hoar
 */
class detailsScanEchonest
{
  protected $echonest, $catalog, $songTable;
  
  public function __construct(
    StreemeEchonestConsumer $echonest,
    EchonestCatalog $catalog,
    SongTable $songTable
  )
  {
    $this->echonest = $echonest;
    $this->catalog = $catalog;
    $this->songTable = $songTable;
  }
  
  /**
   * Create a new catalog
   *
   * @param catalog_name str: the name of the catalog
   * @return             obj: SimpleXMLElement containing the API response
   */
  public function create($catalog_name)
  {
    return $this->catalog->create($catalog_name);
  }
  
  /**
   * Update the catalog with your library information
   *
   * @param catalog_name str: the name of the catalog
   * @return             obj: SimpleXMLElement containing the update
   */
  public function update($catalog_name)
  {
    $filename = sfConfig::get('sf_cache_dir').'/echonestCatalogUpdate.json';
    $entries = array();
    foreach( $this->songTable->getEchonestList() as $value)
    {
      $entries[] = $this->catalog->getUpdateWrapper('update',array(
        'artist_name' => $value['artist_name'],
        'release'     => $value['album_name'],
        'song_name'   => $value['name'],
        'track_number'=> (int) $value['tracknumber'],
      ));
    }
    if($this->catalog->writeCatalogEntries($entries, $filename))
    {
      $catalog_id = $this->catalog->getIdByName($catalog_name);
      return $this->catalog->update($catalog_id, $filename);
    }
    else
    {
      return simplexml_load_string('<result><item>Could Not Write Catalog Entries</item><ticket></ticket></result');
    }
  }
  
  /**
   * Create a new catalog
   *
   * @param catalog_name str: the ticket id for this catalog
   * @return             obj: SimpleXMLElement containing the API response
   */
  public function status($ticket_id)
  {
    return $this->catalog->status($ticket_id);
  }
  
  /**
   * Create a ticket for polling
   *
   * @param catalog_name str: the catalog name to save this ticket id for
   * @param ticket_id    str: an echonest ticket id to track
   * @return             bol: true if successful
   */
  public function setTicket($catalog_name, $ticket_id)
  {
    $name = StreemeUtil::slugify($catalog_name);
    $filename = sfConfig::get('sf_cache_dir').'/echonest' . $name . 'Ticket.txt';
    return $this->catalog->writeCatalogUpdateTicket($ticket_id, $filename);
  }
  
  /**
   * Get the ticket used for polling this catalog
   *
   * @param catalog_name str: the catalog name
   * @return             str: the ticket id or null
   */
  public function getTicket($catalog_name)
  {
    $name = StreemeUtil::slugify($catalog_name);
    $filename = sfConfig::get('sf_cache_dir').'/echonest' . $name . 'Ticket.txt';
    if($result = $this->catalog->readCatalogUpdateTicket($filename))
    {
      return $result;
    }
    else
    {
      return null;
    }
  }
  
  /**
   * Write the xml response to the cache before parsing
   *
   * @param catalog_name str: catalog name
   * @param xml          str: the raw xml file to write
   * @return    bol: true on success
   */
  public function writeResponse($xml, $catalog_name)
  {
    $name = StreemeUtil::slugify($catalog_name);
    $filename = sfConfig::get('sf_cache_dir').'/echonest' . $name . 'Result.xml';
    return file_put_contents($filename, $xml);
  }
  
  /**
   * read the xml response form the cache
   *
   * @param catalog_name str: catalog name
   * @return             str: xml
   */
  public function getCatalogFilename($catalog_name)
  {
    $name = StreemeUtil::slugify($catalog_name);
    return sfConfig::get('sf_cache_dir').'/echonest' . $name . 'Result.xml';
  }
  
  /**
   * Read the data from the foreign catalog database
   *
   * @param catalog_name str: the catalog name
   * @return             str: the ticket id or null
   */
  public function read($catalog_name)
  {
    $catalog_id = $this->catalog->getIdByName($catalog_name);
    return $this->catalog->read($catalog_id);
  }
  
  /**
   * Delete a catalog and/or its cache from echonest
   *
   * @param catalog_name  str: the catalog name to delete
   * @param include_cache bol: also delete the cache from your local mysql db
   */
  public function delete($catalog_name, $include_cache=false)
  {
    $catalog_id = $this->catalog->getIdByName($catalog_name);
    return $this->catalog->delete($catalog_id);
  }
}