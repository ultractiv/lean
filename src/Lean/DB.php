<?

namespace Lean;

class DB {
  
  private $creds;
  private static $db = null;
  
  private function __construct() {
    try {
      $this->getCredentials ();

      $options = array ( 
        \PDO::ATTR_PERSISTENT => true,
        \PDO::ATTR_ERRMODE    => \PDO::ERRMODE_EXCEPTION 
      );

      self::$db = new \PDO( "mysql:host={$this->creds['host']};port={$this->creds['port']};dbname={$this->creds['name']}",
                             $this->creds['user'], 
                             $this->creds['pass'], 
                             $options
                          );
      # self::$db->setAttribute (  );
    } catch ( \PDOException $e ) {
      Logger::log($e);
    }
  }
  
  private function getCredentials() {
    return $this->creds = array (
      'host' => DATABASE_HOST,
      'user' => DATABASE_USER,
      'pass' => DATABASE_PASS,
      'name' => DATABASE_NAME,
      'port' => DATABASE_PORT 
    );
  }
  
  public static function instance() {
    if (!self::$db) new static;
    return self::$db;
  }

}