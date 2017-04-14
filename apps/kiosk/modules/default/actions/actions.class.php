<?php

/**
 * default actions.
 *
 * @package    e-venement
 * @subpackage default
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class defaultActions extends sfActions
{
 /**
  * Executes index action
  *
  * @param sfRequest $request A request object
  */
  public function executeIndex(sfWebRequest $request)
  {
  }

  public function executeCulture(sfWebRequest $request)
  {
    $cultures = array_keys(sfConfig::get('project_internals_cultures', array('fr' => 'Français')));
    
    // culture defined explicitly
    if ( $request->hasParameter('lang') && in_array($request->getParameter('lang'), $cultures) )
    {
      $this->getUser()->setCulture($request->getParameter('lang'));
      $this->getUser()->setAttribute('global_culture_forced', true);
    }
    
    if ( !$this->getUser()->getAttribute('global_culture_forced', false) )
    {
      // all the browser's languages
      $user_langs = array();
      foreach ( $request->getLanguages() as $lang )
      if ( !isset($user_lang[substr($lang, 0, 2)]) )
        $user_langs[substr($lang, 0, 2)] = $lang;
      
      // comparing to the supported languages
      $done = false;
      foreach ( $user_langs as $culture => $lang )
      if ( in_array($culture, $cultures) )
      {
        $done = $culture;
        $this->getUser()->setCulture($culture);
        break;
      }
      
      // culture by default
      if ( !$done )
        $this->getUser()->setCulture($cultures[0]);
    }
      
    $this->redirect($request->getReferer());
  }

}
