<?

namespace Lean;

class Cache {

  private $memcache;
  private $expires = 600; //10 minutes
  
  protected function __construct() {
    if (class_exists('\Memcached')) {
      $m = new \Memcached("memcached_pool");
      $m->setOption(\Memcached::OPT_BINARY_PROTOCOL, TRUE);
      // some nicer default options
      $m->setOption(\Memcached::OPT_NO_BLOCK, TRUE);
      $m->setOption(\Memcached::OPT_AUTO_EJECT_HOSTS, TRUE);
      // We use a consistent connection to memcached, so only add in the
      // servers first time through otherwise we end up duplicating our
      // connections to the server.
      if (!$m->getServerList()) {
        // parse server config
        $servers = explode(",", MEMCACHE_SERVERS);
        foreach ($servers as $s) {
          $parts = explode(":", $s);
          $m->addServer($parts[0], $parts[1]);
        }
      }
      // setup authentication
      if (defined(MEMCACHE_USERNAME) && defined(MEMCACHE_PASSWORD)) 
        $m->setSaslAuthData( \MEMCACHE_USERNAME, \MEMCACHE_PASSWORD );
      $this->memcache = $m;      
    }
  }
  
  public static function instance(){
    return new static;    
  }
  
  public function get($key) {
    if (!$this->memcache) return null;
    
    $data = $this->memcache->get($key);
    return false === $data ? null : $data;
  }
  
  public function set($key, $data) {
    if (!$this->memcache) return null;
    return $this->memcache->set($key, $data, $this->expires);
  }
  
  public function add($key, $data) {
    if (!$this->memcache) return null;
    return $this->memcache->add($key, $data, $this->expires);
  }

  public function delete($key) {
    if (!$this->memcache) return null;
    return $this->memcache->delete($key);
  }
  
  public function replace($key, $data){
    if (!$this->memcache) return null;
    return $this->memcache->replace($key, $data, $this->expires);
  }
  
  public function flush(){
    if (!$this->memcache) return null;
    return $this->memcache->flush();
  }
  
}