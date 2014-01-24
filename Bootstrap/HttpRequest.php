<?php

namespace Slim\Bootstrap;

/**
 * HttpRequest
 * 
 * @package 
 */
class HttpRequest {

	/**  */
	private $url;
	/**  */
	private $uri;
	/**  */
	private $method;
	/**  */
	private $host;
	/**  */
	private $port;
	/**  */
	private $timeout;
	/**  */
	private $content_type;
	/**  */
	private $authorization;
	/**  */
	private $contents;

	/**  */
	private $errno;
	/**  */
	private $errstr;

	/**  */
	private $respons_code;
	/**  */
	private $respons_message;
	/**  */
	private $respons_body;

	/**
	 * コンストラクタ
	 * 
	 * @access public
	 * @param none 
	 * @return 
	 */
	public function __construct(){
	}

	/**
	 * URL
	 * 
	 * @access public
	 * @param $url 
	 * @return 
	 */
	public function setUrl($url) {
		$this->url = $url;
	}

	/**
	 * URI
	 * 
	 * @access public
	 * @param $uri 
	 * @return 
	 */
	public function setUri($uri) {
		$this->uri = $uri;
	}

	/**
	 * メソッド
	 * 
	 * @access public
	 * @param $method 
	 * @return 
	 */
	public function setMethod($method) {
		$this->method = $method;
	}

	/**
	 * ポート
	 * 
	 * @access public
	 * @param $port 
	 * @return 
	 */
	public function setPort($port) {
		$this->port = $port;
	}

	/**
	 * タイムアウト
	 * 
	 * @access public
	 * @param $timeout 
	 * @return 
	 */
	public function setTimeout($timeout){
		$this->timeout = $timeout;
	}

	/**
	 * コンテンツタイプ
	 * 
	 * @access public
	 * @param $content_type 
	 * @return 
	 */
	public function setContentType($content_type) {
		$this->content_type = $content_type;
	}

	/**
	 * 認証
	 * 
	 * @access public
	 * @param $authorization 
	 * @return 
	 */
	public function setAuthorization($authorization) {
		$this->authorization = $authorization;
	}

	/**
	 * コンテンツ
	 * 
	 * @access public
	 * @param $contents 
	 * @return 
	 */
	public function setContents($contents) {
		$this->contents = $contents;
	}

	/**
	 * コンテンツ
	 * 
	 * @access public
	 * @param $contents 
	 * @return 
	 */
	public function addContents($contents) {
		if( 0 < strlen($this->contents) ){
			$this->contents = $this->contents . "&" . $contents;
		}
		else {
			$this->contents = $contents;
		}
	}

	/**
	 * レスポンスコード
	 * 
	 * @access public
	 * @param none 
	 * @return 
	 */
	public function getResponsCode() {
		return (int)$this->respons_code;
	}

	/**
	 * レスポンスメッセージ
	 * 
	 * @access public
	 * @param none 
	 * @return 
	 */
	public function getResponsMessage() {
		return $this->respons_message;
	}

	/**
	 * レスポンスボディ
	 * 
	 * @access public
	 * @param none 
	 * @return 
	 */
	public function getResponsBody() {
		return $this->respons_body;
	}

	/**
	 * クリア
	 * 
	 * @access private
	 * @param none 
	 * @return 
	 */
	private function responsDataClear() {
		$this->errno = null;
		$this->errstr = null;
		$this->respons_code = null;
		$this->respons_message = null;
		$this->respons_body = null;
	}

