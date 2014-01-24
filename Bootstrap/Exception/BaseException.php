<?php
namespace Slim\Bootstrap\Exception;

/**
 * BaseException
 **/
class BaseException extends \Exception {

	public function __construct( $message = "Internal Server Error ", $code = 500, \Exception $previous = null ) {
		parent::__construct( $message, $code, $previous );
	}

	public function getStatusCode() {
		return $this->getCode();
	}

}
