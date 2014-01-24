<?php

namespace Slim\Bootstrap\Exception;

/**
 * NotAuthorizedException
 **/
class NotAuthorizedException extends BaseException {

	public function __construct( $message = "Not Authorized", $code = 401, \Exception $previous = null ) {
		parent::__construct( $message, $code, $previous );
	}

}
