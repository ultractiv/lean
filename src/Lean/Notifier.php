<?

namespace Lean;

class Notifier {

  protected  $subject = '';
  protected  $message = '';
  protected  $recipient = array();
  protected  $sender = array();
  protected  $attachments = array();
  protected  $useBcc = false;
 
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
    $mailer->service->setSubject($this->subject)
           ->setFrom($this->sender)
           //->setTo($this->recipient)
           ->setBody(nl2br($this->message, false))
           ->setAttachments($this->attachments);

    if ($this->useBcc == true) {
      $mailer->service->setBcc($this->recipient);
    } else $mailer->service->setTo($this->recipient);

    $this->reset();

    $mailer->service->send();

  }

  protected function reset(){
    $this->subject     = '';
    $this->message     = '';
    $this->sender      = array();
    $this->recipient   = array();
    $this->attachments = array();
    $this->useBcc      = false;
  }
  
}