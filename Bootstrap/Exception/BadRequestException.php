<?php

namespace Slim\Bootstrap\Exception;

/**
 * BadRequestException
 **/
class BadRequestException extends BaseException {

	public function __construct( $message = "Bad Request", $code = 400, \Exception $previous = null ) {
		parent::__construct( $message, $code, $previous );
	}

}
