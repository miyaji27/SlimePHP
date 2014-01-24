<?php

namespace Slim\Bootstrap\Exception;

/**
 * NotFoundException
 **/
class NotFoundException extends BaseException {

	public function __construct( $message = "Not Found", $code = 404, \Exception $previous = null ) {
		parent::__construct( $message, $code, $previous );
	}

}
