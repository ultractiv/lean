<?

namespace Lean;

class Mailer {

    public $service;

  protected function __construct() {

    switch (getenv('MAILER_USE')):

      case 'mandrill':
          $this->service = Mailer\Mandrill::instance();
          break;

      case 'mailgun':
          $this->service =  Mailer\Mailgun::instance();
            break;

      case 'sendgrid':
          $this->service =  Mailer\Sengrid::instance();
          break;

      case 'swiftmailer':
          $this->service =  Mailer\SwiftMailer::instance();
          break;

    endswitch;

  }

  public static function instance(){
  	return new static;
  }
  
}