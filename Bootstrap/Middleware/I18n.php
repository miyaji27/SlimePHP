<?php

namespace Slim\Bootstrap\Middleware;

use \Slim\Bootstrap\I18n as LibI18n;

class I18n extends \Slim\Middleware {

  protected $fix_lang;

  public function __construct($fix_lang=null) {
    $this->fix_lang = $fix_lang;  
  }

	public function call() {

    // 固定が指定されていたらずっとそのまま
    if ($this->fix_lang) {
      $primary_lang_code = $this->fix_lang;
      if (!array_key_exists($primary_lang_code, LibI18n::lang_map())) {
        $primary_lang_code = null;
      }
    } elseif ( isset($_SESSION['lang'] )) {
		// セッションに値があったらそっちに切り替えるようにする
			$primary_lang_code = $_SESSION["lang"];
      if (!array_key_exists($primary_lang_code, LibI18n::lang_map())) {
        $primary_lang_code = null;
      }
		} elseif ( isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ) {
			// HTTP_ACCEPT_LANGUAGEで渡されるサブコードの部分は無視する
			foreach (split(",", $_SERVER['HTTP_ACCEPT_LANGUAGE']) as $accept_language) {
				if (strpos($accept_language, '-')) {
					$primary_lang_code = substr($accept_language, 0, strpos($accept_language, '-'));
				} else {
					$primary_lang_code = $accept_language;
				}
        if ($primary_lang_code && array_key_exists($primary_lang_code, LibI18n::lang_map())) {
					break;
				}
			}
		}

    if (!$primary_lang_code) {
      $primary_lang_code = LibI18n::get('default_lang');
    }
    $_SESSION['lang'] = $primary_lang_code;
    $sub_lang_code = LibI18n::lang_map()[$primary_lang_code];
    $lang = sprintf("%s_%s.UTF-8", $primary_lang_code, $sub_lang_code);

		putenv("LANG={$lang}");
		setlocale(LC_ALL, $lang);
		$locale = (preg_match("/^".LibI18n::get('locale_path')."/", APP_ROOT_PATH)) ? LibI18n::get('locale_path') : APP_ROOT_PATH . '/' . LibI18n::get('locale_path');
		bindtextdomain(LibI18n::get('domain'), $locale );
		textdomain(LibI18n::get('domain'));

		$this->next->call();
	}
}
