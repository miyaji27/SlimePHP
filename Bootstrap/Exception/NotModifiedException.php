<?php

namespace Slim\Bootstrap\Exception;

/**
 * NotModifiedException
 */
class NotModifiedException extends BaseException {

	public function __construct( $message = "Not Modified", $code = 304, \Exception $previous = null ) {
		parent::__construct( $message, $code, $previous );
	}

}
