<?php
/**
 * Slim-Bootstrap
 *
 * @author      Daisuke Miyajima
 * @copyright   Copyright (c) 2014 Daisuke Miyajima
 * @package     Slim-Bootstrap
 *
 * MIT LICENSE
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace Slim\Bootstrap;

/**
 * Redis 
 * phpredisのラッパークラス
 *
 * @package Slim-Bootstrap
 */
abstract class Redis {

  protected $con;

	protected $_host;
	protected $_port;

	/**
	 * コンストラクタ
	 *
	 * @access private
	 * @param none
	 * @return
	 */
	public function __construct($host = null, $port = null) {
    if ($host && $port) {
			$this->_host = $host;
			$this->_port = $port;
		  $this->con = RedisConnection::getConnection($this->_host, $this->_port);
		} else {
			$this->_host = null;
			$this->_port = null;
			$this->con = null;
		}
	}

	public function __call($method, $args) {
		return call_user_func_array(array($this->con, $method), $args);
	}

	public function __destruct() {
	}

	public final function __clone() {
		throw new RuntimeException ('Clone isn now allowd against '. get_class($this));
	}

}

class RedisConnection {
	
	private static $connection = array();

	/**
	 * コンストラクタ
	 *
	 * @access private
	 * @param none
	 * @return
	 */
	private function __construct() {
	}
	
	/**
	 * シングルトン取得
	 *
	 * @access public
	 * @param none
	 * @return
	 */
	public static function getConnection($host, $port) {
    	$key = sha1($host.$port);
		if (!isset(self::$connection[$key]) || empty(self::$connection[$key])) {
			self::$connection[$key] = new \Redis();
			self::$connection[$key]->connect($host, $port);
		}
		return self::$connection[$key];
	}

	public final function __clone() {
		throw new RuntimeException ('Clone isn now allowd against '. get_class($this));
	}

	public static function disconnect($host, $port) {
		$key = sha1($host.$port);
		self::$connection[$key]->close();
		unset(self::$connection[$key]);
	}
}
