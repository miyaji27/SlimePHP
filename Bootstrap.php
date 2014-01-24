<?php

namespace Slim;

class Bootstrap
{

	/**
	 * @const string
	 */
	const VERSION = '0.1.0';

	// System Start Time
	define('START_TIME', microtime(true));

	// System Start Memory
	define('START_MEMORY_USAGE', memory_get_usage());

	// Is this an AJAX request?
	define('AJAX_REQUEST', strtolower(getenv('HTTP_X_REQUESTED_WITH')) === 'xmlhttprequest');

	// The current TLD address, scheme, and port
	define('DOMAIN', (strtolower(getenv('HTTPS')) == 'on' ? 'https' : 'http') . '://'
		. getenv('HTTP_HOST') . (($p = getenv('SERVER_PORT')) != 80 AND $p != 443 ? ":$p" : ''));

	// The current site path
	define('PATH', parse_url(getenv('REQUEST_URI'), PHP_URL_PATH));

	// application environment
	define('APP_ENV', getenv('SLIM_MODE') ? getenv('SLIM_MODE') : 'development');

	// The application root path
	define('APP_ROOT_PATH', dirname(dirname(__FILE__)));

	// iconv encoding
	iconv_set_encoding("internal_encoding", "UTF-8");

	// multibyte encoding
	mb_internal_encoding('UTF-8');

	// Default timezone of server
	date_default_timezone_set('Asia/Tokyo');

	// erorr reporting
	error_reporting(E_STRICT);

	define('PHP_ACTIVERECORD_AUTOLOAD_PREPEND',false);
	require 'vendor/autoload.php'; // composer autoloder
	use \Core\Exception;

	// Enable global error handling
	set_error_handler(function($errno, $errstr, $errfile, $errline, $errcontext) {
		throw new Exception\BaseException($errstr, $errno);
	});

	register_shutdown_function(function(){
		$isError = false;
		if ($e = error_get_last()){
			switch($e['type']){
				case E_ERROR:
				case E_PARSE:
				case E_CORE_ERROR:
				case E_CORE_WARNING:
				case E_COMPILE_ERROR:
				case E_COMPILE_WARNING:
				case E_STRICT:
				$isError = true;
				break;
			}
		}
		if ($isError){
			var_dump(error_get_last());
			exit;
		}
	});

	set_exception_handler(function($e) {
		echo "Uncaught exception: " , $e->getMessage(), "\n";
	});

	// twig setting
	require_once dirname(__FILE__) . '/vendor/slim/extras/Slim/Extras/Views/Extension/TwigAutoloader.php';

	Extras\Views\Twig::$twigDirectory = dirname(__FILE__).'/vendor/twig/twig/lib/Twig';
	Extras\Views\Twig::$twigOptions = array();
  Extras\Views\Twig::$twigExtensions = array(
    'Twig_Extensions_Slim',
    'Core\Twig\Extensions\TwigUrlExtension', 
    '\Twig_Extensions_Extension_I18n', 
    '\Twig_Extensions_Extension_Text'
    // '\Twig_Extensions_Extension_Intl'
  );
	Bootstrap\Middleware\ActiveRecord::$phpARDirectory = dirname(__FILE__).'/vendor/php-activerecord/php-activerecord';

	Slim::registerAutoloader();
}
