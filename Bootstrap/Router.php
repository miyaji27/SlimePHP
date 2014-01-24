<?php

namespace Slim\Bootstrap;

abstract class Router {

  public function __construct($app) {
    $this->app = $app;
  }

  abstract public function dispatch();

  protected function action_callback($controller,$action) {
    $app = $this->app;
    return function () use($app, $controller, $action) {
      $args = func_get_args();
      $c = new $controller($app);
      $c->dispatch($action, $args);
    };
  }

  protected function get($path, $controller, $action) {
    return $this->app->get($path, $this->action_callback($controller, $action));
  }

  protected function post($path, $controller, $action) {
    return $this->app->post($path, $this->action_callback($controller, $action));
  }

  protected function put($path, $controller, $action) {
    return $this->app->put($path, $this->action_callback($controller, $action));
  }

  protected function delete($path, $controller, $action) {
    return $this->app->delete($path, $this->action_callback($controller, $action));
  }

  protected function map($path, $controller, $action) {
    return $this->app->map($path, $this->action_callback($controller, $action));
  }

  /**
   * RESTアクションを生成する
   *
   * オプションでonlyとexceptを配列で指定できる
   * 両方指定されていた場合は、onlyが優先される
   *
   * ex)
   * $controller = '\Path\To\Controller\SomeController'
   * // 基本
   * $this->rest_actions($controller);
   * // indexアクションのみ
   * $this->rest_actions($controller, ['only' => ['index']]);
   * // destroy 以外
   * $this->rest_actions($controller, ['except' => ['destroy']]);
   *
   */
  protected function rest_actions($controller, $options = []) {
    $default_options = ['only' => [], 'except' => []];
    $options = array_merge($default_options, $options);

    $path = explode("\\", $controller);
    $controller_class = array_pop($path);
    $name = Util::to_snakecase(str_replace('Controller','',$controller_class));

    $allow_action = function ($action, $options) {
      return function () use ($action,$options) {
        $is_only = !empty($options['only']);
        $is_except = !empty($options['except']);
        if ($is_only) {
          if (false !== array_search($action, $options['only'])) {
            return true;
          } 
        } elseif($is_except) {
          if (false === array_search($action, $options['except'])) {
            return true;
          }
        } else {
          return true;
        }
        return false;
      };
    };

    // index
    if ($allow_action('index', $options)) {
      $this->get('/'.$name, $controller,'index_action')->name('index_'.$name); 
    }
    // new
    if ($allow_action('new', $options)) {
      $this->get('/'.$name.'/new', $controller, 'new_action')->name('new_'.$name); 
    }
    // craete
    if ($allow_action('create', $options)) {
      $this->post('/'.$name, $controller, 'create_action')->name('create_'.$name); 
    }
    // edit
    if ($allow_action('edit', $options)) {
      $this->get('/'.$name.'/:id/edit', $controller, 'edit_action')->name('edit_'.$name); 
    }
    // update
    if ($allow_action('update', $options)) {
      $this->put('/'.$name.'/:id', $controller, 'update_action')->name('update_'.$name); 
    }
    // show
    if ($allow_action('show', $options)) {
      $this->get('/'.$name.'/:id', $controller, 'show_action')->name('show_'.$name); 
    }
    // destroy
    if ($allow_action('destroy', $options)) {
      $this->delete('/'.$name.'/:id', $controller, 'destroy_action')->name('destroy_'.$name); 
    }
  }
}
