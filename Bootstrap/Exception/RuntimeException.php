<?php

namespace Slim\Bootstrap\Exception;

/**
 * RuntimeException
 **/
class RuntimeException extends \Exception {

	public function __construct( $message = "Internal Server Error (RuntimeException)", $code = 500, \Exception $previous = null ) {
		parent::__construct( $message, $code, $previous );
	}

	public function getStatusCode() {
		return $this->getCode();
	}

}
