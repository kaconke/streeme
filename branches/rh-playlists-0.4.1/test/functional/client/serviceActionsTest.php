<?php

include(dirname(__FILE__).'/../../bootstrap/functional.php');
$browser = new DoctrineTestFunctional(new sfBrowser());
$browser->loadData()->restart();

$browser->
  info( '1. Internal services should be secure' )->
  
  get('/service/addPlaylist')->
   
  with('request')->begin()->
    isParameter('module', 'service')->
    isParameter('action', 'addPlaylist')->
  end()->

  with('response')->begin()->
    isStatusCode(401)->
  end()->
  
  info('2. Login and add a playlist')->
 
  authenticate()->
  post('/service/addPlaylist', array('name' => 'testplaylist'))->
  with('response')->begin()->
    isStatusCode(200)->
  end()->
  
  info('3. Read the new playlist')->
  
  info('Without an alpha letter')->
  get('/service/listPlaylists', array())->
  
  with('request')->begin()->
    isParameter('module', 'service')->
    isParameter('action', 'listPlaylists')->
  end()->
  
  with('response')->begin()->
    isStatusCode(200)->
    matches('/\[\{\"id\"\:\"1\",\"name\"\:\"Default Playlist\"\},\{\"id\"\:\"2\",\"name\"\:\"testplaylist\"\}\]/')->
  end()->
  
  info('With alpha letter "t"')->
  get('/service/listPlaylists', array( 'alpha' => 't' ))->
  
  with('response')->begin()->
    isStatusCode(200)->
    matches('/\[\{\"id\"\:\"2\",\"name\"\:\"testplaylist\"\}\]/')->
  end()->
  
  info('4. Add content to the playlist')->

  info('no playlist specified')->
  post('/service/addPlaylistContent', array())->
  
  with('request')->begin()->
    isParameter('module', 'service')->
    isParameter('action', 'addPlaylistContent')->
  end()->
  
  with('response')->begin()->
    isStatusCode(404)->
  end()->
  
  info('Playlist 2 specified adding song 1')->
  post('/service/addPlaylistContent', array('playlist_id'=>'2', 'id' =>'9qw9dwj9wqdjw9qjqw9jd', 'type' => 'song'))->
  with('response')->begin()->
    isStatusCode(200)->
  end()->

  post('/service/deletePlaylistContent', array())->
  
  with('request')->begin()->
    isParameter('module', 'service')->
    isParameter('action', 'deletePlaylistContent')->
  end()->
  
  with('response')->begin()->
    isStatusCode(404)->
  end()->
  
  info('Playlist 2 specified deleting song 1')->
  post('/service/addPlaylistContent', array('playlist_id'=>'2', 'id' =>'9qw9dwj9wqdjw9qjqw9jd', 'type' => 'song'))->
  with('response')->begin()->
    isStatusCode(200)->
  end()->
  
  info('5. Delete the test playlist')->
  
  post('/service/deletePlaylist', array('playlist_id' => '2'))->
  with('response')->begin()->
    isStatusCode(200)->
  end()->
  
  get('/service/listPlaylists', array( 'alpha' => 't' ))->
  
  with('response')->begin()->
    isStatusCode(200)->
    matches('/\[\]/')->
  end()  
;