	/**
	 * リクエスト送信
	 * 
	 * @access public
	 * @param none 
	 * @return 
	 */
	public function sendRequest() {

		$this->responsDataClear();

		if( 0 == strlen($this->url) ){
			$this->getLog()->debug("Param Is Null. [oauth_token]");
			return false;
		}

		if( 0 == strlen($this->port) ){
			$this->port = 80;
		}

		$url_parts = parse_url($this->url);
		if( 0 ==strlen($this->host) ){
			$this->host = $url_parts['host'];
		}
		if( 0 ==strlen($this->uri) ){
			$this->uri = $url_parts['path'];
			if( isset($url_parts['query']) ){
				$this->uri .= "?".$url_parts['query'];
			}

		}

		$headers = array();
		$headers[] = sprintf("%s %s HTTP/1.1", ('get' == $this->method ? 'GET' : 'POST'), $this->uri);
		$headers[] = sprintf("Host: %s", $this->host);

		if( 0 < strlen($this->content_type) ){
			$headers[] = sprintf("Content-Type: %s", $this->content_type);
		}
		if( $this->authorization ){
			$headers[] = sprintf("Authorization: %s", $this->authorization);
		}
		if( 0 < strlen($this->contents) ){
			$headers[] = sprintf("Content-Length: %s", strlen($this->contents));
		}

		$headers[] = "Connection: Close";
		$headers = implode("\r\n",$headers) . "\r\n\r\n";
		if( 0 < strlen($this->contents) ){
			$headers .= $this->contents . "\r\n\r\n";
		}
		$fp  = fsockopen($this->host, $this->port, $this->errno, $this->errstr, $this->timeout);
		if( !(get_resource_type($fp) == 'stream')){
			$this->getLog()->debug( "Not stream." );
			return false;
		}
		if( !fwrite($fp, $headers) ){
			fclose($fp);
			$this-->getLog()->debug( "fwrite() Error." );
			return false;
		}
		$respons = '';
		while( !feof($fp) ){
			$respons .= fgets($fp, 128);
		}
		fclose($fp);
		$this->respons_body = $this->parseHttpResponse($respons);
	}

	/**
	 * レスポンスパース
	 * 
	 * @access private
	 * @param $content 
	 * @return 
	 */
	private function parseHttpResponse($content=null) {

		if( is_null($content) ){
			return false;
		}

		$hunks = explode("\r\n\r\n", trim($content));
		if( !is_array($hunks) || count($hunks) < 2) {
			return false;
		}

		$header  = $hunks[count($hunks) - 2];
		$body    = $hunks[count($hunks) - 1];
		$headers = explode("\n", $header);
		unset($hunks);
		unset($header);

		$this->verifyHttpResponse($headers);
		if( in_array('Transfer-Coding: chunked', $headers) ){
			return trim($this->unchunkHttpResponse($body));
		}
		else {
			return trim($body);
		}
	}

	/**
	 * レスポンス確認
	 * 
	 * @access private
	 * @param $headers 
	 * @return 
	 */
	private function verifyHttpResponse($headers=null) {

		if( !is_array($headers) || count($headers) < 1 ){
			return false;
		}

		$pattern = '/^HTTP\/1\.[1,0] (.*?) (.*)/';
		preg_match($pattern, trim($headers[0]), $matches);
		$this->respons_code = (int)$matches[1];
		$this->respons_message = $matches[2];

		return false;
	}

	/**
	 * チャンクドエンコーディングパース
	 * 
	 * @access private
	 * @param $str 
	 * @return 
	 */
	private function unchunkHttpResponse($str=null) {

		if( !is_string($str) || strlen($str) < 1 ){
			return false;
		}

		$eol = "\r\n";
		$add = strlen($eol);
		$tmp = $str;
		$str = '';
		do {
			$tmp = ltrim($tmp);
			$pos = strpos($tmp, $eol);
			if ($pos === false) {
				return false;
			}
			$len = hexdec(substr($tmp,0,$pos));
			if (!is_numeric($len) or $len < 0) {
				return false;
			}
			$str .= substr($tmp, ($pos + $add), $len);
			$tmp  = substr($tmp, ($len + $pos + $add));
			$check = trim($tmp);
		} while( !empty($check) );
		unset($tmp);

		return $str;
	}

	/**
	 * Log
	 * 
	 * @access protected
	 * @param none 
	 * @return 
	 */
	protected function getLog() {
		return $_ENV['app.log'];
	}
}

