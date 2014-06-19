<?
/**
 * Created by PhpStorm.
 * User: agbetunsin
 * Date: 1/22/14
 * Time: 3:07 PM
 */

namespace Lean;

class Router {

  // instance of application controller class
  protected $controller;

  // array of non RESTful routes, mapped to the controller methods to invoke
  protected $routes = array();

  private $path;
  private $request;
  private $controllerMethodPrefix;
  private $params = array();

  // bool flag to indicate whether a route was resolved or not
  private $_resolved = false;

  /*public static function instance(){
    return new self;
  }*/

  protected function init(){}

  public function __construct(){

    $this->path = trim($_SERVER['PATH_INFO'], '/');
    if (!$this->path) $this->path = trim($_GET['path'],'/');
    $this->request = $_SERVER['REQUEST_METHOD'];

    $this->controller = new \Controller;

    $this->init();

    if (!$this->controller)
      throw new RouterException("No controller found in the application");

    $this->route();

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

    try {
      $this->matchRESTRoutes();
    }
    catch (\Exception $e) {
      try {
        if (($e instanceof RouterException) || $e instanceof ControllerException) $this->matchNonRESTRoutes();
        else echo $e->getMessage(); // application / internal server error
      }
      catch (\Exception $ex) {
        if ($ex instanceof ControllerException) ; // $this->notImplement($ex); // or ->notFound()
        else echo $ex->getMessage(); // application / internal server error
      }
    }

    //if (!$this->_resolved) $this->notFound();

  }

  private function matchNonRESTRoutes(){
    if (!empty($this->routes)){
      foreach ($this->routes as $routePattern => $controllerMethod) {

        list($method, $pattern) = explode(' ', $routePattern);

        if ($method != $this->request) continue;

        $pattern = str_replace(':id','(?<id>[0-9]+)',$pattern);

        if (!preg_match("#^{$pattern}$#", $this->path, $matches)) continue;

        if (isset($matches['id']))
          $this->controller->setParams(array('id'=>$matches['id']));

        $this->controller->$controllerMethod();

        $this->_resolved = true;

        return $this->controller->respond();

      }
    }
  }

  private function matchRESTRoutes(){

    //TODO: Singularize and Pluralize resources in RESTful routes to map to singular and plural controller methods

    preg_match('#^(?<route>\w+)(/(?<param>[a-z0-9]+)(/(?<sub_route>\w+))?)?$#', $this->path, $matches);

    if (!isset( $matches['route'] )) throw new RouterException('Restful route not matched');

    // Singularize route if its plural
    $route = substr($matches['route'], 0, -1);

    if (isset($matches['sub_route']) && isset($matches['param'])) {
      $this->params[ $route . '_id' ] = $matches['param'];
      // Singularize subroute if its plural
      $route = substr($matches['sub_route'], 0, -1);
    }
    else if (isset($matches['param'])) {
      $this->params['id'] = $matches['param'];
    }

    $method = $this->controllerMethodPrefix  . ucwords( $route );

    if (( !isset($this->params['id']) && !empty($this->params))
      || (empty($this->params) && $this->request == 'GET'))
    {
      // pluralize method if its singular
      $method = $method . 's';
    }

    $this->controller->setParams($this->params);

    $this->controller->$method();

    $this->_resolved = true;

    return $this->controller->respond();

  }

  private function notFound(){
    //header('content-type: text/html', true, 404);
    header('content-type: text/json', true, 404);
    print json_encode("'/{$this->path}' was not found on this server");
  }

}


class RouterException extends \Exception {

}