<?php
/**
 * Ведение отладочных логов
 */

class payqr_logs
{

  /**
   * Добавление записи в лог файл
   *
   * @param $file
   * @param $message
   */
  public static function log($message)
  {
    if(!payqr_config::$enabledLog)
      return;
    $message = str_repeat("-", 100) . date("Y-m-d H:i:s") . str_repeat("-", 100) . "\n{$message}\n\n";
    file_put_contents(payqr_config::$logFile, $message, FILE_APPEND);
  }
}
