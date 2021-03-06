<?php

/**
 * Base project form.
 * 
 * @package    e-venement
 * @subpackage form
 * @author     Your name here 
 * @version    SVN: $Id: BaseForm.class.php 20147 2009-07-13 11:46:57Z FabianLange $
 */
class LedgerCriteriasForm extends BaseForm
{
  public function configure()
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('Url','CrossAppLink'));
    
    $this->widgetSchema['dates'] = new sfWidgetFormDateRange(array(
      'from_date' => new liWidgetFormJQueryDateText(array('culture' => sfContext::getInstance()->getUser()->getCulture())),
      'to_date'   => new liWidgetFormJQueryDateText(array('culture' => sfContext::getInstance()->getUser()->getCulture())),
      'template'  => '<span class="dates"><span>'.__('From %from_date%').'</span><br><span>'.__('to %to_date% excluded').'</span></span>',
    ));
    $this->validatorSchema['dates'] = new sfValidatorDateRange(array(
      'from_date' => new sfValidatorDate(array('required' => false)),
      'to_date'   => new sfValidatorDate(array('required' => false)),
      'required' => false,
    ));

    $q = Doctrine::getTable('sfGuardUser')->createQuery('u')
      ->andWhere('u.is_active = ?',true);
    if ( !sfContext::getInstance()->getUser()->hasCredential('tck-ledger-all-users') )
      $q->andWhere('u.id = ?',sfContext::getInstance()->getUser()->getId());
    $this->widgetSchema['users'] = new sfWidgetFormDoctrineChoice(array(
      'model'     => 'sfGuardUser',
      'query'     => $q,
      'order_by'  => array('first_name, last_name',''),
      'multiple'  => true,
    ));
    $this->validatorSchema['users'] = new sfValidatorDoctrineChoice(array(
      'model' => 'sfGuardUser',
      'multiple' => true,
      'required' => false,
      'query'     => $q,
    ));
    
    $this->widgetSchema['manifestations'] = new cxWidgetFormDoctrineJQuerySelectMany(array(
      'model' => 'Manifestation',
      'url'   => cross_app_url_for('event','manifestation/ajax'),
      'config'=> '{ max: 50 }',
    ));
    $this->validatorSchema['manifestations'] = new sfValidatorDoctrineChoice(array(
      'model' => 'Manifestation',
      'multiple' => true,
      'required' => false,
    ));
    
    $this->widgetSchema['contact_id'] = new liWidgetFormDoctrineJQueryAutocompleter(array(
      'model' => 'Contact',
      'url'   => cross_app_url_for('rp','contact/ajax'),
    ));
    $this->validatorSchema['contact_id'] = new sfValidatorDoctrineChoice(array(
      'model' => 'Contact',
      'required' => false,
    ));
    $this->widgetSchema['organism_id'] = new liWidgetFormDoctrineJQueryAutocompleter(array(
      'model' => 'Organism',
      'url'   => cross_app_url_for('rp','organism/ajax'),
    ));
    $this->validatorSchema['organism_id'] = new sfValidatorDoctrineChoice(array(
      'model' => 'Organism',
      'required' => false,
    ));
    
    $this->widgetSchema['workspaces'] = new sfWidgetFormDoctrineChoice(array(
      'model'     => 'Workspace',
      'query' => $q = Doctrine::getTable('Workspace')->createQuery('ws')
        ->andWhereIn('ws.id', array_keys(sfContext::getInstance()->getUser()->getWorkspacesCredentials())),
      'order_by'  => array('name',''),
      'multiple'  => true,
    ));
    $this->validatorSchema['workspaces'] = new sfValidatorDoctrineChoice(array(
      'model' => 'Workspace',
      'multiple' => true,
      'required' => false,
    ));
    
    $this->widgetSchema['not-yet-printed'] = new sfWidgetFormInputCheckbox(array(
      'value_attribute_value' => 'yes',
    ));
    $this->validatorSchema['not-yet-printed'] = new sfValidatorBoolean(array(
      'required' => false,
    ));
    
    $this->widgetSchema['tck_value_date_payment'] = new sfWidgetFormInputCheckbox(array(
      'value_attribute_value' => 'yes',
    ));
    $this->validatorSchema['tck_value_date_payment'] = new sfValidatorBoolean(array(
      'required' => false,
    ));
    
    $this->widgetSchema['payment_limit_with_tck_date'] = new sfWidgetFormInputCheckbox(array(
      'value_attribute_value' => 'yes',
    ));
    $this->validatorSchema['payment_limit_with_tck_date'] = new sfValidatorBoolean(array(
      'required' => false,
    ));
    
    $this->widgetSchema->setNameFormat('criterias[%s]');
    $this->disableCSRFProtection();
  }
}
