<?php

namespace Slim\Bootstrap;

abstract class AbstractController {

  use Module\Params;

  public $get;

  public $post;

  protected $app;

  protected $title = "";

  protected $logger;

  /**
   * @var array
   */
  protected $metas = array();

  /**
   * @var array Associative array of template variables
   */
  protected $data = array();

  /**
   * @var array
   */
  private $route_params = array();

  /**
   * @var bool
   */
  protected static $__secure = false;

  protected static $__allow_ssl = false;

  /**
   * @var array
   */
  protected static $before_filters = [];

  /**
   * @var array
   */
  protected static $after_filters = [];

  /**
   * @var \Slim Reference to the primary application instance
   */
  final public function __construct ($app) {
    $this->setApplication($app);
    $this->logger = $this->app->getLog();
    if (static::$__secure && $this->getScheme() === 'http') {
      $this->redirect("https://".$this->app->request()->getHost().$_SERVER['REQUEST_URI']);
    } else if (!static::$__secure && !static::$__allow_ssl &&  $this->getScheme() === 'https'){
      $this->redirect("http://".$this->app->request()->getHost().$_SERVER['REQUEST_URI']);
    }
    $this->route_params = $this->route_params();
    $this->get  = $this->get_params();
    $this->post = $this->post_params();
    $this->initialize();
  }

  abstract protected function initialize();

  /**
   * Set application
   *
   * @param  \Slim $application
   */
  final public function setApplication($application)
  {
    $this->app = $application;
  }

  /**
   * Get application
   *
   * @return \Slim
   */
  final public function getApplication()
  {
    return $this->app;
  }

  /**
   * Set Meta
   *
   * @param  string   $name       The meta name
   * @param  string   $value      The meta value
   */
  final public function meta($name, $value)
  {
    $this->metas[(string) $name] = $value;
  }

  /**
   * Get metas
   *
   * @param  string     $name     A meta name (Optional)
   * @return array|null
   */
  final public function getMetas($name = null)
  {
    if (!is_null($name)) {
      return isset($this->metas[(string) $name]) ? $this->metas[(string) $name] : null;
    } else {
      return $this->metas;
    }
  }

  /**
   * Clear metas
   *
   * @param  string   $name   A meta name (Optional)
   */
  final public function clearMetas($name = null)
  {
    if (!is_null($name) && isset($this->metas[(string) $name])) {
      unset($this->metas[(string) $name]);
    } else {
      $this->metas = array();
    }
  }

  /**
   * Set title
   *
   * @param  string   $title
   */
  final public function title($title = "")
  {
    $this->title = $title;
  }

  /**
   * Get title
   *
   * @return string 	$title
   */
  final public function getTitlte()
  {
    return $this->title;
  }


  /**
   * Redirect
   *
   * This method immediately redirects to a new URL. By default,
   * this issues a 302 Found response; this is considered the default
   * generic redirect response. You may also specify another valid
   * 3xx status code if you want. This method will automatically set the
   * HTTP Location header for you using the URL parameter.
   *
   * @param  string   $url        The destination URL
   * @param  int      $status     The HTTP redirect status code (optional)
   */
  final public function redirect($url, $status = 302) {
    $this->app->redirect($url, $status);
  }

  public function notFound() {
    $this->app->notFound();
  }

	public function notAuth() {
		throw new \Slim\Bootstrap\Exception\NotAuthorizedException();
	}

  public function flash($key, $message) {
    return $this->app->flash($key, $message);
  }

  public function flash_now($key, $message) {
    return $this->app->flashNow($key, $message);
  }

  public function config () {
    return Config::getInstance();
  }

  public function get_params () {
    return $this->get();
  }

  public function post_params () {
    return $this->post();
  }

  /********************************************************************************
   * Rendering
   *******************************************************************************/

  protected function helpers() {
  }

  /**
   * Render a template
   *
   * @param  string $template The name of the template passed into the view's render() method
   * @param  int    $status   The HTTP response status code to use (optional)
   */
  final public function render($template, $status = null) {
    $this->before_render();
    $helper = new Helper($this->helpers());
    $param = array_merge(array(
      'title'		=> $this->title,
      'meta'		=> $this->metas,
      'helper' => $helper,
      '_h' => $helper
    ),
    $this->data);
    try {
      $this->app->render($template, $param, $status);
    } catch (\Twig_Error_Loader $e) {
      $this->notFound();
    }
    $this->after_render();
  }

