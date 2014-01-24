<?php
namespace Slim\Bootstrap\Exception;

/**
 * ForbiddenException
 **/
class ForbiddenException extends BaseException {

	public function __construct( $message = "Forbidden", $code = 403, \Exception $previous = null ) {
		parent::__construct( $message, $code, $previous );
	}

}
