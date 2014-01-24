<?php

namespace Slim\Bootstrap\Worker;

/**
 * 
 * 
 * @package 
 */
abstract class WorkerBase implements WorkerInterface{

	/**  */
	protected $logger;

	/**
	 * 
	 * 
	 * @access public
	 * @param $logger 
	 * @return 
	 */
	public function __construct($logger) {
		$this->logger = $logger;
		// 初期化処理
		if (file_exists('init.php')) {
		  include('init.php');
		} else {
			include(dirname(__FILE__).'/../init.php');
		}
	}

	/**
	 * runからコールされるメソッド
	 * 子クラスで必ず実装する
	 * 
	 */
	abstract protected function perform();

	/**
	 * 実行メソッド
	 * 
	 * @access public
	 * @param none 
	 * @return 
	 */
	public function run() {
		try {
			$this->perform();
		} catch (\Exception $e) {
			$this->error($e);
		}
	}

	/**
	 * 処理中にエラーが発生した場合の処理を記述
	 * 子クラスで実装
	 * 
	 * @access protected
	 * @param $e 
	 * @return 
	 */
	protected function error( \Exception $e) {
		$this->logger->debug($e);
	}

	/**
	 * 
	 * 
	 * @access protected
	 * @param none 
	 * @return 
	 */
	protected function alert() {
	}

}
