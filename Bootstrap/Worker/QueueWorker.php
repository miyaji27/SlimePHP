<?php

namespace Slim\Bootstrap\Worker;

use \Slim\Bootstrap\Redis;

/**
 * この関数は未完成です
 *
 * @package
 */
abstract class QueueWorker extends WorkerBase {

	/**  */
	protected $queue = '';

	/**
	 *
	 *
	 * @access public
	 * @param $logger
	 * @param $queue_key
	 * @return
	 */
	public function __construct($logger, $queue_key=NULL) {
		parent::__construct($logger);

		// 引数 $queue_key に指定がない場合は子クラスを使用する
		$key = 0 < strlen($queue_key) ? $queue_key : $this->queue;
		$this->queue_name = 'queue:' . $key;
		$this->lock_queue = 'queue:' . $key . ':' . getmypid() . ':lock';
		$this->error_queue = 'queue:' . $key . ':error';

		$this->logger->debug( sprintf("Queue Name [%s]", $this->queue_name) );
	}

	/**
	 * redisからqueueを1件取得
	 *
	 * @access protected
	 * @param none
	 * @return
	 */
	protected function dequeue() {
		return RedisManager::getInstance()->rpoplpush($this->queue_name, $this->lock_queue);
	}

	/**
	 * redisからqueueを複数件取得
	 * 
	 * @access protected
	 * @param none 
	 * @return 
	 */
	protected function dequeues($count) {
		$pipe = RedisManager::getInstance()->multi(\Redis::PIPELINE);
		for( $i=0; $i<$count; $i++ ){
			$pipe->rpoplpush($this->queue_name, $this->lock_queue);
		}
		return $pipe->exec();
	}

	/**
	 * redisにqueueを挿入
	 *
	 * @access protected
	 * @param none
	 * @return
	 */
	protected function enqueue() {
	}

	/**
	 * redisにerror queueを挿入
	 *
	 * @access protected
	 * @param none
	 * @return
	 */
	protected function error_enqueue($data) {
		if (is_array($data)) {
			foreach($data as $val) {
				RedisManager::getInstance()->lpush($this->error_queue, $val);
			}
		} else {
			RedisManager::getInstance()->lpush($this->error_queue, $data);
		}
	}

	/**
	 * redisからlock queueを1件削除
	 *
	 * @access protected
	 * @param none
	 * @return
	 */
	protected function remove_lock_queue($data) {
		RedisManager::getInstance()->lrem($this->lock_queue, $data, 1);
	}

}
