<?

namespace Lean;

class Mailer {

  private $message = array();
  private $mailer;

  protected $service = 'mandrill';

  private function __construct() {

    try {
    	$this->mailer = new \Mandrill(getenv('mandrill_api_key'));
    	$this->message = array(
    	  'headers' => array('Reply-To' => getenv('send_from_email')),
    	  'important' => false,
    	  'track_opens' => true
    	);
    } catch (\ErrorException $e) {
      Logger::log($e);
    }
  }

  public static function instance(){
  	return new self;
  }

  public function setTo(array $recipent) {
    $this->message['to'] = array();
    foreach ($recipent as $email=>$name)
      array_push( $this->message['to'], array( 'email' => $email, 'name' => $name ) );
    return $this;
  }

  public function setFrom(array $sender) {
    $this->message['from_email'] = $sender[0];
    $this->message['from_name'] = $sender[1];
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
    $this->message['attachments'] = $attachments;
    return $this;
  }

  public function send() {
    try {
      $this->mailer->messages->send($this->message, false);
      return true;
    } catch (\ErrorException $e){
      Logger::log($e);
    }
  }
  
}