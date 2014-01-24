<?php

namespace Slim\Bootstrap\Trait;

/**
 * Slimのparams関連を取得するtrait
 */
trait Params {

  public function request() {
    $app = \Slim\Slim::getInstance();
    return $app->request();
  }

  public function get($name=null) {
    $app = \Slim\Slim::getInstance();
    return (is_null($name)) ? $app->request()->get() : $app->request()->get($name);
  }

  public function post($name=null) {
    $app = \Slim\Slim::getInstance();
    return (is_null($name)) ? $app->request()->post() : $app->request()->post($name);
  }

  public function params($name=null) {
    $app = \Slim\Slim::getInstance();
    return (is_null($name)) ? $app->request()->params() : $app->request()->params($name);
  }

  public function current_route() {
    $app = \Slim\Slim::getInstance();
    return $app->router()->getCurrentRoute();
  }

  public function route_params() {
    $current_route = $this->current_route();
    return (is_null($current_route)) ? [] : $current_route->getParams();
  }

	/**
	 * Get the URL for a named route
	 * @param  string               $name       The route name
	 * @param  array                $params     Associative array of URL parameters and replacement values
	 * @throws \RuntimeException    If named route does not exist
	 * @return string
	 */
  public function urlFor($name, $params = array()) {
    $app = \Slim\Slim::getInstance();
    return $app->urlFor($name, $params); 
  }

}
