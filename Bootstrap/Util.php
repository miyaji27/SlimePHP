<?php

namespace Slim\Bootstrap;

abstract class Util {

	/**
	 * salt+sha1
	 * @param String $text
	 * @param String $salt
	 */
	public static function salt_sha1( $text , $salt) {
		return sha1($salt.$text);
	}
	/**
	 * AES暗号化
	 * 暗号化
	 * @param $test 文字列
	 * @param $key 暗号化キー
	 */
	public static function aes_encrypt( $text, $key ) {
		$size = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
		// $text = self::pkcs5_pad($text,16);  // AESは16バイトずつ暗号化
		$text = self::pkcs5_pad($text,$size);  // AESは16バイトずつ暗号化
		srand();
		$iv = mcrypt_create_iv($size, MCRYPT_RAND);
		$bin = pack('H*', bin2hex($text) );
		$encrypted = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $bin, MCRYPT_MODE_CBC, $iv);
		return base64_encode($iv.$encrypted);
	}

	/**
	 * AES復号化
	 * @param $data 暗号化データ
	 * @param $key 暗号化キー
	 */
	public static function aes_decrypt( $data, $key ) {
		$size = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
		$data = base64_decode($data);
		$iv = substr($data,0,$size);
		$text = substr($data,$size);
		$bin = pack('H*', bin2hex( $text ) );
		$decrypted = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $bin, MCRYPT_MODE_CBC, $iv);
		return rtrim( self::pkcs5_unpad($decrypted) );
	}


  public static function mysql_aes_key($key)
  {
    $new_key = str_repeat(chr(0), 16);
    for($i=0,$len=strlen($key);$i<$len;$i++)
    {
      $new_key[$i%16] = $new_key[$i%16] ^ $key[$i];
    }
    return $new_key;
  }

  /**
   * AES ECBモードの暗号化
   * @param $data 暗号化データ
   * @key $key 暗号化キー
   */
  public static function mysql_aes_encrypt( $text, $key, $hex_encode = true ) {
    $key = self::mysql_aes_key($key);
    $pad_value = 16-(strlen($text) % 16);
    $text = str_pad($text, (16*(floor(strlen($text) / 16)+1)), chr($pad_value));
    srand();
    $size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB);
    $iv = mcrypt_create_iv($size, MCRYPT_RAND);
    $encrypted = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $text, MCRYPT_MODE_ECB, $iv);
    return ($hex_encode) ? bin2hex($encrypted) : $encrypted;
  }

  /**
   * AES ECBモード復号化
   * @param $data 暗号化データ
   * @param $key 暗号化キー
   */
  public static function mysql_aes_decrypt( $data, $key, $hex_encode = true ) {
    $key = self::mysql_aes_key($key);
    $size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB);
    srand();
    $iv = mcrypt_create_iv($size, MCRYPT_RAND);
    if ($hex_encode) {
      $bin = pack('H*', $data);
    } else {
      $bin = $data;
    }
    $decrypted = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $bin, MCRYPT_MODE_ECB, $iv); 
    return rtrim($decrypted, "\0..\16");
  }


	/**
	 * hmac-sha256ハッシュ
	 *
	 * @param $text ハッシュ化する文字列
	 * @param $key ハッシュ化の為のキー
	 */
	public static function hmac_sha256( $text, $key ) {
		return base64_encode(hash_hmac( "sha256", $text, $key, true ));
	}

	/**
	 * ランダムな32文字の文字列を生成する
	 */
	public static function create_random_key () {
		return md5( uniqid( mt_rand(), true ) );
	}

	/**
	 * シークレットキー発行
	 */
	public static function create_secret() {
		return sha1( uniqid( mt_rand(), true ) );
	}

	/**
	 * 認証コードの発行
	 */
	public static function create_auth_code() {
		$code = '';
		for($i=0;$i<4;$i++) {
			$code .= rand(0,9);
		}
		return $code;
	}

	/**
	 * セッションIDの発行
	 */
	public static function create_session_id() {
		// TODO: シークレットキーと同じ生成方法で問題ないか
		// return sha1( uniqid( mt_rand(), true ) );
		return uuid_create(UUID_TYPE_RANDOM);
	}

	/**
	 * 登録時のverify生成
	 */
	public static function create_verify() {
		// TODO: シークレットキーと同じ生成方法で問題ないか
		return sha1( uniqid( mt_rand(), true ) );
	}


	/**
	 * シグネイチャーの生成
	 */
	public static function create_signature( $req, $key ) {
		$schema = ( $_SERVER['SERVER_PORT'] == '443' ) ? 'https' : 'http';
		$sig_parts = array();
		$method = $req->getMethod();
		switch ($method){
		case 'POST':
			$params = $req->post();
			break;
		case 'GET':
			$params = $req->get();
			break;
		case 'PUT':
			$params = $req->put();
			break;
		default :
			$params = array();
			break;
		}
		ksort($params);
		$sig_parts[] = $method;
		$sig_parts[] = sprintf("%s://%s%s%s",$schema, $_SERVER['HTTP_HOST'], $req->getRootUri(), $req->getResourceUri());
		foreach($params as $k => $v) {
			$sig_parts[] = sprintf("%s=%s", $k, $v);
		}
		$sig_str = implode("&", $sig_parts);
		return rawurlencode(base64_encode(hash_hmac('sha1', $sig_str, $key, true )));
	}

	public static function format_tel_no($val) {
		return preg_replace('/\+\d+\s|[^\d]/','',$val);
	}

	/**
	 * PHP0パディング対応
	 */
	protected static function pkcs5_pad( $text, $blocksize ) {
		$pad = $blocksize - (strlen($text) % $blocksize);
		return $text . str_repeat(chr($pad), $pad);
	}

	/**
	 * PHP0パディング対応
	 */
	protected static function pkcs5_unpad( $text ) {
		$pad = ord($text{strlen($text)-1});
		if ($pad > strlen($text)) return false;
		if (strspn($text, chr($pad), strlen($text) - $pad) != $pad) return false;
		return substr($text, 0, -1 * $pad);
	}

	/**
	 * キャメルケースに変換
	 */
	public static function to_camelcase( $str ){
		$parts = explode('_', $str);
		$new_str = '';
		foreach( $parts as $i => $val) {
			if ( $i > 0 ) {
				$val = ucfirst($val);
			}
			$new_str .= $val;
		}
		return $new_str;
	}

	/**
	 * スネークケースに変換
	 */
	public static function to_snakecase( $str ){
		// 全部大文字だったらそのままにする
		if( preg_match('/[a-z]/', $str) ){
			$str = preg_replace('/([A-Z]{1,})/', '_$1', $str);
			$str = ltrim( $str, '_' );
			return strtolower($str);
		}else{
			return $str;
		}
	}

	/**
	 * UTCに変換
	 */
	public static function to_utcdate($date) {
		if (is_null($date) || strlen($date) <= 0 ) {
			return null;
		}
		try {
			//DateTimeZoneをインスタンス化してsetTiemZoneでUTCをセットする
			$date_obj = new \DateTime($date);
			$date_obj->setTimeZone(new \DateTimeZone('UTC'));
			return $date_obj->format('Y-m-d H:i:s');
		} catch (\Exception $e) {
			throw new Exception\BadRequestException();
		}
	}

	public static function get_ISO8601_now() {
		try {
			$date = new \DateTime();
		} catch (\Exception $e) {
			return null;
		}
		return $date->format(\DateTime::ISO8601);

	}

	/**
	 * アルファベットをすべて大文字にする
	 */
	public static function to_uppercase($str) {
		return strtoupper($str);
	}

	/**
	 * アルファベットをすべて小文字にする
	 */
	public static function to_lowercase($str) {
		return strtolower($str);
	}

	/**
	 * 制限されたIPの中に入っているかの確認
	 *
	 * サブネットマスクに対応
	 */
	public static function ip_check($p_ip, $permit_ips = array()) {
		if (empty($permit_ips)) return false;
		if (!is_array($permit_ips)) $permit_ips = array($permit_ips);
		foreach ($permit_ips as $p_permit_ip) {
			if (count(explode("/", $p_permit_ip)) == 1) {
				if ($p_permit_ip == $p_ip) return true;
				else continue;
			}
			list($ip, $mask_bit) = explode("/", $p_permit_ip);
			$ip_long = ip2long($ip) >> (32 - $mask_bit);
			$p_ip_long = ip2long($p_ip) >> (32 - $mask_bit);
			if ($p_ip_long == $ip_long) {
				return true;
			} else {
				continue;
			}
		}
		return false;
	}


	/**
	 * Convert any given variable into a SimpleXML object
	 *
	 * @param mixed $object variable object to convert
	 * @param string $root root element name
	 * @param object $xml xml object
	 * @param string $unknown element name for numeric keys
	 * @param string $doctype XML doctype
	 */
	public static function to_xml($object, $root = 'data', $xml = NULL, $unknown = 'element', $doctype = "<?xml version = '1.0' encoding = 'utf-8'?>")
	{
		if(is_null($xml))
		{
			$xml = simplexml_load_string("$doctype<$root/>");
		}

		foreach((array) $object as $k => $v)
		{
			if(is_int($k))
			{
				$k = $unknown;
			}

			if(is_scalar($v))
			{
				$xml->addChild($k, h($v));
			}
			else
			{
				$v = (array) $v;
				$node = array_diff_key($v, array_keys(array_keys($v))) ? $xml->addChild($k) : $xml;
				self::from($v, $k, $node);
			}
		}

		return $xml;
	}

	/**
	 * Tests whether a string contains only 7bit ASCII characters.
	 *
	 * @param string $string to check
	 * @return bool
	 */
	public static function is_ascii($string)
	{
		return ! preg_match('/[^\x00-\x7F]/S', $string);
	}


	/**
	 * Encode a string so it is safe to pass through the URL
	 *
	 * @param string $string to encode
	 * @return string
	 */
	public static function base64_url_encode($string = NULL)
	{
		return strtr(base64_encode($string), '+/=', '-_~');
	}


	/**
	 * Decode a string passed through the URL
	 *
	 * @param string $string to decode
	 * @return string
	 */
	public static function base64_url_decode($string = NULL)
	{
		return base64_decode(strtr($string, '-_~', '+/='));
	}


	/**
	 * Convert special characters to HTML safe entities.
	 *
	 * @param string $string to encode
	 * @return string
	 */
	public static function h($string)
	{
		return htmlspecialchars($string, ENT_QUOTES, 'utf-8');
	}

	/**
	 * Filter a valid UTF-8 string so that it contains only words, numbers,
	 * dashes, underscores, periods, and spaces - all of which are safe
	 * characters to use in file names, URI, XML, JSON, and (X)HTML.
	 *
	 * @param string $string to clean
	 * @param bool $spaces TRUE to allow spaces
	 * @return string
	 */
	public static function sanitize($string, $spaces = TRUE)
	{
		$search = array(
			'/[^\w\-\. ]+/u',			// Remove non safe characters
			'/\s\s+/',					// Remove extra whitespace
			'/\.\.+/', '/--+/', '/__+/'	// Remove duplicate symbols
		);

		$string = preg_replace($search, array(' ', ' ', '.', '-', '_'), $string);

		if( ! $spaces)
		{
			$string = preg_replace('/--+/', '-', str_replace(' ', '-', $string));
		}

		return trim($string, '-._ ');
	}


	/**
	 * Create a SEO friendly URL string from a valid UTF-8 string.
	 *
	 * @param string $string to filter
	 * @return string
	 */
	public static function sanitize_url($string)
	{
		return urlencode(mb_strtolower(sanitize($string, FALSE)));
	}


	/**
	 * Filter a valid UTF-8 string to be file name safe.
	 *
	 * @param string $string to filter
	 * @return string
	 */
	public static function sanitize_filename($string)
	{
		return sanitize($string, FALSE);
	}


	/**
	 * Return a SQLite/MySQL/PostgreSQL datetime string
	 *
	 * @param int $timestamp
	 */
	public static function sql_date($timestamp = NULL)
	{
		return date('Y-m-d H:i:s', $timestamp ?: time());
	}


	/**
	 * Make a request to the given URL using cURL.
	 *
	 * @param string $url to request
	 * @param array $options for cURL object
	 * @return object
	 */
	public static function curl_request($url, array $options = NULL)
	{
		$ch = curl_init($url);

		$defaults = array(
			CURLOPT_HEADER => 0,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_TIMEOUT => 5,
		);

		// Connection options override defaults if given
		curl_setopt_array($ch, (array) $options + $defaults);

		// Create a response object
		$object = new stdClass;

		// Get additional request info
		$object->response = curl_exec($ch);
		$object->error_code = curl_errno($ch);
		$object->error = curl_error($ch);
		$object->info = curl_getinfo($ch);

		curl_close($ch);

		return $object;
	}

	/**
	 * Create a RecursiveDirectoryIterator object
	 *
	 * @param string $dir the directory to load
	 * @param boolean $recursive to include subfolders
	 * @return object
	 */
	public static function directory($dir, $recursive = TRUE)
	{
		$i = new \RecursiveDirectoryIterator($dir);

		if( ! $recursive) return $i;

		return new \RecursiveIteratorIterator($i, \RecursiveIteratorIterator::SELF_FIRST);
	}

	/**
	 * Make sure that a directory exists and is writable by the current PHP process.
	 *
	 * @param string $dir the directory to load
	 * @param string $chmod value as octal
	 * @return boolean
	 */
	public static function directory_is_writable($dir, $chmod = 0755)
	{
		// If it doesn't exist, and can't be made
		if(! is_dir($dir) AND ! mkdir($dir, $chmod, TRUE)) return FALSE;

		// If it isn't writable, and can't be made writable
		if(! is_writable($dir) AND !chmod($dir, $chmod)) return FALSE;

		return TRUE;


	}

	/**
	 * Compare two strings to avoid timing attacks
	 *
	 * C function memcmp() internally used by PHP, exits as soon as a difference
	 * is found in the two buffers. That makes possible of leaking
	 * timing information useful to an attacker attempting to iteratively guess
	 * the unknown string (e.g. password).
	 *
	 * @param  string $expected
	 * @param  string $actual
	 * @return boolean
	 */
	public static function compareStrings($expected, $actual)
	{
		$expected     = (string) $expected;
		$actual       = (string) $actual;
		$lenExpected  = strlen($expected);
		$lenActual    = strlen($actual);
		$len          = min($lenExpected, $lenActual);

		$result = 0;
		for ($i = 0; $i < $len; $i++) {
			$result |= ord($expected[$i]) ^ ord($actual[$i]);
		}
		$result |= $lenExpected ^ $lenActual;

		return ($result === 0);
	}


	/**
	 * Word wrap
	 *
	 * @param  string  $string
	 * @param  integer $width
	 * @param  string  $break
	 * @param  boolean $cut
	 * @param  string  $charset
	 * @throws Exception\InvalidArgumentException
	 * @return string
	 */
	public static function wordWrap($string, $width = 75, $break = "\n", $cut = false, $charset = 'utf-8')
	{
		$stringWidth = iconv_strlen($string, $charset);
		$breakWidth  = iconv_strlen($break, $charset);

		if (strlen($string) === 0) {
			return '';
		}

		if ($breakWidth === null) {
			throw new Exception\InvalidArgumentException('Break string cannot be empty');
		}

		if ($width === 0 && $cut) {
			throw new Exception\InvalidArgumentException('Cannot force cut when width is zero');
		}

		$result    = '';
		$lastStart = $lastSpace = 0;

		for ($current = 0; $current < $stringWidth; $current++) {
			$char = iconv_substr($string, $current, 1, $charset);

			$possibleBreak = $char;
			if ($breakWidth !== 1) {
				$possibleBreak = iconv_substr($string, $current, $breakWidth, $charset);
			}

			if ($possibleBreak === $break) {
				$result    .= iconv_substr($string, $lastStart, $current - $lastStart + $breakWidth, $charset);
				$current   += $breakWidth - 1;
				$lastStart  = $lastSpace = $current + 1;
				continue;
			}

			if ($char === ' ') {
				if ($current - $lastStart >= $width) {
					$result    .= iconv_substr($string, $lastStart, $current - $lastStart, $charset) . $break;
					$lastStart  = $current + 1;
				}

				$lastSpace = $current;
				continue;
			}

			if ($current - $lastStart >= $width && $cut && $lastStart >= $lastSpace) {
				$result    .= iconv_substr($string, $lastStart, $current - $lastStart, $charset) . $break;
				$lastStart  = $lastSpace = $current;
				continue;
			}

			if ($current - $lastStart >= $width && $lastStart < $lastSpace) {
				$result    .= iconv_substr($string, $lastStart, $lastSpace - $lastStart, $charset) . $break;
				$lastStart  = $lastSpace = $lastSpace + 1;
				continue;
			}
		}

		if ($lastStart !== $current) {
			$result .= iconv_substr($string, $lastStart, $current - $lastStart, $charset);
		}

		return $result;
	}

	/**
	 * String padding
	 *
	 * @param  string  $input
	 * @param  integer $padLength
	 * @param  string  $padString
	 * @param  integer $padType
	 * @param  string  $charset
	 * @return string
	 */
	public static function strPad($input, $padLength, $padString = ' ', $padType = STR_PAD_RIGHT, $charset = 'utf-8')
	{
		$return          = '';
		$lengthOfPadding = $padLength - iconv_strlen($input, $charset);
		$padStringLength = iconv_strlen($padString, $charset);

		if ($padStringLength === 0 || $lengthOfPadding <= 0) {
			$return = $input;
		} else {
			$repeatCount = floor($lengthOfPadding / $padStringLength);

			if ($padType === STR_PAD_BOTH) {
				$lastStringLeft  = '';
				$lastStringRight = '';
				$repeatCountLeft = $repeatCountRight = ($repeatCount - $repeatCount % 2) / 2;

				$lastStringLength       = $lengthOfPadding - 2 * $repeatCountLeft * $padStringLength;
				$lastStringLeftLength   = $lastStringRightLength = floor($lastStringLength / 2);
				$lastStringRightLength += $lastStringLength % 2;

				$lastStringLeft  = iconv_substr($padString, 0, $lastStringLeftLength, $charset);
				$lastStringRight = iconv_substr($padString, 0, $lastStringRightLength, $charset);

				$return = str_repeat($padString, $repeatCountLeft) . $lastStringLeft
					. $input
					. str_repeat($padString, $repeatCountRight) . $lastStringRight;
			} else {
				$lastString = iconv_substr($padString, 0, $lengthOfPadding % $padStringLength, $charset);

				if ($padType === STR_PAD_LEFT) {
					$return = str_repeat($padString, $repeatCount) . $lastString . $input;
				} else {
					$return = $input . str_repeat($padString, $repeatCount) . $lastString;
				}
			}
		}

		return $return;
	}


	static $plural = array(
		'/(quiz)$/i'               => '$1zes',
		'/^(ox)$/i'                => '$1en',
		'/([m|l])ouse$/i'          => '$1ice',
		'/(matr|vert|ind)ix|ex$/i' => '$1ices',
		'/(x|ch|ss|sh)$/i'         => '$1es',
		'/([^aeiouy]|qu)y$/i'      => '$1ies',
		'/(hive)$/i'               => '$1s',
		'/(?:([^f])fe|([lr])f)$/i' => '$1$2ves',
		'/(shea|lea|loa|thie)f$/i' => '$1ves',
		'/sis$/i'                  => 'ses',
		'/([ti])um$/i'             => '$1a',
		'/(tomat|potat|ech|her|vet)o$/i'=> '$1oes',
		'/(bu)s$/i'                => '$1ses',
		'/(alias)$/i'              => '$1es',
		'/(octop)us$/i'            => '$1i',
		'/(ax|test)is$/i'          => '$1es',
		'/(us)$/i'                 => '$1es',
		'/s$/i'                    => 's',
		'/$/'                      => 's'
	);

	static $singular = array(
		'/(quiz)zes$/i'             => '$1',
		'/(matr)ices$/i'            => '$1ix',
		'/(vert|ind)ices$/i'        => '$1ex',
		'/^(ox)en$/i'               => '$1',
		'/(alias)es$/i'             => '$1',
		'/(octop|vir)i$/i'          => '$1us',
		'/(cris|ax|test)es$/i'      => '$1is',
		'/(shoe)s$/i'               => '$1',
		'/(o)es$/i'                 => '$1',
		'/(bus)es$/i'               => '$1',
		'/([m|l])ice$/i'            => '$1ouse',
		'/(x|ch|ss|sh)es$/i'        => '$1',
		'/(m)ovies$/i'              => '$1ovie',
		'/(s)eries$/i'              => '$1eries',
		'/([^aeiouy]|qu)ies$/i'     => '$1y',
		'/([lr])ves$/i'             => '$1f',
		'/(tive)s$/i'               => '$1',
		'/(hive)s$/i'               => '$1',
		'/(li|wi|kni)ves$/i'        => '$1fe',
		'/(shea|loa|lea|thie)ves$/i'=> '$1f',
		'/(^analy)ses$/i'           => '$1sis',
		'/((a)naly|(b)a|(d)iagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/i'  => '$1$2sis',
		'/([ti])a$/i'               => '$1um',
		'/(n)ews$/i'                => '$1ews',
		'/(h|bl)ouses$/i'           => '$1ouse',
		'/(corpse)s$/i'             => '$1',
		'/(us)es$/i'                => '$1',
		'/s$/i'                     => ''
	);

	static $irregular = array(
		'move'   => 'moves',
		'foot'   => 'feet',
		'goose'  => 'geese',
		'sex'    => 'sexes',
		'child'  => 'children',
		'man'    => 'men',
		'tooth'  => 'teeth',
		'person' => 'people'
	);

	static $uncountable = array(
		'sheep',
		'fish',
		'deer',
		'series',
		'species',
		'money',
		'rice',
		'information',
		'equipment'
	);

	public static function pluralize( $string )
	{
		// save some time in the case that singular and plural are the same
		if ( in_array( strtolower( $string ), self::$uncountable ) )
			return $string;

		// check for irregular singular forms
		foreach ( self::$irregular as $pattern => $result )
		{
			$pattern = '/' . $pattern . '$/i';

			if ( preg_match( $pattern, $string ) )
				return preg_replace( $pattern, $result, $string);
		}

		// check for matches using regular expressions
		foreach ( self::$plural as $pattern => $result )
		{
			if ( preg_match( $pattern, $string ) )
				return preg_replace( $pattern, $result, $string );
		}

		return $string;
	}

	public static function singularize( $string )
	{
		// save some time in the case that singular and plural are the same
		if ( in_array( strtolower( $string ), self::$uncountable ) )
			return $string;

		// check for irregular plural forms
		foreach ( self::$irregular as $result => $pattern )
		{
			$pattern = '/' . $pattern . '$/i';

			if ( preg_match( $pattern, $string ) )
				return preg_replace( $pattern, $result, $string);
		}

		// check for matches using regular expressions
		foreach ( self::$singular as $pattern => $result )
		{
			if ( preg_match( $pattern, $string ) )
				return preg_replace( $pattern, $result, $string );
		}

		return $string;
	}

	public static function pluralize_if($count, $string)
	{
		if ($count == 1)
			return "1 $string";
		else
			return $count . " " . self::pluralize($string);
	}

	public static function isPjax() {
		if (!function_exists('getallheaders')) $headers = self::getallheaders(); 
		else $headers = getallheaders();
		if ((isset($headers['X-PJAX']) && $headers['X-PJAX'] === "true") || 
			(isset($headers['x-pjax']) && $headers['x-pjax'] === "true") ||
			(isset($headers['X-Pjax']) && $headers['X-Pjax'] === "true")) {
				return true;
			}
		return false;
	}

	public static function getallheaders() {
		$headers = ''; 
		foreach ($_SERVER as $name => $value) 
		{ 
			if (substr($name, 0, 5) == 'HTTP_') 
			{ 
				$headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value; 
			} else if ($name == "CONTENT_TYPE") { 
				$headers["Content-Type"] = $value; 
			} else if ($name == "CONTENT_LENGTH") { 
				$headers["Content-Length"] = $value; 
			} else {
				$headers[str_replace(' ', '-', ucwords(strtolower($name, 5)))] = $value; 
			}		
		} 
		return $headers;
	}

	function apache_request_headers() { 
		foreach($_SERVER as $key=>$value) { 
			if (substr($key,0,5)=="HTTP_") { 
				$key=str_replace(" ","-",ucwords(strtolower(str_replace("_"," ",substr($key,5))))); 
				$out[$key]=$value; 
			}else{ 
				$out[$key]=$value; 
			} 
		} 
		return $out; 
	} 


}
