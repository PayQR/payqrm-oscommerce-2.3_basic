<?php
/**
 * Класс конфигурации
 * Подключите этот файл, чтобы обеспечить автозагрузку всех необходимых классов для работы с API PayQR
 */

if (!defined('PAYQR_ROOT')) {
  define('PAYQR_ROOT', dirname(__FILE__) . '/');
  require(PAYQR_ROOT . 'classes/payqr_autoload.php');
}
if (!defined('PAYQR_ERROR_HANDLER')) {
  define('PAYQR_ERROR_HANDLER', dirname(__FILE__) . '/handlers_errors/');
}
if (!defined('PAYQR_HANDLER')) {
  define('PAYQR_HANDLER', dirname(__FILE__) . '/handlers/');
}

class payqr_config
{
// по умолчанию ниже продемонстрированы примеры значений, укажите актуальные значения для своего "Магазина"
  public static $merchantID = ""; // номер "Магазина" из личного кабинета PayQR
  public static $secretKeyIn = ""; // входящий ключ из личного кабинета PayQR (SecretKeyIn), используется в уведомлениях от PayQR
  public static $secretKeyOut = ""; // исходящий ключ из личного кабинета PayQR (SecretKeyOut), используется в запросах в PayQR

  public static $logFile =  ""; // путь к файлу логов библиотеки PayQR

  public static $enabledLog = true; // разрешить библиотеке PayQR вести лог

  public static $maxTimeOut = 10; // максимальное время ожидания ответа PayQR на запрос интернет-сайта в PayQR

  public static $checkHeader = true; // проверять секретный ключ SecretKeyIn в уведомлениях и ответах от PayQR

  public static $version_api = '1.1.1'; // версия библиотеки PayQR

  private function  __construct(){

  }
}
