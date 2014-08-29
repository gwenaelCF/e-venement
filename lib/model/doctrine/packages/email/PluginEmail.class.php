<?php

/**
 * PluginEmail
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    e-venement
 * @subpackage model
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class PluginEmail extends BaseEmail
{
  public $not_a_test      = false;
  public $test_address    = NULL;
  public $to              = array();
  public $mailer          = NULL;
  
  protected function send()
  {
    if ( !$this->to )
      $this->to = array();
    if ( !is_array($this->to) )
      $this->to = array($this->to);
    
    // sending one by one to linked ...
    // contacts
    foreach ( $this->Contacts as $contact )
    if ( $contact->email && !$contact->email_no_newsletter )
      $this->to[] = trim($contact->email);
    // professionals
    foreach ( $this->Professionals as $pro )
    if ( $pro->contact_email && !$pro->contact_email_no_newsletter )
      $this->to[] = trim($pro->contact_email);
    else if ( $pro->Organism->email && !$pro->Organism->email_no_newsletter )
      $this->to[] = trim($pro->Organism->email);
    else if ( $pro->Contact->email && !$pro->Contact->email_no_newsletter )
      $this->to[] = trim($pro->Contact->email);
    // organisms
    foreach ( $this->Organisms as $organism )
    if ( $organism->email && !$organism->email_no_newsletter )
      $this->to[] = trim($organism->email);
    
    // concatenate addresses
    /*
    if ( $this->field_to )
      $this->to = array_merge($this->to,explode(',',str_replace(' ','',$this->field_to)));
    */
    $this->field_to .= implode(', ',$this->to);
    return $this->raw_send(null,$this->nospool);
  }

  protected function sendTest()
  {
    if ( !$this->test_address )
      return false;
    
    return $this->raw_send(array($this->test_address),true);
  }
  
  protected function raw_send($to = array(), $immediatly = false)
  {
    $to = is_array($to) && count($to) > 0 ? $to : $this->to;
    if ( !$to && !$this->field_to )
      return false;
    
    $message = $this->compose(Swift_Message::newInstance()->setTo($to));
    
    foreach ( $this->Attachments as $attachment )
    {
      $att = Swift_Attachment::fromPath($path = substr($attachment->filename, 0, 1) == '/'
          ? $attachment->filename
          : sfConfig::get('sf_upload_dir').DIRECTORY_SEPARATOR.$attachment->filename, $attachment->mime_type)
        ->setFilename($attachment->original_name)
        ->setId('part.'.$attachment->id.'@e-venement');
      $message->attach($att);
    }
    
    $this->setMailer();
    return $immediatly === true
      ? $this->mailer->sendNextImmediately()->send($message)
      : $this->mailer->batchSend($message);
  }
  
  public function setMailer(sfMailer $mailer = NULL)
  {
    if ( $this->mailer instanceof sfMailer )
      return $this;
    
    if ( $mailer instanceof sfMailer )
      $this->mailer = $mailer;
    else if ( sfContext::hasInstance() )
      $this->mailer = sfContext::getInstance()->getMailer();
    
    return $this;
  }

  protected function compose(Swift_Message $message)
  {
    $content = 
      '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'.
      '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">'.
      '<head>'.
      '<title></title>'.
      //'<title>'.$this->field_subject.'</title>'.
      '<meta name="title" content="'.$this->field_subject.'" />'.
      '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />'.
      '</head><body>'.
      $this->content.
      '</body></html>';
    
    $h2t = new HtmlToText($content);
    $message
      ->setFrom($this->field_from)
      ->setSubject($this->field_subject)
      ->setBody($h2t->get_html(),'text/html')
      ->addPart($h2t->get_text(),'text/plain');
    if ( $this->read_receipt )
      $message->setReadReceiptTo($this->field_from);
    return $message;
  }
}
