<?php

/**
 * sfGuardGroup form.
 *
 * @package    e-venement
 * @subpackage form
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrinePluginFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class sfGuardGroupForm extends PluginsfGuardGroupForm
{
  public function configure()
  {
    $this->widgetSchema['users_list']
      ->setOption('order_by',array('first_name, last_name, username',''))
      ->setOption('expanded',true);
    $this->widgetSchema['permissions_list']
      ->setOption('order_by',array('name',''))
      ->setOption('expanded',true);
  }
  
  public function saveUsersList($con = null)
  {
    if (!$this->isValid())
    {
      throw $this->getErrorSchema();
    }

    if (!isset($this->widgetSchema['users_list']))
    {
      // somebody has unset this widget
      return;
    }

    if (null === $con)
    {
      $con = $this->getConnection();
    }

    //Get back users from all domains
    $existing = Doctrine::getTable('sfGuardUser')->createQuery('u')
      ->leftJoin('u.Groups g')
      ->andWhere('g.id = ?', $this->object->id)
      ->execute()
      ->getPrimaryKeys();

    $values = $this->getValue('users_list');
    if (!is_array($values))
    {
      $values = array();
    }

    $unlink = array_diff($existing, $values);
    if (count($unlink))
    {
      $this->object->unlink('Users', array_values($unlink));
    }

    $link = array_diff($values, $existing);
    if (count($link))
    {
      $this->object->link('Users', array_values($link));
    }
  }
}
