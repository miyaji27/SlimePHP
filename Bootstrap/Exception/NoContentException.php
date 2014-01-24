<?php

namespace \Slim\Bootstrap\Exception;

/**
 * NoContentException
 */
class NoContentException extends BaseException {

	public function __construct( $message = "No Content", $code = 204, \Exception $previous = null ) {
		parent::__construct( $message, $code, $previous );
	}

}
