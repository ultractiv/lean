<?

namespace Lean;

class DB {
  
  private $creds;
  private static $db = null;
  
  private function __construct() {
    try {
      $this->getCredentials ();
      self::$db = new \PDO( "mysql:host={$this->creds['host']};dbname={$this->creds['name']}",
                             $this->creds['user'], 
                             $this->creds['pass'], 
                             array ( \PDO::ATTR_PERSISTENT => true )
                          );
      self::$db->setAttribute ( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION );
    } catch ( \PDOException $e ) {
      Logger::log($e);
    }
  }
  
  private function getCredentials() {
    return $this->creds = array (
       'host' => getenv('db_host'),
       'user' => getenv('db_user'),
       'pass' => getenv('db_pass'),
       'name' => getenv('db_name') 
    );
  }
  
  public static function instance() {
    if (!self::$db) new static;
    return self::$db;
  }

}