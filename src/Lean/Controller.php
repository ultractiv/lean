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

  private $view;
  protected $html;

  protected $responseType = 'json';
  protected $responseData = array();

  protected $_flashData = array();

  protected $err = false;

  # array or $resourceName => $modelClassName mappings
  # for RESTful resources that do not resolve to models
  # with the same name
  protected $resourceMappings = array();

  # enable to automatically process RESTful CRUD request
  # when matching models are defined for the requested routes
  public $autoCRUD = false;

  protected function init(){}

  public static function instance(){
    return new static;
  }

  public function __call($method, $args){
    if (method_exists($this, $method)) $this->$method($args);
    else throw new ControllerException(__CLASS__." has no '{$method}' method");
  }

  protected function __construct() {

    session_start();

    $this->view = View::instance();

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
        print $this->html;
        # print !$this->err ? $this->responseData : $this->err;
        break;
      case 'json':
      default:
        if (!$this->err) {
          header('content-type: text/json');
          return print json_encode($this->responseData);
        }
        header('content-type: text/json', true, 400);
        print json_encode($this->err);
        break;
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

  private function checkModelExists($modelClass) {
    if (array_key_exists($modelClass, $this->resourceMappings)) {
      $modelClass = $this->resourceMappings[$modelClass];
    }
    if (!class_exists($modelClass))
      throw new ControllerException('Model '.$modelClass.' class does not exist in '.LEAN_APP_ROOT.'/models');
    return $modelClass;
  }

  /* Magic REST API controller methods */
  public function getModel($modelClass){
    $modelClass = $this->checkModelExists($modelClass);
    $instance = $modelClass::get($this->params['id']);    
    return $this->responseData = $instance->attrs();
  }

  public function getModels($modelClass){
    $modelClass = $this->checkModelExists($modelClass);
    return $this->responseData = $modelClass::all();
  }

  public function createModel($modelClass){
    $modelClass = $this->checkModelExists($modelClass);
    $instance = $modelClass::create($this->data);
    if (!$instance->isValid())
      return $this->err = $instance->getValidationError();
    return $this->responseData = $instance->attrs();
  }

  public function updateModel($modelClass){
    $modelClass = $this->checkModelExists($modelClass);
    $instance = $modelClass::get($this->params['id']);    
    if (! $instance->save($this->data) )
      return $this->err = $instance->getValidationError();
    return $this->responseData = $instance->attrs();
  }

  public function destroyModel($modelClass){
    $modelClass = $this->checkModelExists($modelClass);
    $instance = $modelClass::get($this->params['id']);    
    $instance->destroy();
  }

  protected function viewData(){
    return array();
  }

  protected function setFlashData($data){
    $_SESSION['_flashData'] = $data;
  }

  protected function getFlashData(){
    return (isset($_SESSION['_flashData'])) ? $_SESSION['_flashData'] : array();
  }

  /**
   * Rendering HTML views from templates
   */
  protected function render($template, $data = array()){
    $this->responseType = 'html';
    $data = array_merge( $this->viewData(), $this->getFlashData(), $data );
    $this->html = $this->view->render($template, $data);
    if (isset($_SESSION['_flashData'])) unset($_SESSION['_flashData']);
  }

  protected function _400(){
    $this->render('400');
  }

  protected function _500(){
    $this->render('500');
  }

  protected function redirect($location = '', $data){
    
    return header( 'location: '. $location ) && $this->setFlashData($data);
  }

  protected function redirectBack($data){
    return $this->redirect($_SERVER['HTTP_REFERER'], $data);
  }

  /**
   * Implement the index route by default
   */
  public function index(){
    $this->render('index');
  }

}

class ControllerException extends \Exception {

}