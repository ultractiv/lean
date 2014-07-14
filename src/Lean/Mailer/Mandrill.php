<?

namespace Lean\Mailer;

use Lean\Utils;
use Lean\Logger;

class Mandrill implements Interface {
  
  private $mailer;
  private $defaultSender = array();
  private $defaultRecipient = array();
  private $message = array();
  
  public function __construct($apikey, $from_email, $from_name, $to_email, $to_name) {
    
    if (!class_exists('\Mandrill'))
      throw new Exception("mandrill/mandrill package is not installed", 1);
    
    try {
    	$this->mailer = new \Mandrill($apikey);
    } catch (\ErrorException $e) {
      throw new Exception("Mandrill Error:" . $e->getMessage(), 1);
    }
    
    $this->defaultSender = array( $from_email, $from_name );

    $this->defaultRecipient = array( $to_email => $to_name );

    $this->message = array(
      'headers' => array('Reply-To' => $from_email),
      'important' => false,
      'track_opens' => true
    );
    
  }
  
  public static function instance(){
  	return new static;
  }

  public function setTo(array $recipients) {

    $this->message['to'] = array();

    if ( empty( $recipients ) )
      $recipients = $this->defaultRecipient;

    foreach ($recipients as $email => $name)
      array_push( $this->message['to'], array( 'email' => $email, 'name' => $name ) );

    return $this;
  }

  public function setFrom(array $sender) {

    if (empty($sender))
      $sender = $this->defaultSender;

    $this->message['from_email'] = $sender[0];
    $this->message['from_name']  = $sender[1];

    return $this;
  }

  public function setSubject($subject) {
    $this->message['subject'] = $subject;
    return $this;
  }

  public function setBody($body) {
    $this->message['text'] = $body;
    $this->message['html'] = $body;
    return $this;
  }

  public function setAttachments(array $files) {
    $attachments = array();
    foreach ($files as $path => $new_name) {
      if (is_readable($path)) {
        $attachment = array(
          'name' => basename($path),
          'type' => Utils::content_type($path),
          'content' => base64_encode(file_get_contents($path))
        );
        if ($new_name != '') $attachment['name'] = $new_name;
        $attachments[] = $attachment;
      }
    }
    if (!empty($attachments))
      $this->message['attachments'] = $attachments;
    return $this;
  }

  public function send() {
    try {
      // if (!getenv('MAILER_PRETEND'))
      $this->mailer->messages->send($this->message, false);
      return true;
    } catch (\ErrorException $e){
      # Logger::log($e);
    }
  }
  
}