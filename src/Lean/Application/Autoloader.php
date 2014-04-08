<?php
/**
 * Created by PhpStorm.
 * User: agbetunsin
 * Date: 4/8/14
 * Time: 1:14 PM
 */

namespace Lean\Application {

  class Autoloader {

    public function __construct(){
      spl_autoload_register("autoload_lean_app_files");
    }

  }

}

namespace {

  function autoload_lean_app_files($className) {

    $appBase = LEAN_APP_ROOT ."/";
    $appModels = $appBase . "models/";
    $appControllers = $appBase . "controllers/";

    $file = strtolower($className) . ".php";

    if (is_readable( $appBase . $file ))
      require_once $appBase . $file;

    else if (is_readable( $appModels . $file ))
      require_once $appModels . $file;

    else if (is_readable( $appControllers . $file ))
      require_once $appControllers. $file;

  }

}