<?

namespace Lean\Model;

trait Traits {

  public static function get($id) {
    $instance = new static( $id );
    if (!$instance->id) throw new Exception(static::getClassName() . " with id=" .$id. " does not exist.");
    return $instance;
  }

  public static function find(array $attrs){
    $instance = new static;
    if ($instance->findOne ($attrs)) return $instance;
  }
  
  public static function all(){
    $instance = new static;
    return $instance->findAll ();
  }

  public static function where(array $attrs, $asObjects = false) {
    $instance = new static;
    return $instance->findMany($attrs, $asObjects);
  }

  protected static function getClassName(){
    return __CLASS__ ;
  }

  public static function instantiate(array $attrs){
    return new static($attrs);
  }

  public static function create(array $attrs){
    $instance = new static($attrs);
    $instance->beforeCreate($attrs);
    if ($instance->isValid() && $instance->save($attrs))
      $instance->afterCreate();
    return $instance;
  }

}