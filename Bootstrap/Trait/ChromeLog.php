<?php

namespace Slim\Bootstrap\Trait;

trait ChromeLog {
  protected function chrome_logger($object, $level) {
    if (class_exists('\ChromePhp')) {
      switch($level) {
        case 3: // INFO
          \ChromePhp::info($object);
          break;
        case 2: // WARN
          \ChromePhp::warn($object);
          break;
        case 1: // ERROR
        case 0: // FATAL
          \ChromePhp::error($object);
          break;
        default:
          \ChromePhp::log($object);
          break;
      }
    }
  }
}
