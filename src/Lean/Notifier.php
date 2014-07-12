<?

namespace Lean;

class Notifier {

  protected  $subject = '';
  protected  $message = '';
  protected  $recipient = array();
  protected  $sender = array();
  protected  $attachments = array();  
 
  public static function instance() {
    return new static;
  }

  protected function __construct() {
    
    $this->init();

  }

  protected function init(){}
  
  // Sends off the notification
  protected function send(){
    
    $mailer = Mailer::instance();
    $mailer->setSubject($this->subject)
           ->setFrom($this->sender)
           ->setTo($this->recipient)
           ->setBody(nl2br($this->message, false));

    if (!empty($this->attachments))
      $mailer->setAttachments($this->attachments);

    $this->reset();

    $mailer->send();

  }

  protected function reset(){
    $this->sender      = array();
    $this->recipient   = array();
    $this->attachments = array();
  }
  
}