<?php

namespace Slim\Bootstrap;

/**
 * Log
 *
 * debug( mixed $object )
 * info( mixed $object )
 * warn( mixed $object )
 * error( mixed $object )
 * fatal( mixed $object )
 */
class Log {

	const FATAL = 0;
	const ERROR = 1;
	const WARN = 2;
	const INFO = 3;
	const DEBUG = 4;

	/**
	 * @var array
	 */
	static protected $levels = array(
		0 => 'FATAL',
		1 => 'ERROR',
		2 => 'WARN',
		3 => 'INFO',
		4 => 'DEBUG'
		);

	/**
	 * @var mixed
	 */
	protected $writer;

	/**
	 * @var bool
	 */
	protected $enabled;

	/**
	 * @var int
	 */
	protected $level;

	/**
	 * Constructor
	 * @param   mixed   $writer
	 * @return  void
	 */
	public function __construct( $writer ) {
		$this->writer = $writer;
		$this->enabled = true;
		$this->level = 4;
	}

	public function set_log_file_name( $log_file_name ) {
		$this->writer->set_log_file_name($log_file_name);
	}

	/**
	 * Is logging enabled?
	 * @return bool
	 */
	public function getEnabled() {
		return $this->enabled;
	}

	/**
	 * Enable or disable logging
	 * @param   bool    $enabled
	 * @return  void
	 */
	public function setEnabled( $enabled ) {
		if ( $enabled ) {
			$this->enabled = true;
		} else {
			$this->enabled = false;
		}
	}

	/**
	 * Set level
	 * @param   int $level
	 * @return  void
	 * @throws  InvalidArgumentException
	 */
	public function setLevel( $level ) {
		if ( !isset(self::$levels[$level]) ) {
			throw new InvalidArgumentException('Invalid log level');
		}
		$this->level = $level;
	}

	/**
	 * Get level
	 * @return int
	 */
	public function getLevel() {
		return $this->level;
	}

	/**
	 * Set writer
	 * @param   mixed $writer
	 * @return  void
	 */
	public function setWriter( $writer ) {
		$this->writer = $writer;
	}

	/**
	 * Get writer
	 * @return mixed
	 */
	public function getWriter() {
		return $this->writer;
	}

	/**
	 * Is logging enabled?
	 * @return bool
	 */
	public function isEnabled() {
		return $this->enabled;
	}

	/**
	 * Log debug message
	 * @param   mixed           $object
	 * @return  mixed|false     What the Logger returns, or false if Logger not set or not enabled
	 */
	public function debug( $object ) {
		return $this->log($object, 4);
	}

	/**
	 * Log info message
	 * @param   mixed           $object
	 * @return  mixed|false     What the Logger returns, or false if Logger not set or not enabled
	 */
	public function info( $object ) {
		return $this->log($object, 3);
	}

	/**
	 * Log warn message
	 * @param   mixed           $object
	 * @return  mixed|false     What the Logger returns, or false if Logger not set or not enabled
	 */
	public function warn( $object ) {
		return $this->log($object, 2);
	}

	/**
	 * Log error message
	 * @param   mixed           $object
	 * @return  mixed|false     What the Logger returns, or false if Logger not set or not enabled
	 */
	public function error( $object ) {
		return $this->log($object, 1);
	}

	/**
	 * Log fatal message
	 * @param   mixed           $object
	 * @return  mixed|false     What the Logger returns, or false if Logger not set or not enabled
	 */
	public function fatal( $object ) {
		return $this->log($object, 0);
	}

	/**
	 * Log message
	 * @param   mixed   The object to log
	 * @param   int     The message level
	 * @return  int|false
	 */
	protected function log( $object, $level ) {
		if ( $this->enabled && $this->writer && $level <= $this->level ) {
			return $this->writer->write($object, $level);
		} else {
			return false;
		}
	}
}
