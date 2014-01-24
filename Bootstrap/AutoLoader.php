<?php

namespace Slim\Bootstrap;

require_once dirname(__FILE__).'/Exception/BaseException.php';

use \Slim\Bootstrap\Exception;

class AutoLoader {

	public static function namespace_loader($class) {
		$root_namespace = substr(__NAMESPACE__, 0, strpos(__NAMESPACE__,'\\'));
		$class = str_replace($root_namespace, '', $class);
		$file_path = sprintf('%s/../%s.php', dirname(__FILE__), str_replace('\\', '/', $class));
		if (file_exists($file_path)) {
			require_once($file_path);
		}
	}

}
