<?

namespace Lean;

class Notifier {

  protected  $subject = '';
  protected  $message = '';
  protected  $recipient = array();
  protected  $sender = array();
  protected  $attachments = array();

  /*
  protected  $defaultSender;
  protected  $defaultRecipient;
  */
 
  public static function instance() {
    return new static;
  }

  protected function __construct() {

    /*
    $this->defaultSender = array(
      getenv('send_from_email'), getenv('send_from_name')
    );

    $this->defaultRecipient = array(
      getenv('send_to_email') => getenv('send_to_name')
    );
    */

    $this->init();

  }

  protected function init(){}
  
  /*public static function instance(){
  	return new self();
  }*/
  
  // Sends off the notification
  protected function send(){

    /*
    if (!$this->sender)
      $this->sender = $this->defaultSender;

    if (!$this->recipient)
      $this->recipient = $this->defaultRecipient;*/

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