<?php

namespace Slim\Bootstrap;

use \Slim\Bootstrap\Exception;

class Config {

	private static $instance = array();

	protected $config;

	public static function getInstance($key = "default") {
		if( !isset(self::$instance[$key]) || empty(self::$instance[$key]) ) {
			self::$instance[$key] = new self();
		}
		return self::$instance[$key];
	}

	public function __construct() {
		$this->config = array();
	}

	public function __get( $name ) {
		if (is_array($this->config) && isset($this->config[$name])) {
			return $this->config[$name];
		} else {
			return false;
		}
	}

	public function __toString() {
		return var_dump($this->config);
	}

	final public function load_config($file) {
		if (file_exists($file)) {
			$config = parse_ini_file($file, true);
			if (is_array($config)) {
				$this->config = array_merge($this->config, $config);
			}
		} else {
			throw new Exception\InvalidArgumentException('config file not founded : {$file}');
		}
	}

}
