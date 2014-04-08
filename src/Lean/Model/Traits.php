<?

namespace Lean\Model;

trait Traits {

  protected static $notifyOnCreate = false;
  
  public static function get($id) {
    $instance = new self ( $id );
    if ($instance->id)
      return $instance;
  }
  
  public static function all(){
    $instance = new self ();
    return $instance->findAll ();
  }

  public static function where(array $attrs, $asObjects = false) {
    $instance = new self ();
    return $instance->findMany($attrs, $asObjects);
  }

  protected static function getClassName(){
    return __CLASS__ ;
  }

  public static function instantiate(array $attrs){
    return new self ($attrs);
  }

  public static function create(array $attrs){
    $instance = new self($attrs);
    if ($instance->isValid()) {
      $instance->save($attrs);
      if (self::$notifyOnCreate) $instance->notifier->notifyOnCreate ( $instance->attrs () );
    }
    return $instance;
  }
  
}