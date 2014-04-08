<?

namespace Lean;

class Logger {
  
  public static function log ($e) {
    error_log('A ' . get_class($e) . ' error occurred: ' . $e->getMessage(), 0);
    throw $e;
  }
  
  public static function logQuery($statement, array $attrs = null){
    $query = preg_replace('/(\?)/', '%s', $statement);
    // array_values($attrs);
    error_log( $query, 0);
  }
  
}
