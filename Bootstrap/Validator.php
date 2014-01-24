<?php
/**
 * Slim-Bootstrap
 *
 * @author      Daisuke Miyajima
 * @copyright   Copyright (c) 2014 Daisuke Miyajima
 * @package     Slim-Bootstrap
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
 *
 * USAGE
 *
 * ex1) runメソッドを実行してバリデートに引っかかるとfalseを返す
 * $validator = new Validator();
 * $validator->add('param_a', 'presence');
 * $validator->add('param_a', 'isbool');
 *
 * if (!$validator->run($params)) {
 *	var_dump($validator->get_errors());
 * }
 *
 *
 * ex2) コンストラクタにtrueを渡すとExceptionがthrowされる インスタンス化してからthrow_exceptionというプロパティにtrueをセットしてもいい
 * $validator = new Validator(true);
 * $validator->add('param_a', 'presence');
 * $validator->add('param_a', 'isbool');
 *
 * try {
 * 	$validator->run($params);
 * } catch (ValidateException $e) {
 * 	var_dump($e->errors());
 * }
 *
 *
 * ex3) throwする例外クラスをset_exception_classメソッドで指定できる、第2引数に例外クラスに渡すオプションを指定できる、なくてもいい
 * ※例外クラスはネームスペースをフルで指定する
 * $validator = new Validator(true);
 * $validator->set_exception_class('MyException', array('message', 1));
 * $validator->add('param_a', 'presence');
 * $validator->add('param_a', 'isbool');
 *
 * try {
 * 	$validator->run($params);
 * } catch (MyException $e) {
 * 	var_dump($e->getMesssage());
 * }
 *
 *
 * ex4) registerメソッドでバリデーションメソッドを登録できる
 * $validator = new Validator(true);
 * $validator->register('length_20', function ($value) {
 *   return (strlen($value) < 20) : true : false;
 * });
 * $validator->add('param_a', 'length_20');
 *
 */

namespace Slim\Bootstrap;

class Validator {

	public $throw_exception = false;

	protected $rule = array();
	protected $errors = array();
	protected $validation_methods = array();
	protected $exception_class = null;
	protected $exception_args = array();


	/**
	 * constructer
	 *
	 * @param Boolean $throw_exception
	 */
	public function __construct ($throw_exception=null) {
		if (!is_null($throw_exception)) {
			$this->throw_exception = $throw_exception;
		}
	}


	/**
	 * Exceptionクラスの指定
	 *
	 * @param String $class
	 * @param Array $args
	 */
	public function set_exception_class ($class, $args=array()) {
		$this->exception_class = $class;
		$this->exception_args = $args;
	}


	/**
	 * バリデーションルールの追加
	 *
	 * @param String field
	 * @param String method
	 * @param String options, optional
	 */
	public function add () {
		if (func_num_args() >= 2) {
			$args = func_get_args();
			$field = array_shift($args);
			$method = array_shift($args);
			$rule = array('method' => $method, 'options' => $args);
			$this->rule[$field][] = $rule;
		}
	}


	/**
	 * バリデーションメソッドの登録
	 * 登録するバリデーションメソッドはBooleanを返す
	 *
	 * @param String $name
	 * @param Closure $fn
	 */
	public function register ($name, $fn) {
		$this->validation_methods[$name] = $fn;
	}


	/**
	 * validation実行
	 *
	 * @param Array $params
	 */
	public function run ($params) {
		if (is_array($params)) {
			foreach ($this->rule as $field => $validators) {
				if (is_array($validators)) {
					foreach ($validators as $valid) {
						$value = (isset($params[$field])) ? $params[$field] : null;
						$method = $valid['method'];
						$options = array_merge(array($value), $valid['options']);

						if (method_exists(self, $method)) {
							$result = call_user_func_array(array(self, $method), $options);
						} elseif (array_key_exists($method, $this->validation_methods)) {
							$result = call_user_func_array($this->validation_methods[$method], $options);
						}

						if ($result === false) {
							$this->errors[$field] = $result;
						}
					}
				}
			}

			if ($this->throw_exception && !empty($this->errors)) {
				if (is_null($this->exception_class)) {
					throw new ValidateException($this->errors);
				} else {
					$Ex = $this->exception_class;
					if ($this->exception_args) {
						list($message, $code, $previous) = $this->exception_args;
						$message  = (is_null($message)) ? '' : $message;
						$code     = (is_null($code)) ? '' : $code;
						$previous = (is_null($previous)) ? '' : $previous;
						throw new $Ex($message, $code, $previous);
					} else {
						throw new $Ex();
					}
				}
			}

			return (empty($this->errors)) ? true : false;
		}
	}


