<?php

namespace Slim\Bootstrap\Trait;

use \Slim\Bootstrap\Exception;
use \Zend\Mail as ZendMail;
use \Zend\Mime\Mime;
use \Zend\Mime\Message as MimeMessage;
use \Zend\Mime\Part as MimePart;
use \Zend\Mail\Header;
use \Slim\Bootstrap\Config;

/**
 * クラスにメール送信機能を持たせるtrait
 */
trait Mail {

  protected $mail_exception;

  /**
   * メール送信
   *
   * SESで送る場合は、Configに下記のように書いておく
   *
   * [SMTP]
   * name = 'email-smtp.us-east-1.amazonaws.com'
   * host = 'email-smtp.us-east-1.amazonaws.com'
   * port = 25
   * 
   * [SMTP_AUTH]
   * username = '***************'
   * password = '***************'
   *
   *
   * $this->send_mail([
   *   'from' => 'from@example.jp', 
   *   'to' => 'to@example.com', 
   *   'subject' => 'subject',
   *   'body' => 'body'
   *  ]);
   *
   */
  protected function send_mail($data) {

    $parse_option = function($option) {
      if (is_array($option)) {
        $address = $option[0];
        $name = $option[1];
      } else {
        $address = $option;
        $name = null;
      }
      return [$address, $name];
    };

    try {
      list($from, $from_name) = $parse_option($data['from']);
      list($to, $to_name) = $parse_option($data['to']);
      if (isset($data['reply_to'])) {
        $reply_to_data = $data['reply_to'];
      } else {
        $reply_to_data = $data['from'];
      }
      list($reply_to, $reply_to_name) = $parse_option($reply_to_data);

      $encoding = 'UTF-8';
      $subject_encoding = 'UTF-8';
      if ( preg_match('/docomo.ne.jp$/', $to) ) {
        // $encoding = 'SJIS-win';
        $encoding = 'ISO-2022-JP';
        // $subject_encoding = 'ISO-2022-JP';
        $subject_encoding = 'ISO-2022-JP';
      } elseif ( preg_match('/(vodafone.ne.jp|softbank.ne.jp|ezweb.ne.jp)$/', $to) ) {
        $encoding = 'ISO-2022-JP';
        $subject_encoding = 'ISO-2022-JP';
      }

      if (is_array($data['body'])) {
        $txt = new MimePart(mb_convert_encoding($data['body']['text'], $encoding, mb_internal_encoding()));
        $txt->type = Mime::TYPE_TEXT;
        $txt->charset = $encoding;
        $html = new MimePart(mb_convert_encoding($data['body']['html'], $encoding, mb_internal_encoding()));
        $html->type = Mime::TYPE_HTML;
        $html->charset = $encoding;
        $body = new MimeMessage();
        $body->setParts([$txt, $html]);
        $multi_part = true;
      } else {
        // $body = mb_convert_encoding($data['body'], $encoding, mb_internal_encoding());
        $txt = new MimePart(mb_convert_encoding($data['body'], $encoding, mb_internal_encoding()));
        $txt->type = Mime::TYPE_TEXT;
        $txt->charset = $encoding;
        $body = new MimeMessage();
        $body->setParts([$txt]);
        $multi_part = false;
      }

      $message = new ZendMail\Message();
      $message->setFrom($from, $from_name);
      $message->setTo($to, $to_name);
      $message->setReplyTo($reply_to, $reply_to_name);
      $message->setSubject(mb_convert_encoding($data['subject'], $subject_encoding, mb_internal_encoding()));
      $message->setBody($body);
      if ($multi_part) {
      $message->setEncoding($encoding);
        $message->getHeaders()->get('content-type')->setType(Mime::MULTIPART_ALTERNATIVE);
      // } else {
      //   $message->getHeaders()->get('content-type')->setType(Mime::TYPE_TEXT);
      }

      $config = Config::getInstance();
      $smtp_setting = $config->SMTP;
      $smtp_auth = $config->SMTP_AUTH;
      if ($smtp_auth) {
        $smtp_setting['connection_class'] = 'plain';
        $smtp_setting['connection_config']['auth'] = 'plain';
        $smtp_setting['connection_config']['username'] = $smtp_auth['username'];
        $smtp_setting['connection_config']['password'] = $smtp_auth['password'];
        $smtp_setting['connection_config']['ssl'] = 'tls';

      }
      $options = new ZendMail\Transport\SmtpOptions($smtp_setting);
      $transport = new ZendMail\Transport\Smtp($options);
      $transport->send($message);
      return true;
    } catch (\Exception $e) {
      $this->mail_exception = $e;
      return false;
    }
  }

  protected function get_mail_error() {
    return $this->mail_exception;
  }

  /**
   * キューイング
   */
//  protected function mail_queue() {
//  }
}
