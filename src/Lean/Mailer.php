<?

namespace Lean;

class Mailer {

  private $message = array();
  private $mailer;

  protected $defaultSender;
  protected $defaultRecipient;

  protected $service = 'mandrill';

  protected function __construct() {

    try {
    	$this->mailer = new \Mandrill(MANDRILL_APIKEY);
    	$this->message = array(
    	  'headers' => array('Reply-To' => getenv('SEND_FROM_EMAIL')),
    	  'important' => false,
    	  'track_opens' => true
    	);
      $this->setDefaults();
    } catch (\ErrorException $e) {
      # Logger::log($e);
    }
  }

  protected function setDefaults(){

    $this->defaultSender = array(
      getenv('SEND_FROM_EMAIL'), getenv('SEND_FROM_NAME')
    );

    $this->defaultRecipient = array(
      getenv('SEND_TO_EMAIL') => getenv('SEND_TO_NAME')
    );

  }

  public static function instance(){
  	return new static;
  }

  public function setTo(array $recipient) {

    $this->message['to'] = array();

    if ( empty( $recipient ) )
      $recipient = $this->defaultRecipient;

    foreach ($recipient as $email => $name)
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
      if (!getenv('MAILER_PRETEND'))
        $this->mailer->messages->send($this->message, false);
      return true;
    } catch (\ErrorException $e){
      Logger::log($e);
    }
  }
  
}