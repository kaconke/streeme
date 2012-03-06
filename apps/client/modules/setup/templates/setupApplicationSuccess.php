<?php
#
# Configure the Application app.yml
#
slot( 'title', __( 'Streeme: Application Configuration' ) );
slot( 'description', __( 'Please configure your application preferences' ) );
slot( 'metaHTML', '<meta name="viewport" content="width=device-width, initial-scale=0.4666, maximum-scale=1.0, user-scalable=no">' );
use_stylesheet( '/css/setup/stylesheet.css' );
?>
<script src="/js/jquery-1.4.2.min.js" type="text/javascript"></script>
<div class="container">
  <div class="languagecontainer"><?php include_partial('player/languagechooser'); ?></div>
  <div class="formarea clearfix">
    <div class="infosection">
      <h1><?php echo __('Streeme Settings') ?></h1>
      <p>
        <?php echo __('Use this tool to configure your application settings for Streeme. If you\'re unsure about a setting, please check the <a href="http://code.google.com/p/streeme/wiki/InstallingStreeme#Configure_Streeme">Streeme wiki</a> for more details.') ?>
        <?php echo __('The following form will create or edit the following file, which you may edit at any time using a text editor or by re-running this setup script.') ?>
        <span class="code"><?php echo sprintf( '%s%sapps%sclient%sconfig%sapp.yml', sfConfig::get('sf_root_dir'), DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR) ?></span>
      </p>
    </div>
    <form method="post" action="<?php echo url_for('@setup_application') ?>" id="dbSetupForm" onsubmit="return confirm('<?php echo __('Warning: this action will replace your current app.yml file including an custom settings. Proceed with setup?') ?>') ? true : false;">
      <div class="formsection">
        <ul class="form">
          <?php include_partial('setup/applicationSetupForm', array('form'=>$form)); ?>
        </ul>
      </div>
      <div class="buttonsection">
        <ul class="buttons">
          <li class="submitbutton"><input type="submit" value="<?php echo __('Finish Setup') ?>"/></li>
          <li><a>&nbsp;</a></li>
        </ul>
      </div>
    </form>
  </div>
</div>