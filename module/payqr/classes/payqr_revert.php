<?php
/**
 * Работа с объектами "Возвраты"
 *
 * Интернет-сайт получает объект "Возвраты" целиком в каждом уведомлении от PayQR о событиях, связанных с этим объектом. Интернет-сайт может сам реализовывать любые логики работы с объектом "Возвраты", но для упрощения написания собственного кода некоторые методы заранее подготовлены и представлены в рамках этого файла. Пользуйтесь ими при желании.
 */
 
class payqr_revert
{
  protected $data;
  protected $livemode;

  public function __construct($data, $livemode=false)
  {
    $this->data = $data;
    $this->livemode = $livemode;
  }

  /**
   * Проверяет режим работы полученного уведомления от PayQR (true - "боевой", false - "тестовый")
   * @return bool
   */
  public function isLivemode()
  {
    return $this->livemode;
  }

  /**
   * Возвращает идентификатор PayQR конкретного объекта "Возвраты"
   * @return mixed
   */
  public function getRevertId()
  {
    payqr_logs::log('payqr_revert::getRevertId()');
    return isset($this->data->id) ? $this->data->id : 0;
  }

  /**
   * Возвращает идентификатор PayQR конкретного объекта "Счет на оплату", по которому осуществлялся возврат
   * @return mixed
   */
  public function getInvId()
  {
    payqr_logs::log('payqr_revert::getInvId()');
    return isset($this->data->invoiceId) ? $this->data->invoiceId : 0;
  }

  /**
   * Возвращает номер счета в PayQR, по которому осуществлялся возврат (так номер счета видит покупатель в приложении PayQR)
   * @return mixed
   */
  public function getPayqrNumber()
  {
    payqr_logs::log('payqr_revert::getPayqrNumber()');
    return isset($this->data->pqrNumber) ? $this->data->pqrNumber : 0;
  }

  /**
   * Возвращает сумму возврата по счету из объекта PayQR "Возвраты"
   * @return float
   */
  public function getAmount()
  {
    payqr_logs::log('payqr_revert::getAmount()');
    return isset($this->data->amount) ? $this->data->amount : 0;
  }

} 