<?

namespace Lean;

class Cache {

  private $cache;
  private $expires = 600; //10 minutes
  
  protected function __construct() {
    if (class_exists('Memcached')) {
      $cache = new \Memcached;
      if ($cache->addServer(getenv('memcached_host'), getenv('memcached_port')))
        $this->cache = $cache;
    }
  }
  
  public static function instance(){
    return new static;    
  }
  
  public function get($key) {
    if (!$this->cache) return null;
    
    $data = $this->cache->get($key);
    return false === $data ? null : $data;
  }
  
  public function set($key, $data) {
    if (!$this->cache) return null;
    return $this->cache->set($key, $data, $this->expires);
  }
  
  public function add($key, $data) {
    if (!$this->cache) return null;
    return $this->cache->add($key, $data, $this->expires);
  }

  public function delete($key) {
    if (!$this->cache) return null;
    return $this->cache->delete($key);
  }
  
  public function replace($key, $data){
    if (!$this->cache) return null;
    return $this->cache->replace($key, $data, $this->expires);
  }
  
  public function flush(){
    if (!$this->cache) return null;
    return $this->cache->flush();
  }
  
}