<?php

/**
 * Created by PhpStorm.
 * User: agbetunsin
 * Date: 4/8/14
 * Time: 10:43 AM
 */

namespace Lean\Application;

use \Lean\RouterException;

class Bootstrap {

  protected $env = 'development';
  protected $config;

  public function __construct(){

    $base_dir =  str_replace('/vendor/ultractiv/lean/src/Lean/Application', '', dirname(__FILE__));

    if (!defined('LEAN_APP_ROOT')) define('LEAN_APP_ROOT', "$base_dir/app");

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

    new Autoloader;

  }

  private function configure(){

    $config = \Spyc::YAMLLoad(LEAN_APP_ROOT . '/config.yaml');

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
      if (array_key_exists('to', $mailer)) {
        putenv('send_to_name='  . $mailer['to']['name']);
        putenv('send_to_email=' . $mailer['to']['email']);
      }
      if ($mailer['use']=='mandrill'){
        putenv('mandrill_api_key=' . $mailer['api_key']);
      }
      if (array_key_exists('pretend', $mailer)) {
        putenv('mailer_pretend=true');
      }
    }

    if (array_key_exists('social_api', $config)) {
      if (array_key_exists('twitter', $config['social'])) {
        $twitter = $config['social']['twitter'];
        define('TWITTER_CONSUMER_KEY',    $twitter['consumer_key']);
        define('TWITTER_CONSUMER_SECRET', $twitter['consumer_secret']);
        define('TWITTER_ACCESS_TOKEN',    $twitter['access_token']);
        define('TWITTER_ACCESS_SECRET',   $twitter['access_secret']);
      }
    }

    if (array_key_exists('uploads', $config)) {
      $root = realpath(LEAN_APP_ROOT . '/../');
      foreach($config['uploads'] as $resource => $directory)
        putenv("{$resource}_upload_dir=". $root .'/'. $directory);
    }

  }

  public function application(){
    if (class_exists('\Router')) new \Router;
    else throw new RouterException("No router class found in ".LEAN_APP_ROOT);
  }

} 