<?php
/**
 * Валидатор чисел
 */

class payqr_numeric_validator {
  /**
   * Вспомогательный метод для валидации аргумента, если он является числовым
   *
   * @param mixed     $argument
   * @param string|null $argumentName
   * @return bool
   */
  public static function validate($argument, $argumentName = null)
  {
    if (trim($argument) != null && !is_numeric($argument)) {
      throw new payqr_exeption("$argumentName is not a valid numeric value");
    }
    return true;
  }
} 