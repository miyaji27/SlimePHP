<?php
namespace Slim\Bootstrap\Middleware;

abstract class ActiveRecord extends \Slim\Middleware {

	/**
	 * Call
	 *
	 * @return void
	 */
	final public function call()
	{
		// Check for Composer Package Autoloader class loading
		$this->initialize();
		$this->next->call();
	}

	abstract protected function initialize();
}
