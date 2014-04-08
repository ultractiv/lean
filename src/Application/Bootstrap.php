<?php
/**
 * Created by PhpStorm.
 * User: agbetunsin
 * Date: 4/8/14
 * Time: 10:43 AM
 */

namespace Lean\Application;

class Bootstrap {

  protected $env = 'development';
  protected $config;

  public function __construct(){

    #if (!defined('ROOT')) define('ROOT', dirname(\__FILE__)."/");
    #if (!defined('APP_ROOT')) define('APP_ROOT', realpath( dirname(\__FILE__)."/../../app/"));

    if (!defined('HOST')) define('HOST', "{$_SERVER['HTTP_HOST']}/");

    # Test environment

    if (!preg_match('/localhost/i', HOST)) {
      $this->env = 'production';
      # Turn off error reporting for all but the most important errors
      error_reporting(E_ERROR);
    }
    #else {
      # redefine host to include application root directory name
      # $host .= '/annals';
    #}

    $this->configure();

  }

  private function configure(){

    $config = \Spyc::YAMLLoad(APP_ROOT . 'config.yaml');

    if (array_key_exists('*', $config)) {
      $config = array_merge_recursive($config['*'], $config[$this->env]);
    }

    putenv('env='. $this->env);

    $this->config = $config;

    if (array_key_exists('database', $config)) {
      $db = $config['database'];
      putenv('db_host='. $db['host']);
      putenv('db_user='. $db['user']);
      putenv('db_pass='. $db['password']);
      putenv('db_name='. $db['name']);
      putenv('db_type='. $db['type']);
    }

    if (array_key_exists('memcached', $config)) {
      $mem = $config['memcached'];
      putenv('memcached_host='. $mem['host']);
      putenv('memcached_port='. $mem['port']);
    }

    if (array_key_exists('mailer', $config)) {
      $mailer = $config['mailer'];
      if (array_key_exists('from', $mailer)) {
        putenv('send_from_name='  . $mailer['from']['name']);
        putenv('send_from_email=' . $mailer['from']['email']);
      }
      if ($mailer['use']=='mandrill'){
        putenv('mandrill_api_key=' . $mailer['api_key']);
      }
    }

    if (array_key_exists('uploads', $config)) {
      foreach($config['uploads'] as $resource => $directory)
        putenv("{$resource}_upload_dir=". ROOT . $directory);
    }

  }

} 