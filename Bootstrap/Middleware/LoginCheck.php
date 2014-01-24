<?php
namespace Slim\Bootstrap\Middleware;

abstract class LoginCheck extends \Slim\Middleware {

	/**
	 * @var array
	 */
	protected $settings = array(
		'login.url' => '/',
		'auth.type' => 'http'
	);

	/**
	 * Constructor
	 *
	 * @return  void
	 */
	public function __construct(array $settings = array())
	{
		$this->settings = array_merge($this->settings, $settings);
	}

	/**
	 * Call
	 *
	 * @return void
	 */
	final public function call()
	{
		$this->before_filter();
		$this->filter();
		$this->after_filter();

		$this->next->call();
	}

	abstract public function before_filter();
	abstract public function after_filter();

	/**
	 * Form based authentication
	 *
	 * @param object $req
	 */
	protected function filter()
	{
		$req = $this->app->request();
		$settings = $this->settings;
		$app = $this->app;
		$this->app->hook('slim.before.router', function () use ($req, $settings, $app) {
			$callable = function() {return false;};
			if (isset($settings['security.checker']))
				$callable = $settings['security.checker'];
			if (isset($settings['security.all']) && is_bool($settings['security.all']) && $settings['security.all']) {
				if (!call_user_func($callable)) {
					if ($req->getPathInfo() === $settings['login.url']) {
						$app->redirect($app->request()->getUrl().$app->request()->getRootUri().$settings['login.url']);
					}
				}
				return;
			}
			$secured_urls = isset($settings['security.urls']) ? $settings['security.urls'] : array();
			foreach ($secured_urls as $surl) {
				$patternAsRegex = $surl['path'];
				if (substr($surl['path'], -1) === '/') {
					$patternAsRegex = $patternAsRegex . '?';
				}
				$patternAsRegex = '@^' . $patternAsRegex . '$@';

				if (preg_match($patternAsRegex, $req->getPathInfo())) {
					if (!call_user_func($callable)) {
					if ($req->getPathInfo() === $settings['login.url']) {
						$app->redirect($app->request()->getUrl().$app->request()->getRootUri().$settings['login.url']);
					}
					}
					break;
				}
			}
		});
	}

	protected function checker($callable) {
		if (is_callable($callable))
			$this->settings = array_merge($this->settings, array('security.checker'=>$callable));
	}

	protected function cleareChecker($callable) {
		unset($this->settings['security.checker']);
	}

}
