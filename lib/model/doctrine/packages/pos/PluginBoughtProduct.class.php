<?php

/**
 * PluginBoughtProduct
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    e-venement
 * @subpackage model
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class PluginBoughtProduct extends BaseBoughtProduct
{
  public function preDelete($event)
  {
    if ( $this->member_card_id )
      throw new liEvenementException('You cannot remove a product linked with a member card, you should better try to remove the member card...');
  }
  public function preSave($event)
  {
    if ( !$this->isModified() )
      return parent::preSave($event);
    
    // if the item is not being bought or is bought already, modifications are not allowed
    $mods = $this->getModified();
    if ( $this->integrated_at && !isset($mods['integrated_at']) )
      throw new liEvenementException('Trying to modify the #'.$this->id.' item which has been bought already.');
    
    parent::preSave($event);
    
    if ( !$this->vat_id && $this->product_declination_id )
      $this->Vat = $this->Declination->Product->Vat;
    if ( is_null($this->vat) && !$this->vat_id )
      throw new liEvenementException('Trying to set VAT on a BoughtProduct w/o prerequisites...');
    if ( is_null($this->vat) )
      $this->vat = $this->Vat->value ? $this->Vat->value : 0;
    if ( !$this->price_name )
      $this->price_name = (string)$this->Price;
    
    if ( !$this->value )
      $this->value = $this->getValueFromSchema();
    
    if ( !$this->name )
      $this->name = (string)$this->Declination->Product;
    if ( !$this->declination )
      $this->declination = (string)$this->Declination;
    
    if ( !$this->code && $this->product_declination_id )
      $this->code = $this->Declination->code;
    if ( !$this->description_for_buyers
      && $this->product_declination_id && $this->Declination->description_for_buyers )
      $this->description_for_buyers = $this->Declination->description_for_buyers;
  }
  
  public function getValueFromSchema()
  {
    $value = NULL;
    foreach ( $this->Declination->Product->PriceProducts as $p )
    if ( $this->price_id == $p->price_id )
      $value = $p->value ? $p->value : 0; // free price here
    
    return $value;
  }
  public function isSold()
  {
    return !is_null($this->integrated_at);
  }
  
  public function getIndexesPrefix()
  {
    return strtolower(get_class($this));
  }
}
