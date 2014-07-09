<?

/**
 * Created by PhpStorm.
 * User: agbetunsin
 * Date: 4/5/14
 * Time: 9:23 PM
 * Single controller class that encapsulates all actions:
 * All CRUD ops and much more
 */

namespace Lean;

class Controller {

  protected $data = array();
  protected $get = array();
  protected $file = array();
  protected $params = array(); // request params
  protected $session = array();

  protected $responseType = 'json';
  protected $responseData = array();

  protected $err = false;

  # enable to automatically process RESTful CRUD request
  # when matching models are defined for the requested routes
  protected $autoCRUD = false;

  protected function init(){}

  public function __call($method, $args){
    if (method_exists($this, $method)) $this->$method($args);
    else throw new ControllerException(__CLASS__." has no '{$method}' method");
  }

  public function __construct() {

    session_start();

    $this->data = $_POST;
    $this->get = $_GET;
    $this->file = $_FILES;
    $this->session = $_SESSION;

    $this->init();

  }

  public function setParams(array $params){
    $this->params = $params;
  }

  public function extendPostData(array $data){
    $this->data = array_merge($this->data, $data);
  }

  public function respond(){
    switch ($this->responseType){
      case 'iframe-html':
        header('content-type: text/html');
        print '<textarea data-type="application/json">';
        print json_encode(!$this->err ? $this->responseData : array('error'=>$this->err));
        print '</textarea>';
        break;
      case 'html':
        header('content-type: text/html');
        print !$this->err ? $this->responseData : $this->err;
        break;
      case 'json':
      default:
        if (!$this->err) {
          header('content-type: text/json');
          return print json_encode($this->responseData);
        }
        header('content-type: text/json', true, 400);
        print json_encode($this->err);
    }

  }

  protected function dataOnly($fields = ''){
    $fields = explode(' ', $fields);
    $data = array();
    foreach($fields as $field)
      if (isset($this->data[$field]))
        $data[$field] = $this->data[$field];
    $this->data = $data;
  }

  /* Magic REST controller methods */

  public function getModel($modelClass){

    if (!class_exists($modelClass))
      throw new ControllerException('Model '.$modelClass.' class does not exist');

    $instance = $modelClass::get($this->params['id']);
    if (!$instance) return $this->err = "No such {$modelClass}";
    return $this->responseData = $instance->attrs();
  }

  public function getModels($modelClass){

    if (!class_exists($modelClass))
      throw new ControllerException('Model '.$modelClass.' class does not exist');

    return $this->responseData = $modelClass::all();
  }

  public function createModel($modelClass){

    if (!class_exists($modelClass))
      throw new ControllerException('Model '.$modelClass.' class does not exist');

    $instance = $modelClass::create($this->data);
    if (!$instance->isValid())
      return $this->err = $instance->getValidationError();
    return $this->responseData = $instance->attrs();
  }

  public function updateModel($modelClass){

    if (!class_exists($modelClass))
      throw new ControllerException('Model '.$modelClass.' class does not exist');

    $instance = $modelClass::get($this->params['id']);
    if (!$instance) return $this->err = "No such {$modelClass}";
    if (! $instance->save($this->data) )
      return $this->err = $instance->getValidationError();
    return $this->responseData = $instance->attrs();
  }

  public function destroyModel($modelClass){

    if (!class_exists($modelClass))
      throw new ControllerException('Model '.$modelClass.' class does not exist');

    $instance = $modelClass::get($this->params['id']);
    if (!$instance)
      return $this->err = "No such {$modelClass}";
    $instance->destroy();
  }

}

class ControllerException extends \Exception {

}