<?php

namespace Slim\Bootstrap;

use \Slim\Bootstrap\Exception;

class I18n {

  protected static $domain = 'default';
  protected static $locale_path = "config/locale";
  protected static $default_lang = "ja";

  public static function configure($config) {
    $vars = get_class_vars(__CLASS__);
    $properties = array_intersect_key($config, $vars);
    foreach ($properties as $key => $value) {
      self::${$key} = $value;
    }
  }

  /**
   * get static property
   */
  public static function get($name) {
    $vars = get_class_vars(__CLASS__);
    if (array_key_exists($name, $vars)) {
      return self::${$name};
    }
    return null;
  }


  /**
   * yamlファイルを読み込んで.poと.moを生成する
   */
  public static function convert() {
    $locales = array();
    foreach (new \DirectoryIterator(self::$locale_path) as $file_info) {
      if ($file_info->isDot() || $file_info->isDir()) continue;
      if ($file_info->getExtension() === 'yml' || $file_info->getExtension() === 'yaml') {
        $file_name = $file_info->getBasename(".".$file_info->getExtension());
        $tmp = explode(".", $file_name);
        $lang = array_pop($tmp);
        $yaml = yaml_parse_file($file_info->getPathname());
        if (isset($locales[$lang])) {
          $locales[$lang] = array_merge_recursive($locales[$lang], $yaml);
        } else {
          $locales[$lang] = $yaml;
        }
      }
    }
    foreach ($locales as $lang => $yaml) {
      self::write_text($lang, $yaml);
    }
  }

  protected static function write_text($lang,$yaml) {
    // check or create locale dir
    $locale_dir = sprintf("%s/%s/%s_%s/LC_MESSAGES", APP_ROOT_PATH, self::$locale_path, $lang, self::lang_map()[$lang]);
    if (!file_exists($locale_dir)) {
      mkdir($locale_dir, 0777, true);
    }
    $po_txt = self::parse_yaml_to_po_text($yaml);

    $po_path = sprintf("%s/%s.po", $locale_dir, self::$domain);
    $mo_path = sprintf("%s/%s.mo", $locale_dir, self::$domain);
    file_put_contents($po_path, $po_txt);
    $fmt_cmd = sprintf("msgfmt -o %s %s", $mo_path, $po_path);
    shell_exec($fmt_cmd);

  }

  protected static function parse_yaml_to_po_text ($data, $prefix = '') {
    $po_txt = '';
    foreach ($data as $key => $msgstr) {
      $msgid = ($prefix) ? $prefix . '.' . $key : $key;
      if (is_array($msgstr)) {
        $po_txt .= self::parse_yaml_to_po_text($msgstr, $msgid);
      } else {
        $po_txt .= sprintf("msgid \"%s\"\nmsgstr \"%s\"\n\n", $msgid, $msgstr);
      }
    }
    return $po_txt;	
  }

  /**
   * プライマリコードと対応するデフォルトのサブコードのマッピング
   * gettextはサブコードがついてないと動かないこともあるようなので補完する
   * 取り急ぎ主要と思われる言語のみ対応
   */
  public static function lang_map() {
    return [
      "ja" => "JP", // 日本語
      "en" => "US", // 英語
      "ko" => "KR", // 韓国語
      "zh" => "CN", // 中国語
      "es" => "ES", // スペイン語
      "fr" => "FR", // フランス語 
      "de" => "DE", // ドイツ語
      "it" => "IT", // イタリア語
      "pt" => "PT", // ポルトガル語
      "ru" => "RU"  // ロシア語
      ];
  }

  public static function lang() {
    return $_SESSION['lang'];
  }

  public static function set_lang($lang) {
    if (array_key_exists($lang, self::lang_map())) {
      $_SESSION['lang'] = $lang;
    }
  }

}
