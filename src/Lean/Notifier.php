<?

namespace Lean;

class Notifier {

  protected  $attachments = null;
  protected  $subject = '';
  protected  $message = '';
  protected  $recipient;
  protected  $sender = null;

  protected  $defaultSender;

  public function __construct() {
    $this->init();
  }

  protected function init(){}
  
  /*public static function instance(){
  	return new self();
  }*/
  
  // Sends off the notification
  protected function send(){

    if (!$this->sender)
      $this->sender = array( $this->defaultSender['email'], $this->defaultSender['name'] );

    $mailer = Mailer::instance();
    $mailer->setSubject($this->subject)
           ->setFrom($this->sender)
           ->setTo($this->recipient)
           ->setBody(nl2br($this->message, false));

    if (!empty($this->attachments))
      $mailer->setAttachments($this->attachments);

    $mailer->send();

  }
  
}