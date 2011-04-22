<?php
#
# Find an appropriate combined js/css file in cache
#
class combineAction extends sfAction
{  
  public function execute($request)
  {
    $this->setRenderMode(sfView::RENDER_NONE);
    $combiner = new combineFiles();
    $file = $combiner->getFileName($request->getParameter('type'), $request->getParameter('namespace'));
    if( is_readable( $file ) )
    {
      $this->response->setContent( file_get_contents( $file ) );
    }
    $this->response->sendHttpHeaders();
    $this->response->sendContent();
    return sfView::NONE;
  }
}