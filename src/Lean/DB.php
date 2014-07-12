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
      'host' => \DATABASE_HOST,
      'user' => \DATABASE_USER,
      'pass' => \DATABASE_PASS,
      'name' => \DATABASE_NAME 
    );
  }
  
  public static function instance() {
    if (!self::$db) new static;
    return self::$db;
  }

}