<?php
/**
 * HTTP Digest Authentication
 *
 * Use this middleware with your Slim Framework application
 * to require HTTP digest auth for all routes.
 *
 * Much of this code was created using <http://php.net/manual/en/features.http-auth.php>
 * as a reference. I do not claim ownership or copyright on this code. This
 * derivative class is provided under the MIT public license.
 *
 * @author Josh Lockhart <info@slimframework.com>
 * @author Samer Bechara <sam@thoughtengineer.com>
 * @version 1.0
 *
 * USAGE
 *
 * $app = new \Slim\Slim();
 * $app->add(new \Slim\Bootstrap\Middleware\HttpDigestAuth(array('user1' => 'password1', 'user2' => 'password2')));
 *
 * MIT LICENSE
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */
namespace Slim\Bootstrap\Middleware;

class HttpDigestAuth extends \Slim\Extras\Middleware\HttpDigestAuth {}
