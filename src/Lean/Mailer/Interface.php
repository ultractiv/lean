<?

namespace Lean\Mailer;

interface Interface {
	
  protected function __construct() {}
  
  public static function instance() {}

  public function setTo(array $recipient) {}  

  public function setFrom(array $sender) {}

  public function setSubject($subject) {}

  public function setBody($body) {}

  public function setAttachments(array $files) {}

  public function send() {}

}