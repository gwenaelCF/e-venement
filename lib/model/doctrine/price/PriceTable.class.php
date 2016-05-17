<?php

/**
 * PriceTable
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class PriceTable extends PluginPriceTable
{
  public function fetchTheMostExpansiveForGauge($gauge_id)
  {
    $q = $this->createQuery('p')
      ->leftJoin('p.PriceGauges pg')
      ->leftJoin('pg.Gauge g')
      ->leftJoin('p.PriceManifestations pm')
      ->leftJoin('pm.Manifestation m')
      ->leftJoin('m.Gauge mg')
      ->andWhere('(mg.id = ? OR g.id = ?)', array($gauge_id, $gauge_id))
      ->orderBy('pg.value DESC, pm.value DESC')
      ->select('p.*, (CASE WHEN pg.value IS NULL THEN pm.value ELSE pg.value END) AS real_value')
    ;
    return $q->fetchOne();
  }
  
  public function createQuery($alias = 'p', $override_credentials = true)
  {
    $q = parent::createQuery($alias);
    
    if ( sfContext::hasInstance() && ($user = sfContext::getInstance()->getUser()) && $user->getId()
      && (!$override_credentials || !$user->isSuperAdmin() && !$user->hasCredential('event-admin-price')) )
      $q->andWhere("$alias.id IN (SELECT up.price_id FROM UserPrice up WHERE up.sf_guard_user_id = 0) OR (SELECT count(up2.price_id) FROM UserPrice up2 WHERE up2.sf_guard_user_id = ?) = 0",array($user->getId(),$user->getId()));
    $q->leftJoin("$alias.Translation pt");
    
    return $q;
  }
  
  public function fetchOneByName($name)
  {
    $q = $this->createQuery('p')->andWhere('pt.name = ?',$name);
    return $q->fetchOne();
  }
  
  /**
   * Returns an instance of this class.
   *
   * @return object PriceTable
   */
  public static function getInstance()
  {
    return Doctrine_Core::getTable('Price');
  }
}
