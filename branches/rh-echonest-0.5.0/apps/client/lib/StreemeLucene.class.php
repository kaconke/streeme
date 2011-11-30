<?php 
/**
 * Lucene tools for keyword searching within music catalogs
 * 
 * @author Richard Hoar
 * @package Streeme
 * @see integration notes here: http://www.symfony-project.org/jobeet/1_4/Doctrine/en/17
 */
class StreemeLucene
{
  protected $lucene;

  /**
   * Constructor - bootstrap Zend Framework autoloader 
   * 
   * @param auto_start bol: true if constructor should bootstrap the indexer
   * @see config/Projectconfiguration.class.php 
   */
  public function __construct($auto_start = true)
  {
    ProjectConfiguration::registerZend();
    $this->lucene = $this->getIndex();
  }
    
  /**
   * Get the lucene index object to begin queries
   * 
   * @return  obj: The lucene search object 
   */
  public function getIndex()
  {
    if (file_exists($index_dir = $this->getLuceneIndexFile()))
    {
      return Zend_Search_Lucene::open($index_dir);
    }
   
    return Zend_Search_Lucene::create($index_dir);
  }
  
  /**
   * Update the index with new data when a song is added to the library
   * 
   * @param song_array arr: the song array
   */
  public function updateIndex($song_array)
  {
    if(isset($song_array) && count($song_array) === 0) return;
    
    $id = sha1( serialize($song_array) );
    
    // remove existing entries
    foreach ($this->lucene->find(sprintf('uid:%s', $id)) as $hit)
    {
      $this->lucene->delete($hit->id);
    }
   
    $doc = new Zend_Search_Lucene_Document();
   
    // add song unique id - generally a hash of the entire song array
    $doc->addField(Zend_Search_Lucene_Field::Keyword('uid', $id));
   
    // add all indexable fields
    $doc->addField(Zend_Search_Lucene_Field::UnStored('artist_name', $song_array['artist_name'], 'utf-8'));
    $doc->addField(Zend_Search_Lucene_Field::UnStored('album_name', $song_array['album_name'], 'utf-8'));
    $doc->addField(Zend_Search_Lucene_Field::UnStored('song_name', $song_array['song_name'], 'utf-8'));
    $doc->addField(Zend_Search_Lucene_Field::UnStored('genre_name', $song_array['genre_name'], 'utf-8'));
     
    // add job to the index
    $this->lucene->addDocument($doc);
    $this->lucene->commit();
  }

  /**
   * Get unique IDs based on lucene keyword searches
   * 
   * @param keywords str: A list of keywords to search
   * @return         arr: a list of uniqueids to match against
   */
  public function getSongIds($keywords)
  {
    $uids = array();
    foreach ($this->lucene->find($keywords) as $hit)
    {
      $uids[] = $hit->uid;
    }
       
    return $uids;
  }

  /**
   * Purge all records
   */
  public function deleteAll()
  {
    $this->lucene->deleteAll();
    $this->lucene->commit();
  }
  
  /**
   * Optimize the index
   */
  public function optimize()
  {
    $this->lucene->optimize();
    $this->lucene->commit();
  }
  
  /**
   * Get the filename of the lucene object
   * 
   * @return str: the filename of the object
   */
  public function getLuceneIndexFile()
  {
    return sprintf('%s/%s/%s', sfConfig::get('sf_data_dir'), sfConfig::get('sf_environment'), sfConfig::get('app_lucene_dirname', 'lucene'));
  }
}