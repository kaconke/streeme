<?php

class scheduledscanTask extends sfBaseTask
{
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('type', null, sfCommandOption::PARAMETER_REQUIRED, 'The type of scan to run - see opts'),
    ));

    $this->namespace        = '';
    $this->name             = 'schedule-scan';
    $this->briefDescription = 'Permits scheduled scans for use with crons and windows task scheduler';
    $this->detailedDescription = <<<EOF
The [schedule-scan|INFO] will perform a scan of your music library and art
on a schedule defined by crontab or windows task scheduler. You can edit the
order and components of the scan in your apps/client/config/app.yml

Call it with:

  [php symfony schedule-scan|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    //bootstrap "client" context
    require_once( dirname(__FILE__) . '/../../config/ProjectConfiguration.class.php' );
    $configuration = ProjectConfiguration::getApplicationConfiguration( 'client', 'dev', true );
    $context = sfContext::createInstance( $configuration );
    
    $scan_list = sfConfig::get('app_msp_media_scan_plan');
    $root_dir = sfConfig::get('sf_root_dir');
    if( is_array( $scan_list ) && count( $scan_list ) > 0 )
    {
      foreach( $scan_list as $scan_item )
      {
        passthru( $root_dir . '/symfony ' . $scan_item );
        echo "\r\n\r\n";
      }
    }
  }
}
