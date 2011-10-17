<?php

class scanechonestTask extends sfBaseTask
{
  protected function configure()
  {
    $this->addArguments(array(
       new sfCommandArgument('action', sfCommandArgument::REQUIRED, 'none'),
       new sfCommandArgument('catalog_name', sfCommandArgument::REQUIRED, sfConfig::get('app_echonest_catalog_name', null)),
    ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'client'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
      // add your own options here
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
    $echonest = null;
    // add your code here
    switch($arguments['action'])
    {
      case 'create':
        break;
      case 'update':
        break;
      case 'delete':
        break;
    }
  }
}