  final public function json($data) {
    $this->before_render();
    try {
      if (is_array($data) || $data instanceof \stdClass ) {
        $json = json_encode($data);

      } elseif(json_decode($data)) {
        $json = $data;
      } else {
        throw new \Exception();
      }
      header('Content-Type: application/json; charset=utf-8;');
      echo $json;
      exit;
    } catch (\Exception $e) {
      $this->notFound();
    }
    $this->after_render();
  }

  final public function jsonp($callback_name,$data) {
    $this->before_render();
    try {
      if (is_array($data) || $data instanceof \stdClass ) {
        $json = json_encode($data);

      } elseif(json_decode($data)) {
        $json = $data;
      } else {
        throw new \Exception();
      }
      header( 'Content-Type: text/javascript; charset=utf-8' );
      echo $callback_name . "(".$json.")";
      exit;
    } catch (\Exception $e) {
      $this->notFound();
    }
    $this->after_render();
  }

  public function fetch($template) {
    $this->before_render();
    $helper = new Helper($this->helpers());
    $param = array_merge(array(
      'title'		=> $this->title,
      'meta'		=> $this->metas,
      'helper' => $helper,
      '_h' => $helper
    ),
    $this->data);
    $this->getApplication()->view()->appendData($param);
    $content = $this->getApplication()->view()->fetch($template);
    $this->after_render();
    return $content;
  }

  public function before_render() {
  }

  public function after_render() {

  }


  /********************************************************************************
   * Params
   *******************************************************************************/

  /**
   * Get data
   * @param  string|null      $key
   * @return mixed            If key is null, array of template data;
   *                          If key exists, value of datum with key;
   *                          If key does not exist, null;
   */
  final public function getData($key = null)
  {
    if (!is_null($key)) {
      return isset($this->data[$key]) ? $this->data[$key] : null;
    } else {
      return $this->data;
    }
  }

  /**
   * Set data
   *
   * If two arguments:
   * A single datum with key is assigned value;
   *
   *     $view->setData('color', 'red');
   *
   * If one argument:
   * Replace all data with provided array keys and values;
   *
   *     $view->setData(array('color' => 'red', 'number' => 1));
   *
   * @param   mixed
   * @param   mixed
   * @throws  InvalidArgumentException If incorrect method signature
   */
  final public function setData()
  {
    $args = func_get_args();
    if (count($args) === 1 && is_array($args[0])) {
      $this->data = $args[0];
    } elseif (count($args) === 2) {
      $this->data[(string) $args[0]] = $args[1];
    } else {
      throw new \InvalidArgumentException('Cannot set View data with provided arguments. Usage: `View::setData( $key, $value );` or `View::setData([ key => value, ... ]);`');
    }
  }

  /**
   * Append new data to existing template data
   * @param  array
   * @throws InvalidArgumentException If not given an array argument
   */
  final public function appendData($data)
  {
    if (!is_array($data)) {
      throw new \InvalidArgumentException('Cannot append view data. Expected array argument.');
    }
    $this->data = array_merge($this->data, $data);
  }

  /**
   * Is this an AJAX request?
   * @return bool
   */
  public function isAjax()
  {
    return $this->getApplication()->request()->isAjax();
  }

  public function isPjax() 
  {
    if ($this->getApplication()->request()->params('ispjax')) {
      return true;
    } elseif (Util::isPjax()) {
      return true;
    } elseif ($this->getApplication()->request()->headers('X_PJAX') && $this->getApplication()->request()->headers('X_PJAX') === 'true') {
      return true;
    } else {
      return false;
    }

  }

  public function dispatch($method, $args = []) {
    $this->before_filter();
    call_user_func_array([$this, $method], $args);
    $this->after_filter();
  }

  public function before_filter() {
    foreach(static::$before_filters as $filter) {
      call_user_func([$this, $filter]);
    }
  }

  public function after_filter() {
    foreach(static::$after_filters as $filter) {
      call_user_func([$this, $filter]);
    }
  }

  protected function getRouteParams($key = null) {
    if ($key == null) return $this->route_params;
    if (array_key_exists($key, $this->route_params)) {
      return $this->route_params[$key];
    }
    return null;
  }

  public function getScheme() {
    return ($_SERVER['HTTP_X_FORWARDED_PROTO']) ? $_SERVER['HTTP_X_FORWARDED_PROTO'] : $this->app->request()->getScheme();
  }
}
