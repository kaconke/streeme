<?php
/**
 * JVM Based Solr/Lucene search index provider connector. 
 * 
 * @author Richard Hoar
 * @package Streeme
 * @depends sfSolrPlugin
 */
class StreemeIndexerSolr extends StreemeIndexerBase
{
  protected $lucene, $service;
  
  public function __construct()
  {
    $this->lucene = sfLucene::getInstance('index', 'en');
    if(!$this->lucene->getSearchService()->ping())
    {
      throw new Exception('Solr index server not loaded - please use >symfony lucene:service client start');
    }
    $this->service = $this->lucene->getSearchService();
  }
  
  /**
   * Attempt to raise the service 
   * 
   * @return bol: true on success 
   */
  public function bootstrapService()
  {
    return true;
  }
    
  /**
   * Add a document to the index
   * 
   * @param unique_id   str: the song's unique id
   * @param song_name   str: song name
   * @param artist_name str: artist name 
   * @param artist_name str: album name 
   * @param genre_name  str: genre name
   * @param tags        str: any other tags/words to describe the track
   * @return            bol: true on success
   */
  public function doAddDocument($unique_id, $song_name, $artist_name, $album_name, $genre_name, $tags=null)
  {
    $boost = false;
    $doc = new sfLuceneDocument();

    $doc->setField('song_name', $song_name, $boost);
    $doc->setField('artist_name', $artist_name, $boost);
    $doc->setField('album_name', $album_name, $boost);
    $doc->setField('genre_name', $genre_name, $boost);
    $doc->setField('tags', $tags, $boost);   
    $doc->setField('sfl_guid', $unique_id);
    
    $this->service->addDocument($doc);
    unset($doc);
    
    return true;
  }

  /**
   * Pre transaction script
   * 
   * @return          bol: true on success
   */
  public function preTransaction()
  {
    //non transactional batch entry 
    return true;
  }
  
  /**
   * Post transaction script 
   * 
   * @return          bol: true on success
   */
  public function postTransaction()
  {
    //non transactional batch entry 
    return true;
  }
  
  /**
   * Abort transaction 
   * 
   * @return          bol: true on success
   */
  public function rollbackTransaction()
  {
    //non transactional batch entry 
    return true;
  }
    
  /**
   * destroy session / clean up stray data commits
   * 
   * @return          bol: true on success
   */
  public function flush()
  {
    $this->service->commit();
    $this->service->optimize();
    
    return true;
  }
  
  /**
   * get unique key list by keywords
   * 
   * @param keywords    str: a user's search terms
   * @param limit       int: limit the resultset to the number specified
   * @param idFieldName str: the canonical id fieldname for the song's unique id
   * @return            arr: a list of matching keys found by fulltext search
   */
  public function getKeys($keywords, $limit = 100, $idFieldName = 'sfl_guid')
  {
    $user_search = preg_match("/[\*|\!|\+|\-|\&\&|\|\||\(|\)|\[|\]|\^|\~|\*|\?|\:|\\\"|\\\]/", $keywords, $void_matches);
    $user_search = (substr($keywords, -1)===' ') ? true : false;
      
    $criteria = new sfLuceneCriteria();
    if(strpos($keywords, ' '))
    {
      $criteria->addPhrase($keywords);
    }
    else
    {
      $criteria->add(sprintf('%s%s', $keywords, (($user_search) ? '' : '*')), sfLuceneCriteria::TYPE_AND, true);      
    }
    $criteria->setParam('fl', $idFieldName);  
    $criteria->setLimit($limit);   
    $keys = array();
    foreach($this->lucene->friendlyFind($criteria) as $result)
    {
      $tmp = $result->getResult()->getField($idFieldName);
      $keys[] = $tmp['value'];
    }
    
    unset($criteria, $result);
    
    return $keys;
  }
}