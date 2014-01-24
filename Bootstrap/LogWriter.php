<?php

namespace Slim\Bootstrap;

/**
 * カスタムログ書き込みクラス
 */
class LogWriter {

  use \Slim\Bootstrap\Trait\ChromeLog;

	protected $log_file_path;
	protected $log_file_name;
	protected $use_chrome_logger;
	protected $levels = array(
		0 => 'FATAL',
		1 => 'ERROR',
		2 => 'WARN',
		3 => 'INFO',
		4 => 'DEBUG'
		);

	public function __construct($log_dir = "logs", $log_file_name = "logger", $use_chrome_logger = true) {
		$this->log_file_path = $log_dir;
		$this->set_log_file_name($log_file_name);
		$this->use_chrome_logger = $use_chrome_logger;
	}

	public function set_log_file_name($log_file_name) {
		$this->log_file_name = $log_file_name . '.log';
	}

	public function write($message, $level_no) {
		$time = date('Y-m-d H:i:s');
		$level = $this->levels[$level_no];
		if (is_array($message)){
			$message = var_export( $message, true );
		} elseif($message instanceof \Exception) {
			$message = (string)$message;
		} elseif( is_object( $message ) ) {
			ob_start();
			print_r( $message );
			$message = ob_get_contents();
			ob_end_clean();
		} else {
			$message = (string)$message;
		}

    if (APP_ENV === 'development' && $this->use_chrome_logger) {
      $this->chrome_logger($message, $level_no);
    }

		$log = sprintf("[%s][%s] - %s\n", $time, $level, $message);

		error_log($log, 3, $this->log_file_path . DIRECTORY_SEPARATOR . $this->log_file_name);
	}

}
