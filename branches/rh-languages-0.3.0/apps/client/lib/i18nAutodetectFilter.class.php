<?php
class i18nAutodetectFilter extends sfFilter
{

  /**
   * Log filter activity
   *
   * @param string  $message
   * @param int     $level
   */
  public function log($message, $level = sfLogger::DEBUG)
  {
    sfContext::getInstance()->getLogger()->log('{i18nSubdomainFilter} '.$message, $level);
  }

  /**
   * Executes filter chain
   *
   * @param sfFilterChain $filterChain
   */
  public function execute($filterChain)
  {
    if ($this->isFirstCall())
    {
      $context = $this->getContext();
      $request = $context->getRequest();
      $user    = $context->getUser();
      
      //get the user's preferred (browser) language
      $lang    =  $request->getPreferredCulture( sfConfig::get('sf_translations_available', array() ) );
      
      //has the user overridden the language?
      if( $request->getParameter('sf_culture') && in_array( $request->getParameter('sf_culture'),  sfConfig::get('sf_translations_available', array() ) ) )
      {
        $lang = $request->getParameter('sf_culture');
      }
      
      $this->log(sprintf('Applying detected requested lang to session: %s', $lang));
      $user->setCulture( $lang );
      sfConfig::set('sf_current_culture', $lang );
      $context->getResponse()->addMeta( 'language', $lang, true );
    }
    $filterChain->execute();
  }

}
