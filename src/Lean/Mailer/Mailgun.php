<?

namespace Lean\Mailer;

use Lean\Utils;
# use Lean\Logger;

class Mailgun implements MailerInterface {
  
  private $mailer;
  private $defaultSender = array();
  private $defaultRecipient = array();
  private $message = array();
  private $attachments = array();
  
  public function __construct($from_email = '', $from_name = '', $to_email = '', $to_name = '') {
    
    if (!class_exists('\Mailgun\Mailgun'))
      throw new \Exception("mailgun/mailgun-php package is not installed", 1);

    if (!defined('MAILGUN_APIKEY'))
      throw new \Exception("mailgun_apikey must be configured", 1);

    if (!defined('MAILGUN_DOMAIN'))
      throw new \Exception("mailgun_domain must be configured", 1);
    
    try {
    	$this->mailer = new \Mailgun\Mailgun(MAILGUN_APIKEY);
    } catch (\ErrorException $e) {
      throw new \Exception("Mailgun Error:" . $e->getMessage(), 1);
    }

      $this->defaultSender = array( getenv('SEND_FROM_EMAIL'), getenv('SEND_FROM_NAME') );

      $this->defaultRecipient = array( getenv('SEND_TO_EMAIL') => getenv('SEND_TO_NAME') );
    
  }
  
  public static function instance(){
  	return new static;
  }

  public function setTo(array $recipients) {

    $to = array();

    if ( empty( $recipients ) )
      $recipients = $this->defaultRecipient;

    foreach ($recipients as $email => $name) {
      $to[] = "$name <{$email}>";
    }

    $this->message['to'] = join(', ', $to);

    return $this;

  }

  public function setFrom(array $sender) {

    if (empty($sender))
      $sender = $this->defaultSender;

    $this->message['from'] = "'{$sender[1]}' <{$sender[0]}>";

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
    $attach = array();
    foreach ($files as $path => $new_name) {
      if (is_readable($path)) {
        $attach = array(
          'remoteName' => ($new_name != '') ? $new_name : basename($path),
          'filePath' => $path
        );
      }
    }
    if (!empty($attach))
      $this->attachments['attachment'] = $attach;
    return $this;
  }

  public function send() {
    try {
      if (!getenv('MAILER_PRETEND'))
        $this->mailer->sendMessage(MAILGUN_DOMAIN, $this->message, $this->attachments);
      var_dump($this->attachments);
      return true;
    } catch (\ErrorException $e){
      # Logger::log($e);
    }
  }
  
}