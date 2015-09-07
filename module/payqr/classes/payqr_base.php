<?php

/**
 * Проверка конфигурации библиотеки PayQR
 */
class payqr_base
{

public static $apiUrl = "https://payqr.ru/shop/api/1.0"; // Не меняйте этот адрес без получения официальных извещений PayQR
  public static function checkConfig()
  {
    $helper = new payqr_helper();
    payqr_config::$secretKeyIn = $helper->getOption("sercer_key_in");
    payqr_config::$secretKeyOut = $helper->getOption("secret_key_out");
    payqr_config::$logFile = realpath(dirname(__FILE__) . "/../../") . "/" .$helper->getOption("log_path");
    payqr_config::$merchantID = $helper->getOption("merch_id");


    if (payqr_config::$secretKeyIn == "") {
        throw new payqr_exeption("Поле payqr_config::secretKeyIn не может быть пустым, проверьте конфигурацию библиотеки PayQR");
    }
    if (payqr_config::$secretKeyOut == "") {
        throw new payqr_exeption("Поле payqr_config::secretKeyOut не может быть пустым, проверьте конфигурацию библиотеки PayQR");
    }
    if (payqr_config::$enabledLog && payqr_config::$logFile == "") {
        throw new payqr_exeption("Поле payqr_config::logFile не может быть пустым, проверьте конфигурацию библиотеки PayQR");
    }
    if (payqr_config::$merchantID == "") {
        throw new payqr_exeption("Поле payqr_config::merchantID не может быть пустым, проверьте конфигурацию библиотеки PayQR");
    }
  }

  /**
   * Эквивалент apache_request_headers()
   * @return mixed
   */
  public static function getallheaders()
  {
    foreach ($_SERVER as $name => $value) {
      if (substr($name, 0, 5) == 'HTTP_') {
        $name = str_replace(' ', '-', str_replace('_', ' ', substr($name, 5)));
        $headers[$name] = $value;
      } else {
        if ($name == "CONTENT_TYPE") {
          $headers["Content-Type"] = $value;
        } else {
          if ($name == "CONTENT_LENGTH") {
            $headers["Content-Length"] = $value;
          }
        }
      }
    }
    return $headers;
  }
}
