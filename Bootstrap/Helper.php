<?php
namespace Slim\Bootstrap;

class Helper {

  protected $helpers;
  protected $helper_objects;

  public function __construct($helpers = array()) {
    $this->helpers = $helpers;
    $this->initialize();
  }

  public function __call($name, $args) {
    foreach ($this->helper_objects as $helper) {
      if (method_exists($helper, $name)) {
        return call_user_func_array([$helper, $name], $args); 
      }
    }
  }

  protected function initialize() {
    foreach ($this->helpers as $helper) {
      $this->helper_objects[] = new $helper();
    } 
  }
}
