<?php

namespace Slim\Bootstrap\Middleware;

abstract class SessionRedis extends \Slim\Middleware
{
	// stores settings
	protected $settings;

	// stores redis object
	protected $redis;

	protected $session_stat = array();

	/**
	 * Constructor
	 *
	 * sets the settings to their new values or uses the default values
	 *
	 * @param (Array) $settings
	 * @return void
	 */
	public function __construct( $settings = array() )
	{
		// A neat way of doing setting initialization with default values
		$this->settings = array_merge(array(
			'session.name'		=> 'slim_session',
			'session.id'		=> md5(uniqid(mt_rand(),true)),
			'session.expires'	=> ini_get('session.gc_maxlifetime'),
			'cookie.lifetime'	=> 0,
			'cookie.path'		=> '/',
			'cookie.domain'		=> '',
			'cookie.secure'		=> false,
			'cookie.httponly'	=> true
		), $settings);

		// if the setting for the expire is a string convert it to an int
		if ( is_string($this->settings['session.expires']) )
			$this->settings['session.expires'] = intval($this->settings['session.expires']);

		// cookies blah!
		session_name($this->settings['session.name']);


		session_set_cookie_params(
			$this->settings['cookie.lifetime'],
			$this->settings['cookie.path'],
			$this->settings['cookie.domain'],
			$this->settings['cookie.secure'],
			$this->settings['cookie.httponly']
		);

		// overwrite the default session handler to use this classes methods instead
		session_set_save_handler(
			array($this, 'open'),
			array($this, 'close'),
			array($this, 'read'),
			array($this, 'write'),
			array($this, 'destroy'),
			array($this, 'gc')
		);
	}

	/**
	 * call
	 *
	 * slim imposed method, must call $this->next->call() or the middleware will stop in its tracks
	 *
	 * @return void
	 */
	public function call()
	{

		// start our session
		session_start();

		if (!session_id())
			session_id($this->settings['session.id']);

		// tell slim it's ok to continue!
		$this->next->call();
	}

	/**
	 * open
	 *
	 * creates a new connection with our redis server
	 *
	 * @return true
	 */
	abstract public function open( $session_path, $session_name );

	/**
	 * close
	 *
	 * @return true
	 */
	public function close()
	{
		$this->redis = null;
		return true;
	}


	/**
	 * read
	 *
	 * reads session data
	 *
	 * @return Array
	 */
	public function read( $session_id )
	{
		$key = "{$this->settings['session.name']}:{$session_id}";

		$session_data = $this->redis->get($key);
		if ( $session_data === NULL ) {
			return "";
		}
		$this->redis->session_stat[$key] = md5($session_data);

		return $session_data;
	}

	/**
	 * write
	 *
	 * writes session data
	 *
	 * @return True|False
	 */
	public function write( $session_id, $session_data )
	{
		$key = "{$this->settings['session.name']}:{$session_id}";
		$lifetime = $this->settings['session.expires'];

		//check if anything changed in the session, only send if has changed
		if ( !empty($this->redis->session_stat[$key]) && $this->redis->session_stat[$key] == md5($session_data) ) {
			//just sending EXPIRE should save a lot of bandwidth!
			$this->redis->setTimeout($key, $lifetime);
		} else {
			$this->redis->setex($key, $lifetime, $session_data);
		}

	}

	/**
	 * destroy
	 *
	 * destroys session
	 *
	 * @return true
	 */
	public function destroy( $session_id )
	{
		$this->redis->delete("{$this->settings['session.name']}:{$session_id}");
		return true;
	}

	/**
	 * gc
	 *
	 * garbage collection is performed using redis' internal timeout mechanism
	 *
	 * @return void
	 */
	public function gc(){}

	/**
	 * Destructor
	 *
	 * do things
	 *
	 * @return void
	 */
	public function __destruct()
	{
		session_write_close();
	}
}
