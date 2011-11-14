<?php
  $tempo_range = range(0.0,500.0,5.0);
  $danceability_range = range(0.0,1.0,0.05);
  $loudness_range = range(-100.0,100.0, 5.0);
  /*
  $mood = array(
                  'any'      => __('Any'),
                  'happy'    => __('Happy'),
                  'angry'    => __('Angry'),
                  'sad'      => __('Sad'),
                  'relaxing' => __('Relaxing'),
                  'excited'  => __('Excited')
                );
  */
  $musickey = array(
                      '12' => __('Any'),
                      '0'  => 'c',
                      '1'  => 'c-sharp',
                      '2'  => 'd',
                      '3'  => 'e-flat',
                      '4'  => 'e',
                      '5'  => 'f',
                      '6'  => 'f-sharp',
                      '7'  => 'g',
                      '8'  => 'a-flat',
                      '9'  => 'a',
                      '10' => 'b-flat',
                      '11' => 'b'
                    );
  $musicmode = array(
                      '2' => __('Any'),
                      '0' => 'minor',
                      '1' => 'major',
                    );
  $hotness_range = range(0.0,1.0,0.05);
  $energy_range = range(0.0,1.0,0.05);

  /**
   * Generates a select from a given array or range
   *
   * @param field_name str: the name of the select
   * @param range      arr: the range or array for the option value
   * @return           str: html select with range options
   */
  function generate_range($field_name, $range, $default = null)
  {
    $options = null;
    foreach($range as $key => $value)
    {
      if(is_float($value))
      {
        $value = number_format($value, 2);
        $key = $value;
      }
      if($default == $value)
      {
        $options .= sprintf('<option value="%s" selected="selected">%s</option>', $key, $value);
      }
      else
      {
        $options .= sprintf('<option value="%s">%s</option>', $key, $value);
      }
    }
    return sprintf('<select name="%s", id="%s">%s</select>', $field_name, $field_name, $options);
  }
?>
<div class="formtitle"><?php echo __( 'Echonest Properties' ) ?></div>
<div class="horizontalrule"></div>
<form onsubmit="return false">
  <label for="temposelector"><?php echo __('Tempo (BPM):') ?></label>
  <?php echo __('Min:') . generate_range('tempo_min', $tempo_range) ?> <?php echo __('to')?> <?php echo __('Max:') . generate_range('tempo_max', $tempo_range, 500) ?>
  <br/><br/>
  <label for="danceabilityselector"><?php echo __('Danceability:') ?></label>
  <?php echo __('Min:') . generate_range('danceability_min', $danceability_range) ?> <?php echo __('to')?> <?php echo __('Max:') . generate_range('danceability_max', $danceability_range, 1.0) ?>
  <br/><br/>
  <label for="energyselector"><?php echo __('Energy:') ?></label>
  <?php echo __('Min:') . generate_range('energy_min', $energy_range) ?> <?php echo __('to')?> <?php echo __('Max:') . generate_range('energy_max', $energy_range, 1.0) ?>
  <br/><br/>
  <label for="loudnessselector"><?php echo __('Loudness:') ?></label>
  <?php echo __('Min:') . generate_range('loudness_min', $loudness_range, -100) ?> <?php echo __('to')?> <?php echo __('Max:') . generate_range('loudness_max', $loudness_range, 100) ?>
  <br/><br/>
  <label for="hotnessselector"><?php echo __('Hotttnesss:') ?></label>
  <?php echo __('Min:') . generate_range('song_hotttnesss_min', $hotness_range) ?> <?php echo __('to')?> <?php echo __('Max:') . generate_range('song_hotttnesss_max', $hotness_range, 1.0) ?>
  <br/><br/>
  <label for="musickeyselector"><?php echo __('Key:') ?></label>
  <?php echo generate_range('musickey', $musickey) ?> <?php echo generate_range('musicmode', $musicmode) ?>
  <br/><br/>
  <div class="horizontalrule" style="margin-bottom: 20px;"></div>
  <div class="echonestsubmit"/>
    <button id="echonestsearchbutton"><?php echo __('Search') ?></button>
  </div>
</form>