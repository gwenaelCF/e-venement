<?php

/**
 * PluginMemberCard
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    e-venement
 * @subpackage model
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class PluginMemberCard extends BaseMemberCard
{
  public function preSave($event)
  {
    if ( !$this->transaction_id && $this->Payments->count() > 0 )
      $this->Transaction = $this->Payments[0]->Transaction;
    
    if ( $this->MemberCardType->product_declination_id
      && $this->MemberCardType->price_id
      && $this->Transaction->MemberCards->count() > 0
      && $this->BoughtProducts->count() == 0
    )
    {
      $bp = new BoughtProduct;
      $bp->Declination = $this->MemberCardType->ProductDeclination;
      $bp->Price = $this->MemberCardType->Price;
      $bp->Transaction = $this->Transaction;
      $bp->Transaction->contact_id = $this->contact_id;
      $bp->integrated_at = date('Y-m-d H:i:s');
      $this->BoughtProducts[] = $bp;
    }
    
    parent::preSave($event);
  }
  
  public function getIndexesPrefix()
  {
    return strtolower(get_class($this));
  }
}
