<?php

require_once dirname(__FILE__).'/../lib/payment_methodGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/payment_methodGeneratorHelper.class.php';

/**
 * payment_method actions.
 *
 * @package    e-venement
 * @subpackage payment_method
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class payment_methodActions extends autoPayment_methodActions
{
  public function executeDelPicture(sfWebRequest $request)
  {
    $q = Doctrine_Query::create()->from('Picture p')
      ->where('p.id IN (SELECT e.picture_id FROM PaymentMethod e WHERE e.id = ?)',$request->getParameter('id'))
      ->delete()
      ->execute();
    return sfView::NONE;
  }
}
