<?php
namespace Slim\Bootstrap\Exception;

/**
 * InvalidArgumentException
 **/
class InvalidArgumentException extends \Exception {

	public function __construct( $message = "Internal Server Error (InvalidArgumentException)", $code = 500, \Exception $previous = null ) {
		parent::__construct( $message, $code, $previous );
	}

	public function getStatusCode() {
		return $this->getCode();
	}

}
