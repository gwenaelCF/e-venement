<?php

/**
 * OptionAccounting form.
 *
 * @package    e-venement
 * @subpackage form
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class OptionAccountingForm extends BaseOptionAccountingForm
{
  protected $model = 'OptionAccounting';
  
  /**
   * @see OptionForm
   */
  public function configure()
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers('I18N');
    parent::configure();
    
    $this->widgets = array('' => array(
      'file'                  => array('label' => 'File number',              'type' => 'integer', 'options' => array('min' => 0, 'max' => 9999)),
      'ja_code'               => array('label' => 'JA code',                  'type' => 'integer', 'options' => array('min' => 0, 'max' => 99)),
      'acc_section_analytic'  => array('label' => 'Analytic section',         'type' => 'integer', 'options' => array('min' => 0, 'max' => 999)),
      'acc_section_other'     => array('label' => 'Other section',            'type' => 'integer', 'options' => array('min' => 0, 'max' => 999)),
      'code_doc_invoice'      => array('label' => 'Invoice document code',    'type' => 'string', 'default' => 'F', 'options' => array('min_length' => 1, 'max_length' => 1)),
      'code_doc_credit_side'  => array('label' => 'Credit side code',         'type' => 'string', 'default' => 'A', 'options' => array('max_length' => 1)), // un "avoir" comptable
      'code_debit'            => array('label' => 'Debit code',               'type' => 'string', 'default' => 'D', 'options' => array('min_length' => 1, 'max_length' => 1)),
      'code_credit'           => array('label' => 'Credit code',              'type' => 'string', 'default' => 'C', 'options' => array('min_length' => 1, 'max_length' => 1)),
      'analytic_account'      => array('label' => 'Analytic account',         'type' => 'integer', 'options' => array('min' => 0, 'max' => 999999999999)),
      'rec_code_transaction'  => array('label' => 'Transaction record code',  'type' => 'string', 'default' => 'T', 'options' => array('min_length' => 1, 'max_length' => 1)),
      'rec_code_imputation'   => array('label' => 'Imputation record code',   'type' => 'string', 'default' => 'I', 'options' => array('min_length' => 1, 'max_length' => 1)),
      'rec_code_expiration'   => array('label' => 'Expiration record code',   'type' => 'string', 'default' => 'E', 'options' => array('min_length' => 1, 'max_length' => 1)),
      'rec_code_analytic'     => array('label' => 'Analytic record code',     'type' => 'string', 'default' => 'Y', 'options' => array('min_length' => 1, 'max_length' => 1)),
      'incomes_acc_generic'   => array('label' => 'Generic account for incomes', 'type' => 'string', 'options' => array('min_length' => 1, 'max_length' => 10)),
      'incomes_acc_vat'       => array('label' => 'Account for VAT',          'type' => 'string', 'options' => array('min_length' => 1, 'max_length' => 10)),
      'currency'              => array('label' => 'Currency',                 'type' => 'string', 'default' => 'EUR', 'options' => array('max_length' => 3, 'min_length' => 1)),
    ));
    
    foreach ( $this->widgets as $fsname => $fieldset )
    foreach ( $fieldset as $name => $value )
    {
      if ( !isset($value['helper']) )
        $value['helper'] = $this->widgets[$fsname][$name]['helper'] = '';
      if ( !isset($value['default']) )
        $value['default'] = $this->widgets[$fsname][$name]['default'] = '';
      
      $validator_class = 'sfValidator'.strtoupper(substr($value['type'],0,1)).strtolower(substr($value['type'],1));
      
      $this->widgetSchema   [$name] = new sfWidgetFormInputText(array(
          'label'                 => $value['label'],
          'default'               => $value['default'],
        ),
        array(
          'title'                 => __('default:').' '.$value['default'].' '.$value['helper'],
      ));
      
      $value['options']['required'] = true;
      $this->validatorSchema[$name] = new $validator_class($value['options']);
    }
  }
  
  public function getDBOptions()
  {
    $r = array();
    
    foreach ( self::buildOptionsQuery()->fetchArray() as $opt )
      $r[$opt['name']] = $opt['value'];
    
    return $r;
  }
  
  protected static function buildOptionsQuery()
  {
    return Doctrine::getTable('OptionAccounting')->createQuery('acc')
      ->andWhere('acc.sf_guard_user_id IS NULL');
  }
}
