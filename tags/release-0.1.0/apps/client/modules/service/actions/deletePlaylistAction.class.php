<?php
#
# Delete a Playlist
#
class deletePlaylistAction extends sfAction
{
  public function execute($request)
  {
		//validate required fields
		$playlist_id = $request->getParameter( 'playlist_id' );
    if( !isset( $playlist_id ) || empty( $playlist_id ) ) $this->forward404();

    //add content
    $playlist = new PlaylistService();
		$playlist->delete_playlist( $playlist_id );
		exit;
  }
}