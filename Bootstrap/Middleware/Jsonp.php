<?php
/**
 * JSONP Middleware Class for the Slim Framework
 *
 * @author  Tom van Oorschot <tomvanoorschot@gmail.com>
 * @since  17-12-2012
 *
 * Simple class to wrap the response of the application in a JSONP callback function.
 * The class is triggered when a get parameter of callback is found   
 *
 * Usage
 * ====
 * 
 * $app = new \Slim\Bootstrap();
 * $app->add(new \Slim\Bootstrap\Middleware\JSONPMiddleware());
 * 
 */

namespace Slim\Bootstrap\Middleware;

class JSONPMiddleware extends \Slim\Extras\Middleware\JsonpMiddleware {}
