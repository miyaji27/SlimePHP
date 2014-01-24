<?php

namespace Slim\Bootstrap\Exception;

/**
 * MethodNotAllowedException
 **/
class MethodNotAllowedException extends BaseException {

	public function __construct( $message = "Method Not Allowed", $code = 405, \Exception $previous = null ) {
		parent::__construct( $message, $code, $previous );
	}

}
