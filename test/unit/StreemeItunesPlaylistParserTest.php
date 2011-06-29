<?php
include( dirname(__FILE__) . '/../bootstrap/unit.php' );
include( dirname(__FILE__) . '/../../apps/client/lib/StreemeItunesPlaylistParser.class.php' );

// Initialize the test object
$t = new lime_test( 1, new lime_output_color() );

$t->comment( '->construct()' );
try
{
  $missing_file = new StreemeItunesPlaylistParser( dirname(__FILE__) . '/../files/nonexistent xml file.xml' );
  $t->fail('This should halt execution until the user fixes the file');
}
catch( Exception $e )
{
  if( $e->getMessage() === 'Could not open iTunes File' )
    $t->pass( 'File Does not Exist Exception thrown properly' );
  else
    $t->fail( 'Unexpected exception thrown...' );
}

$parser = new StreemeItunesPlaylistParser( '/Users/user/Music/iTunes/iTunes Music Library.xml');
$playlist_name = $itunes_playlist_id = null;
$playlist_songs = array();
while($parser->getPlaylist($playlist_name, $itunes_playlist_id, $playlist_songs))
{
  var_dump( $playlist_name );
  var_dump( $itunes_playlist_id );
  var_dump( $playlist_songs );
}
$parser->free();