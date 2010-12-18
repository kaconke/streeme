<?php
#
# Gets and displays a list of genres in a select input
#
?>
<div class="browse" id="ctbrowse<?php echo strtolower( $title ); ?>">
  <div class="label">Browse <?php echo ucwords( $title ); ?></div>
  <div class="listcontainer_noscroll">
    <select name="genreselector" id="genreselector">
      <option value="none"><?php echo __( '-- Choose a Genre --' ) ?></option>
      <option value="0"><?php echo __( 'Uncatergorized' ) ?></option>
      <?php
      foreach( $list as $row )
      {
        echo '<option value="' . $row[ 'id' ] . '">' . __( $row[ 'name' ] ) . '</option>';
      } 
      ?>
    </select>
  </div>
</div>