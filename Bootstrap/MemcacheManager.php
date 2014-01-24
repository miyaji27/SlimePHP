<?php

namespace Slim\Bootstrap;

/**
 * Memcachemanager
 *
 * php-pecl-memcachedのラッパークラス
 *
 * @package
 */
abstract class MemcacheManager {

	/**  */
	private static $singleton;
	/**  */
	private $memcache;

	/**
	 * コンストラクタ
	 *
	 * @access private
	 * @param array
	 * @return
	 */
	private function __construct() {
		$servers = $this->getServers();
		if (is_array($servers) && !empty($servers)) {
			$this->memcache = new \Memcached();
			$this->memcache->addServers($servers);
		} else {
			$this->memcache = null;
		}
	}

	/**
	 * シングルトン取得
	 *
	 * @access public
	 * @param array 
	 * @return
	 */
	public static function getInstance() {
		if (!is_object(self::$singleton)) {
			self::$singleton = new MemcacheManager();
		}
		return self::$singleton;
	}

	/**
	 * プロパティの取得
	 * ここではmemcachedに問い合わせてキャッシュ値を返す
	 *
	 * @access public
	 * @param $key
	 * @return
	 */
	public function _get($key) {
		if (is_array($Key)) {
			//配列が渡された時は複数取得
			return $this->getMulti($key);
		} else {
			//配列でない場合は直接問い合わせる
			return $this->get($key);
		}
	}

	/**
	 * アイテムの取得
	 *
	 * @access public
	 * @param $key
	 * @return
	 */
	public function get($key) {
		if (!is_null($this->memcache))
			return $this->memcache->get($key);
		else return false;
	}

	/**
	 * 複数のアイテムを取得
	 *
	 * @access public
	 * @param $keys
	 * @return
	 */
	public function getMulti(array $keys) {
		if (!is_null($this->memcache))
			return $this->memcache->getMulti($keys);
		else return false;
	}

	/**
	 * アイテムを格納する
	 *
	 * @access public
	 * @param $key
	 * @param $value
	 * @param $exp
	 * @return
	 */
	public function set($key, $value, $expiration = 0) {
		$expiration = (int)$expiration;
		if ($expiration <= 0 || $key == null || $value == null) return false;
		if (!is_null($this->memcache))
			return $this->memcache->set($key, $value, $expiration);
		else return false;
	}

	/**
	 * アイテムを削除する
	 *
	 * @access public
	 * @param $key
	 * @return
	 */
	public function remove($key) {
		if (!is_null($this->memcache))
			$this->memcache->delete($key);
		else return false;
	}

	/**
	 * キャッシュ内のすべてのアイテムを無効にする
	 * 危険なので基本的に使わないこと
	 *
	 * @access public
	 * @param $delay
	 * @return
	 */
	public function removeAll($delay = 0) {
		if (!is_null($this->memcache))
			$this->memcache->flush($delay);
		else return false;
	}

	/**
	 *
	 *
	 * @access public
	 * @param none
	 * @return
	 */
	public final function __clone() {
		throw new RuntimeException ('Clone isn now allowd against '. get_class($this));
	}

	abstract protected function getServers();
}
