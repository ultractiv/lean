<?

/**
 * Created by PhpStorm.
 * User: agbetunsin
 * Date: 4/8/14
 * Time: 10:43 AM
 */

namespace Lean\Application;
use Lean\RouterException;

class Bootstrap {

  protected $env = 'development';
  protected $config;
  protected $patterns = array(
    'ENV_VAR'   => '#^%([a-zA-Z_]+)%$#i',
    'LOCALHOST' => '#^(localhost|127.0.0.1)$#i',
    'MYSQL_URL' => '#^mysql://(?<user>.+):(?<password>.+)@(?<host>.+)/(?<name>.+)\?(.*)?$#i',
    'MONGO_URL' => '#^mongodb://(?<host>.+)/(?<name>.+)$#i',
    'REDIS_URL' => '#^redis://(?<host>.+):(?<port>\d+)$#i'
  );

  public static function instance(){
    return new self;
  }

  public function __construct(){

    $base_dir =  preg_replace('#(/vendor/.*)$#', '', dirname(__FILE__));

    if (!defined('LEAN_APP_ROOT')) define('LEAN_APP_ROOT', "$base_dir/app");

    # Test environment
    if (preg_match($this->patterns['LOCALHOST'], $_SERVER['HTTP_HOST'])) {
      /*$server = array(
        'http_host'=>$_SERVER['HTTP_HOST'],
        'remote_addr'=>$_SERVER['REMOTE_ADDR'],
        'request_uri'=>$_SERVER['REQUEST_URI'],
        'path_info'=>$_SERVER['PATH_INFO'],
        'script_name'=>$_SERVER['SCRIPT_NAME'],
        'document_root'=>$_SERVER['DOCUMENT_ROOT'],
        'server_addr'=>$_SERVER['SERVER_ADDR'],
        'server_name'=>$_SERVER['SERVER_NAME']
      );*/
      $root = explode('/', ltrim($_SERVER['SCRIPT_NAME'],"/"), 2)[0];
    }
    else {
      $this->env = 'production';
      $root = '';
      # Turn off error reporting for all but the most important errors
      error_reporting(E_ERROR);
    }

    if (!defined('HOST')) define('HOST', "{$_SERVER['HTTP_HOST']}/{$root}");

    $this->configure();

    new Autoloader;

  }

  private function configure(){

    $config = \Spyc::YAMLLoad(LEAN_APP_ROOT . '/config.yaml');

    if (array_key_exists('*', $config)) {
      $config = array_merge_recursive($config['*'], $config[$this->env]);
    }

    putenv('ENVIRONMENT='. $this->env);

    $this->config = $config;

    if (array_key_exists('database', $config)) {
      $db = $config['database'];
      define('DATABASE_TYPE', $db['type']);
      if (array_key_exists('url', $db) && preg_match($this->patterns['MYSQL_URL'], $this->read($db['url']), $match)) {
        $db = $match;
      }      
      define('DATABASE_HOST', $this->read($db['host']));
      define('DATABASE_USER', $this->read($db['user']));
      define('DATABASE_PASS', $this->read($db['password']));
      define('DATABASE_NAME', $this->read($db['name']));
    }

    if (array_key_exists('memcached', $config)) {
      $mem = $config['memcached'];
      if (isset($mem['servers']))
        define('MEMCACHE_SERVERS',  $this->read($mem['servers']));
      else if (isset($mem['host']) && isset($mem['port']))
        define('MEMCACHE_SERVERS',  $this->read($mem['host']) . ":" . $this->read($mem['port']) );
      if (isset($mem['username']))
        define('MEMCACHE_USERNAME', $this->read($mem['username']));
      if (isset($mem['password']))
        define('MEMCACHE_PASSWORD', $this->read($mem['password']));
    }

    if (array_key_exists('mailer', $config)) {
      $mailer = $config['mailer'];
      if (array_key_exists('from', $mailer)) {
        putenv('SEND_FROM_NAME='  . $mailer['from']['name']);
        putenv('SEND_FROM_EMAIL=' . $mailer['from']['email']);
      }
      if (array_key_exists('to', $mailer)) {
        putenv('SEND_TO_NAME='  . $mailer['to']['name']);
        putenv('SEND_TO_EMAIL=' . $mailer['to']['email']);
      }
      if (strtolower($mailer['use'])=='mandrill'){
        if (isset($mailer['mandrill_apikey']))
          define('MANDRILL_APIKEY',   $this->read($mailer['mandrill_apikey']));
        if (isset($mailer['mandrill_username']))
          define('MANDRILL_USERNAME', $this->read($mailer['mandrill_username']));
      }
      # TODO: Implement config params parsing fro sendgrid and smtp
      # if (strtolower($mailer['use'])=='sendgrid'){
        # define('SMTP_USER',      $this->read($mailer['smtp_user']));
        # define('SMTP_PASSWORD',  $this->read($mailer['smtp_password']));
        # define('SMTP_HOST',      $this->read($mailer['smtp_host']));
        # define('SMTP_PORT',      $this->read($mailer['smtp_port']));
      # }
      # if (strtolower($mailer['use'])=='smtp'){
        # define('SMTP_USER',      $this->read($mailer['smtp_user']));
        # define('SMTP_PASSWORD',  $this->read($mailer['smtp_password']));
        # define('SMTP_HOST',      $this->read($mailer['smtp_host']));
        # define('SMTP_PORT',      $this->read($mailer['smtp_port']));
      # }
      if (array_key_exists('pretend', $mailer)) {
        putenv('MAILER_PRETEND=true');
      }
    }

    # if (array_key_exists('webservices', $config)) {      
      # $webservices = $config['webservices'];
      if (array_key_exists('twitter', $config)) {
        $twitter = $config['twitter'];
        define('TWITTER_CONSUMER_KEY',    $this->read($twitter['consumer_key']));
        define('TWITTER_CONSUMER_SECRET', $this->read($twitter['consumer_secret']));
        define('TWITTER_ACCESS_TOKEN',    $this->read($twitter['access_token']));
        define('TWITTER_ACCESS_SECRET',   $this->read($twitter['access_secret']));
      }
      if (array_key_exists('aws', $config)) {
        $aws = $config['aws'];
        define('AWS_CONSUMER_KEY',    $this->read($aws['consumer_key']));
        define('AWS_CONSUMER_SECRET', $this->read($aws['consumer_secret']));
      }
    # }

    # TODO: Implement parsing of required AWS config if upload_to == aws
    if (array_key_exists('uploads', $config)) {
      if (array_key_exists('upload_to', $config)) {
        if (strtolower($config['upload_to'])=='aws') {
          # ensure aws configs are set
          
        }
        elseif (strtolower($config['upload_to'])=='filesystem') {
          
        }
      }

      $root = realpath(LEAN_APP_ROOT . '/../');
      foreach($config['uploads'] as $resource => $directory)
        putenv("{$resource}_upload_dir=". $root .'/'. $directory);
    }

  }

  public function application(){
    if (!class_exists('\Router')) throw new RouterException("No Router class found in ".LEAN_APP_ROOT);
    return \Router::instance();
  }

  private function read($var){
    if (!preg_match($this->patterns['ENV_VAR'], $var, $match)) return $var;
    return getenv($match[1]);
  }

} 