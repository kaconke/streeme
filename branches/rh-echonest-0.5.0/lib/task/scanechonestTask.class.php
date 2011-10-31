<?php
require_once(dirname(__FILE__).'/scanners/detailsScanEchonest.php');
require_once(dirname(__FILE__).'/../vendor/cli/cliProgressBar.class.php');

class scanechonestTask extends sfBaseTask
{
  protected function configure()
  {
    $this->addArguments(array(
       new sfCommandArgument('action', sfCommandArgument::REQUIRED, 'none'),
       new sfCommandArgument('catalog_name', sfCommandArgument::OPTIONAL, null),
    ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'client'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
      new sfCommandOption('verbose', null, sfCommandOption::PARAMETER_REQUIRED, 'Show verbose messages from echonest', false),
    ));

    $this->namespace        = '';
    $this->name             = 'scan-echonest';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [scan-echonest|INFO] task does things.
Call it with:

  [php symfony scan-echonest|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    // initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
    $connection = $databaseManager->getDatabase($options['connection'])->getConnection();
    $apiKey = sfConfig::get('app_echonest_api_key', false);
    if(!$apiKey)
    {
      throw new Exception('You must declare an API Key for echonest in your app.yml file to continue.');
    }
    if(!$arguments['catalog_name'])
    {
      $arguments['catalog_name'] = sfConfig::get('app_echonest_catalog_name', null);
    }
    
    $echonest = new StreemeEchonestConsumer($apiKey, new sfWebBrowser);
    $verbose = @$options['verbose'];
    
    $catalog = new detailsScanEchonest(
      $echonest,
      new EchonestCatalog($echonest),
      SongTable::getInstance()
    );
    
    switch($arguments['action'])
    {
      case 'create':
        $this->doCreate($catalog->create($arguments['catalog_name']), $arguments['catalog_name'], $verbose);
        break;
      case 'update':
        $response = $this->doUpdate($catalog->update($arguments['catalog_name']), $arguments['catalog_name'], $verbose);
        $ticket_id = (string) $response->ticket;
        if(strlen($ticket_id) > 0)
        {
          $catalog->setTicket($arguments['catalog_name'], $ticket_id);
          $this->doGetProgress($catalog, $ticket_id, $arguments['catalog_name'], $verbose);
          $this->doDownload($catalog->read($arguments['catalog_name']), $arguments['catalog_name'], $verbose, $catalog);
        }
        break;
      case 'progress':
        $ticket_id = $catalog->getTicket($arguments['catalog_name']);
        $this->doGetProgress($catalog, $ticket_id, $arguments['catalog_name'], $verbose);
        break;
      case 'download':
        $this->doDownload($catalog->read($arguments['catalog_name']), $arguments['catalog_name'], $verbose, $catalog);
        break;
      case 'sync':
        $this->doSyncCache($catalog->getCatalogFilename($arguments['catalog_name']), $arguments['catalog_name'], $verbose);
        break;
      case 'delete':
        $this->doDelete($catalog->delete($arguments['catalog_name']),$arguments['catalog_name'], $verbose);
        break;
    }
  }
  
  /**
   * Process the results of the create task
   *
   * @param response     obj: a simplexml object of the response from echonest
   * @param catalog_name str: the name of the catalog
   * @param verbose      bol: output the results from echonest and catalog name to the screen
   */
  protected function doCreate(SimpleXMLElement $response, $catalog_name, $verbose = false)
  {
    if($verbose)
    {
      var_dump($response, $catalog_name);
    }
    if((string) $response->status->code === '5' || (string) $response->status->code === '0')
    {
      echo sprintf("Catalog: \"%s\" is ready for use.\r\n", (string) $catalog_name);
    }
    else
    {
      throw new Exception(sprintf("Could not create catalog: \"%s\". Please check your API key and make sure your catalog name is alphanumeric only", (string) $catalog_name));
    }
  }
  
  /**
   * Process the results of an update task
   *
   * @param response     obj: a simplexml object of the response from echonest
   * @param catalog_name str: the name of the catalog
   * @param verbose      bol: output the results from echonest and catalog name to the screen
   */
  protected function doUpdate(SimpleXMLElement $response, $catalog_name, $verbose = false)
  {
    if($verbose)
    {
      var_dump($response, $catalog_name);
    }
    if((string) $response->status->code === '0')
    {
      echo sprintf("Catalog: \"%s\" has been updated.\r\n", (string) $catalog_name);
      return $response;
    }
    else
    {
      throw new Exception(sprintf("Catalog was not updated: \"%s\". Please use verbose mode for more info.", (string) $catalog_name));
    }
    
    return;
  }
  
  /**
   * Process the results of a progress report
   *
   * @param response     obj: a simplexml object of the response from echonest
   * @param catalog_name str: the name of the catalog
   * @param verbose      bol: output the results from echonest and catalog name to the screen
   */
  protected function doGetProgress($catalog, $ticket_id, $catalog_name, $verbose = false)
  {
    while( $response = $catalog->status($ticket_id) )
    {
      if($verbose)
      {
        var_dump($response, $catalog_name);
        break;
      }
         
      cliProgressBar::show_status((int) $response->items_updated, (int) $response->total_items, 30);
      
      if((int)$response->percent_complete == 100)
      {
        break;
      }
      
      sleep(5);
    }
    
    return;
  }
  
  /**
   * Download the read response to an xml file in cache, so we can close the connection immediately
   * after the xml file is delivered.
   *
   * @param xml          str: the xml result from the read call
   * @param catalog_name str: the name of the catalog
   * @param verbose      bol: output the results from echonest and catalog name to the screen
   */
  protected function doDownload($xml, $catalog_name, $verbose = false, detailsScanEchonest $catalog)
  {
    if($verbose)
    {
      var_dump($xml, $catalog_name);
    }
    if(strpos($xml, '<code>0</code>'))
    {
      if($catalog->writeResponse($xml, $catalog_name))
      {
        echo "Successfully downloaded the results from echonest...\r\n";
        return;
      }
      else
      {
        throw new Exception(sprintf("Could not write catalog xml cache: \"%s\". Please use verbose mode for more info.", (string) $catalog_name));
      }
    }
    else
    {
      throw new Exception(sprintf("Could not read catalog: \"%s\". Please use verbose mode for more info.", (string) $catalog_name));
    }
  }
  
  /**
   * Sync the local database with the foreign service
   *
   * @param xml_filename str: the resulting xml filefrom the download task
   * @param catalog_name str: the name of the catalog
   * @param verbose      bol: output the results from echonest and catalog name to the screen
   */
  protected function doSyncCache($xml_filename, $catalog_name, $verbose = false)
  {
    if($verbose)
    {
      var_dump($xml_filename, $catalog_name);
    }
    if(is_readable($xml_filename))
    {
      //sync with local cache database
      $parser = new StreemeEchonestCatalogParser($xml_filename);
      $song = SongTable::getInstance();
      $echonestProperties = EchonestPropertiesTable::getInstance();
      while( $echonestData = $parser->getDetails() )
      {
        //$echonestProperties->setDetails($song_id, $echonestData);
      }
    }
    else
    {
      throw new Exception(sprintf("Could not read catalog: \"%s\". Please use verbose mode for more info.", (string) $catalog_name));
    }
    return;
  }
  
  
  /**
   * Process the results of a progress report
   *
   * @param response     obj: a simplexml object of the response from echonest
   * @param catalog_name str: the name of the catalog
   * @param verbose      bol: output the results from echonest and catalog name to the screen
   */
  protected function doDelete(SimpleXMLElement $response, $catalog_name, $verbose = false)
  {
    if($verbose)
    {
      var_dump($response, $catalog_name);
    }
    if((string) $response->status->code === '0')
    {
      echo sprintf("Catalog: \"%s\" has been deleted.\r\n", (string) $catalog_name);
      
      //Delete from the local cache database
    }
    elseif((string) $response->status->code === '4')
    {
      echo sprintf("Catalog: \"%s\" no longer exists - have you already deleted it?\r\n", (string) $catalog_name);
    }
    else
    {
      throw new Exception(sprintf("Catalog was not deleted: \"%s\". Please use verbose mode for more info.", (string) $catalog_name));
    }
  }
}
