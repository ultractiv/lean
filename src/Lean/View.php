<?

namespace Lean;

class View {

  private $twig;

  public static function instance(){
    return new static;
  }

  private function host(){
    $protocol = 'http://';
    if (!preg_match('#^HTTP/#i', $_SERVER['SERVER_PROTOCOL'])) $protocol = 'https://';
    return $protocol . HOST ;
  }

  private function __construct(){
    if (!is_dir(LEAN_APP_ROOT.'/views')) throw new ViewException("Can't initialize views", 1);
    $loader = new \Twig_Loader_Filesystem(LEAN_APP_ROOT.'/views');
    $this->twig = new \Twig_Environment($loader, array(
      'cache' => preg_replace('#/app$#', '/tmp', LEAN_APP_ROOT)
    ));
  }

  public function render($template, $data = array()){
    if (!preg_match('#\.html$#', $template)) {
      $template .= '.html';
    }    
    $tmpl = $this->twig->loadTemplate($template);
    # extend data with base_url variable
    $data['base_url'] = $this->host();
    $data['home_url'] = $this->host() . '/';
    return $tmpl->render($data);
  }

}

class ViewException extends \Exception {

}