	/**
	 * エラー結果取得
	 */
	public function get_errors () {
		return $this->errors;
	}


	/**
	 * 必須チェック
	 */
	final public static function presence ($value) {
		if( $value === "" || is_null( $value ) ){
			return false;
		}
		return true;
	}


	/**
	 * 日付型が判定
	 */
	final public static function isdatetime ($value, $format = 'Y-m-d H:i:s') {
		$timestamp = strtotime($value);
		if (false === $timestamp) {
			return false;
		}

		if (date($format, intval($timestamp)) != $value) {
			return false;
		}
		return true;
	}

	/**
	 * booleanかどうか判定
	 * 第2引数にtrueがわたされた場合は、1,0もbooleanとして判定する
	 *
	 * @param {value} $val
	 * @param boolean $num_flg
	 *
	 */
	final public static function isbool ($value, $num_flg = false) {
		if (is_null($value)) return true;

		$bool = false;

		if (is_bool($value)) {
			$bool = true;
		} else {
			if ($num_flg) {
				if (is_numeric($value)) {
					$value = intval($value);
					$bool =  ($value === 0 || $value === 1) ? true : false;
				}
			}
		}

		return $bool;
	}

	/**
	 * integer型チェック
	 */
	final public static function isnumeric ($value) {
		if (is_null($value) || $value === '') return true;
		return is_numeric($value);
	}

	/**
	 * 数値範囲チェック
	 *
	 * @param array $opt ( min => [最低数値], max => [最高数値] )
	 */
	final public static function numeric_size ($value, $opt = array('min' => 0, 'max' => 0)) {
		if (is_null($value) || $value == '') return true;
		if (!is_numeric($value)) return false;
		return (intval($value) < $opt['min'] || intval($value) > $opt['max']) ? false : true;
	}

	/**
	 * 文字数チェック
	 */
	final public static function str_length ($value, $opt = array('min' => 0, 'max' => 0)) {
		$cnt = mb_strlen($value);
		return ($cnt < $opt['min'] || $cnt > $opt['max']) ? false : true;
	}

	/**
	 * URL形式
	 */
	final public static function isurl ($value) {
		if (is_null($value) || $value == '') return true;
		if (!preg_match('/^(https?|ftp)(:\/\/[-_.!~*\'()a-zA-Z0-9;\/?:\@&=+\$,%#]+)$/', $value)) {
			return false;
		}
		return true;
	}

	/**
	 * メールアドレス形式
	 */
	final public static function ismail ($value) {
		return \Zend\Validator\EmailAddress::isValid($value);
	}

	/**
	 * 半角英数字( 記号付き )
	 */
	final public static function is_halfsize_string ($value) {
		if (is_null($value) || $value == '') return true;
		if (!preg_match('/^[a-zA-Z0-9!-~]+$/', $value)) {
			return false;
		}
		return true;
	}

	/**
	 * 日付の範囲チェック
	 */
	final public static function check_date_interval ($since, $to) {
		$since_obj = new \DateTime($since);
		$to_obj = new \DateTime($to);
		return ($since_obj >= $to_obj) ? false : true;
	}

	/**
	 * 与えられた配列の値のどれかにマッチするか
	 */
	final public static function isincluded ($value, $ary) {
		if (empty($value)) {
			return true;
		}
		if (!is_array($ary)) $ary = array($ary);
		return in_array($value, $ary, true);
	}
	
}

/**
 * バリデーション例外クラス
 */
class ValidateException extends \Exception {
	protected $errors;
	public function __construct ($errors) {
		$this->errors = $errors;
	}

	public function errors () {
		return $this->errors;
	}
}
