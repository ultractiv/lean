<?

namespace Lean\Mailer;

interface MailerInterface {

  function __construct( $from_email = '', $from_name = '', $to_email = '', $to_name = '');
  
  public static function instance();

  public function setTo(array $recipient);

  public function setFrom(array $sender);

  public function setSubject($subject);

  public function setBody($body);

  public function setAttachments(array $files);

  public function send();

}