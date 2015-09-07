<?php
/**
 * Автозагрузка классов библиотеки PayQR
 */
if (version_compare(PHP_VERSION, '5.0.0') < 0) {
  die("Для вашей версии PHP (".phpversion().") необходимо использовать другую версию библиотеки PayQR");
}
payqr_autoload::Register();

class payqr_autoload
{
  /**
   * Регистрация автозагрузчика со стандартной библиотекой PHP (SPL)
   */
  public static function Register()
  {
    if (function_exists('__autoload')) {
      // Register any existing autoloader function with SPL, so we don't get any clashes
      spl_autoload_register('__autoload');
    }
    // Register ourselves with SPL
    if (version_compare(PHP_VERSION, '5.3.0') >= 0) {
      return spl_autoload_register(array('payqr_autoload', 'Load'), true, true);
    } else {
      return spl_autoload_register(array('payqr_autoload', 'Load'));
    }
  }

  /**
   * Autoload a class identified by name
   *
   * @param    string $pClassName Name of the object to load
   */
  public static function Load($pClassName)
  {
    if ((class_exists($pClassName, false)) || (strpos($pClassName, 'payqr_') !== 0)) {
      // Either already loaded, or not a PayQR class request
      return false;
    }
    $pClassFilePath = PAYQR_ROOT . 'classes' . DIRECTORY_SEPARATOR . $pClassName . '.php';
    if ((file_exists($pClassFilePath) === false) || (is_readable($pClassFilePath) === false)) {
      // Can't load
      return false;
    }
    require($pClassFilePath);
  }
} 