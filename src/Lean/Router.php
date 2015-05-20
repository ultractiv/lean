<?
/**
 * Created by PhpStorm.
 * User: agbetunsin
 * Date: 1/22/14
 * Time: 3:07 PM
 */

namespace Lean;
use ICanBoogie\Inflector;

class Router {

  // instance of application controller class
  protected $controller;

  // array of non RESTful routes, mapped to the controller methods to invoke
  protected $routes = array();

  private $path;
  private $request;
  private $controllerMethodPrefix;
  private $params = array();
  private $inflector;

  private $urlPatterns = array(
    'REST' => '#^/?(?<resource>\w+)(/(?<identifier>[a-z0-9]+)(/(?<sub_resource>\w+))?)?$#'
  );

  // bool flag to indicate whether a route was resolved or not
  private $_resolved = false;

  protected function init(){}

  public static function instance(){
    return new static;
  }

  protected function __construct(){

    $this->inflector = Inflector::get();

    if ( !class_exists('\Controller') )
      throw new ControllerException("No Controller class found in ".LEAN_APP_ROOT);
    
    $this->controller = \Controller::instance();

    $this->request = $_SERVER['REQUEST_METHOD'];

    $this->path = $_SERVER['PATH_INFO'];

    if (!$this->path && isset($_GET['path'])) $this->path = $_GET['path'];

    $this->init();

    $this->route();

    $this->controller->respond();

  }

  private function parseInputData(){
    $input = json_decode(file_get_contents("php://input"), true);
    if (is_array($input))
      $this->controller->extendPostData($input);
  }

  private function route(){

    switch ($this->request) {
      case 'POST':
        $this->controllerMethodPrefix = 'create';
        $this->parseInputData();
        break;
      case 'PATCH':
      case 'PUT':
        $this->controllerMethodPrefix = 'update';
        $this->parseInputData();
        break;
      case 'DELETE':
        $this->controllerMethodPrefix = 'destroy';
        break;
      case 'GET':
      case 'OPTIONS':
      case 'HEAD':
      default:
        $this->controllerMethodPrefix = 'get'; // instead of read
    }

    if ($this->path == '') {
      $this->_resolved = true;
      return $this->controller->index();
    }

    try {
      $this->match();
    }

    catch (\Exception $e) {
      try {
        if (($e instanceof RouterException) || ($e instanceof ControllerException) || ($e instanceof ModelException)) $this->matchRestRoutes();
        else echo $e->getMessage(); // application / internal server error
      }
      catch (\Exception $ex) {
        #if ($ex instanceof ControllerException) ; // $this->notImplement($ex); // or ->notFound()
        #else 
        echo $ex->getMessage(); // application / internal server error
      }
    }

    //if (!$this->_resolved) $this->notFound();

  }

  private function match(){

    # If non REST routes are defined in $this->routes

    if (!empty($this->routes)){
      
      foreach ($this->routes as $routePattern => $controllerMethod) {

        list($method, $pattern) = explode(' ', $routePattern);

        if ($method != $this->request) continue;
        
        if (!preg_match("#^/?{$pattern}$#i", $this->path)) {

          $pattern = preg_replace('#:([a-z_]+)#i','(?<$1>[a-z0-9\._]+)', $pattern);
          preg_match("#^/?{$pattern}$#", $this->path, $matches);

          if (!$matches) continue;

          if (count($matches) > 1) 
            $this->controller->setParams($matches);

        }

        if (!method_exists($this->controller, $controllerMethod))
          throw new ControllerException("$controllerMethod is not defined in app/controller.php", 1);

        $this->_resolved = true;

        return $this->controller->$controllerMethod();

      }

    }

    if ($this->_resolved !== true)
        
      $this->matchRestRoutes();
  }

  private function usePluralMethod(){
    return (!isset($this->params['id']) && !empty($this->params))
      || (empty($this->params) && $this->request == 'GET');
  }

  private function matchRestRoutes(){
    
    preg_match($this->urlPatterns['REST'], $this->path, $matches);

    if (!isset( $matches['resource'] )) throw new RouterException('REST route not matched, try nonREST');
    
    $resource = $this->inflector->singularize( $matches['resource'] );

    if (isset($matches['sub_resource']) && isset($matches['identifier'])) {
      $this->params[ $resource . '_id' ] = $matches['identifier'];      
      $resource = $this->inflector->singularize( $matches['sub_resource']);
    }
    else if (isset($matches['identifier'])) {
      $this->params['id'] = $matches['identifier'];
    }
    
    $method = ucfirst( $resource );

    if ($this->usePluralMethod())
    {      
      $method = $this->inflector->pluralize( $method );
    }

    $method = $this->controllerMethodPrefix . $method ;
    
    $this->controller->setParams($this->params);

    // Check first if Controller has $method
    // otherwise, if $this->autoCRUD is enable
    // try the magic crud resolver
    if (method_exists($this->controller, $method)) $this->controller->$method();
    else if ($this->controller->autoCRUD == true) {
      // use magic helper
      $method = $this->controllerMethodPrefix . 'Model';
      if ($this->usePluralMethod()) $method .= 's';
      $this->controller->$method( ucfirst( $resource ) );
    }

    # $this->_resolved = true;

  }

  # TODO: Move this to Controller or View class
  private function notFound(){
    // header('content-type: text/html', true, 404);
    header('content-type: text/json', true, 404);
    print json_encode("'/{$this->path}' was not found on this server");
  }

}

class RouterException extends \Exception {

}