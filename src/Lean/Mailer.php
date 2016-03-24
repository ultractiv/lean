<?

namespace Lean;

class Mailer {

  protected function __construct() {

    switch (getenv('MAILER_USE')):

      case 'mandrill':
        return Mailer\Mandrill::instance();
            break;

      case 'mailgun':
        return Mailer\Mailgun::instance();
            break;

      case 'sendgrid':
        return Mailer\Sengrid::instance();
            break;

      case 'swiftmailer':
        return Mailer\SwiftMailer::instance();
            break;

    endswitch;

  }

  public static function instance(){
  	return new static;
  }
  
}