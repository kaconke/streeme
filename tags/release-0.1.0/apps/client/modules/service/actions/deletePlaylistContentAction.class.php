<?php
#
# Delete Content from the user's playlists 
#
class deletePlaylistContentAction extends sfAction
{
  public function execute($request)
  {
		//validate required fields
    if ( $request->getParameter( 'playlist_id' ) == 'false' ) $this->forward404();

    //add content
    $playlist = new PlaylistService();
		$playlist->delete_from_playlist( $request->getParameter( 'playlist_id' ), $request->getParameter( 'id' ) );
		exit;
  }
}