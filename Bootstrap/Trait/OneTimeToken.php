<?php

namespace Slim\Bootstrap\Trait;

trait OneTimeToken {
  /**
   * トークン生成
   */
  protected function generate_token( $prefix, $limit = 48, $options = [] ) {
    $request = \Slim\Slim::getInstance()->request();
    $key = uniqid( $prefix );
    $value = md5(uniqid(rand(),1));
    $c_key = sprintf( "_%s_tkn_key", $prefix );
    $c_val = sprintf( "_%s_tkn_value", $prefix );
		$path     = ($options['path'])     ? $options['path'] : $request->getRootUri();
		$secure   = ($options['secure'])   ? $options['secure'] : false;
		$httponly = ($options['httponly']) ? $options['httponly'] : true;
    setcookie($c_key, $key,   mktime(date('H')+$limit), $path, $request->getHost(), $secure, $httponly);
    setcookie($c_val, $value, mktime(date('H')+$limit), $path, $request->getHost(), $secure, $httponly);
    return array($key,$value);
  }


  /**
   * トークンのチェック
   */
  protected function check_token( $prefix ) {
    $request = \Slim\Slim::getInstance()->request();
    $result = preg_grep('/^'.$prefix.'/', array_keys( $request->params()) );
    if( !empty( $result ) ) {
      $key = array_shift($result);
      $value = $request->params($key);
      $c_key = sprintf( "_%s_tkn_key", $prefix );
      $c_val = sprintf( "_%s_tkn_value", $prefix );
      $tkn_key = $_COOKIE[$c_key];
      $tkn_value = $_COOKIE[$c_val];
      if( $key == $tkn_key && $value == $tkn_value ) {
        return true;
      }
    }
    return false;
  }


	/**
	 * トークンの削除
	 **/
	protected function delete_token ( $prefix ) {
		setcookie(sprintf('_%s_tkn_key'), "", time() - 3600);
		setcookie(sprintf('_%s_tkn_value'), "", time() - 3600);
	}

}
