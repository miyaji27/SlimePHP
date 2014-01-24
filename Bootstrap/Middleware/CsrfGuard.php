<?php
/**
 * CSRF Guard
 *
 * Use this middleware with your Slim Framework application
 * to protect you from CSRF attacks.
 *
 * USAGE
 *
 * $app = new \Slim\Slim();
 * $app->add(new \Bootstrap\Middleware\CsrfGuard());
 *
 */
namespace Slim\Bootstrap\Middleware;

class CsrfGuard extends \Slim\Extras\Middleware\CsrfGuard {}
