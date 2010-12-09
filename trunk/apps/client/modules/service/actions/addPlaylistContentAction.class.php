<?php
#
# Add Content to the user's playlists 
#
class addPlaylistContentAction extends sfAction
{
  public function execute($request)
  {
		//validate required fields
    if ( $request->getParameter( 'playlist_id' ) == 'false' ) $this->forward404();

    //add content
    $playlist = new PlaylistService();
		$playlist->add_to_playlist( $request->getParameter( 'playlist_id' ), $request->getParameter( 'id' ), $request->getParameter( 'type' ) );
		exit;
  }
}