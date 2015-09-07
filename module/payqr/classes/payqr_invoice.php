<?php
/**
 * Работа с объектами "Счет на оплату"
 *
 * Интернет-сайт получает объект "Счет на оплату" целиком в каждом уведомлении от PayQR о событиях, связанных с этим объектом. Интернет-сайт может сам реализовывать любые логики работы с объектом "Счет на оплату", но для упрощения написания собственного кода некоторые методы заранее подготовлены и представлены в рамках этого файла. Пользуйтесь ими при желании.
 */
 
class payqr_invoice
{
  protected $data;
  public $cancel = false;
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
   * Возвращает идентификатор PayQR конкретного объекта "Счет на оплату"
   * @return string
   */
  public function getInvId()
  {
    payqr_logs::log('payqr_invoice::getInvId()');
    return isset($this->data->id) ? $this->data->id : 0;
  }

 /**
   * Возвращает номер счета в PayQR (так номер счета видит покупатель в приложении PayQR)
   * @return mixed
   */
  public function getPayqrNumber()
  {
    payqr_logs::log('payqr_invoice::getPayqrNumber()');
    return $this->data->payqrNumber;
  }

  /**
   * Возвращает номер покупателя в PayQR (уникальный идентификатор покупателя, можно ориентироваться для начисления бонусов за повторные покупки)
   * @return mixed
   */
  public function getPayqrUserId()
  {
    payqr_logs::log('payqr_invoice::getPayqrUserId()');
    return $this->data->payqrUserId;
  }

  /**
   * Возвращает номер заказа интернет-сайта (orderId) из объекта PayQR "Счет на оплату"
   * @return mixed
   */
  public function getOrderId()
  {
    payqr_logs::log('payqr_invoice::getOrderId()');
    return isset($this->data->orderId) ? $this->data->orderId : 0;
  }

  /**
   * Возвращает номер заказа интернет-сайта (orderId) из объекта PayQR "Счет на оплату"
   * @return mixed
   */
  public function getOrderGroupId()
  {
    payqr_logs::log('payqr_invoice::getOrderGroupId()');
    return isset($this->data->orderGroup) ? $this->data->orderGroup : 0;
  }

  /**
   * Передает номера заказа интернет-сайта (orderId) в объект PayQR "Счет на оплату"
   * @param $id
   * @return bool
   */
  public function setOrderId($order_id)
  {
    payqr_logs::log('payqr_invoice::setOrderId('.$order_id.')');
    if (isset($this->data)) {
      $this->data->orderId = $order_id;
      return true;
    }
    return false;
  }
  
  /**
   * Тестовый метод удаляет orderId из json Ответа
   * @return bool
   */
  public function removeOrderId()
  {
    payqr_logs::log('payqr_invoice::removeOrderId()');
    if (isset($this->data)) {
      unset($this->data->orderId);
      return true;
    }
    return false;
  }
  /**
   * Тестовый метод удаляет orderId из json Ответа
   * @return bool
   */
  public function removeAmount()
  {
    payqr_logs::log('payqr_invoice::removeAmount()');
    if (isset($this->data)) {
      unset($this->data->amount);
      return true;
    }
    return false;
  }

  /**
   * Возвращает сумму заказа из объекта PayQR "Счет на оплату"
   * @return float
   */
  public function getAmount()
  {
    payqr_logs::log('payqr_invoice::getAmount()');
    return isset($this->data->amount) ? $this->data->amount : 0;
  }

  /**
   * Изменяет сумму заказа в объекте PayQR "Счет на оплату" (для случаев крайней необходимости)
   * @param $amount
   * @return bool
   */
  public function setAmount($amount)
  {
    payqr_logs::log('payqr_invoice::setAmount('.$amount.')');
    if (isset($this->data)) {
      $this->data->amount = round($amount, 2);
      return true;
    }
    return false;
  }

  /**
   * Возвращает содержимое корзины из объекта PayQR "Счет на оплату
   * @return mixed
   */
  public function getCart()
  {
    payqr_logs::log('payqr_invoice::getCart()');
    return isset($this->data->cart) ? $this->data->cart : false;
  }

  /**
   * Изменяет содержимое корзины в объекте PayQR "Счет на оплату"
   * @param $cart_obj
   * @return bool
   */
  public function setCart($cart_obj)
  {
    payqr_logs::log('payqr_invoice::setCart($cart_obj)');
    if (isset($this->data)) {
      $this->data->cart = $cart_obj;
      return true;
    }
    return false;
  }

  /**
   * Возвращает данные покупателя из объекта PayQR "Счет на оплату"
   * @return object
   */
  public function getCustomer()
  {
    payqr_logs::log('payqr_invoice::getCustomer()');
    return isset($this->data->customer) ? $this->data->customer : false;
  }

  /**
   * Возвращает адрес доставки из объекта PayQR "Счет на оплату"
   * @return mixed
   */
  public function getDelivery()
  {
    payqr_logs::log('payqr_invoice::getDelivery()');
    return isset($this->data->delivery) ? $this->data->delivery : false;
  }

  /**
   * Передает список способов доставки интернет-сайта в объект PayQR "Счет на оплату"
   *
   * $delivery_cases_array = [
   *  [
   *    'article' => '2001',
   *    'number' => '1.1',
   *    'name' => 'DHL',
   *    'description' => '1-2 дня',
   *    'amountFrom' => '0',
   *    'amountTo' => '70'
   *  ],
   *
   * ];
   *
   * @param $delivery_cases_array
   */
  public function setDeliveryCases($delivery_cases_array)
  {
    payqr_logs::log('payqr_invoice::setDeliveryCases($delivery_cases_array)');
    if (isset($this->data) && count($delivery_cases_array) > 0) {
      $delivery_cases = array();
      foreach ($delivery_cases_array as $delivery) {
        $delivery_cases[] = json_decode(json_encode($delivery), false);
      }
      $this->data->deliveryCases = $delivery_cases;
      return true;
    }
    return false;
  }

  /**
   * Передает список пунктов самовывоза интернет-сайта в объект PayQR "Счет на оплату"
   *
   * $pick_points_cases_array = [
   *  [
   *    'article' => '1001',
   *    'number' => '1.1',
   *    'name' => 'Наш пункт самовывоза 1',
   *    'description' => 'с 10:00 до 22:00',
   *    'amountFrom' => '90',
   *    'amountTo' => '140',
   *  ],
   *
   * ];
   *
   * @param $delivery_cases_array
   */
  public function setPickPointsCases($pick_points_cases_array)
  {
    payqr_logs::log('payqr_invoice::setDeliveryCases($pick_points_cases_array)');
    if (isset($this->data) && count($pick_points_cases_array) > 0) {
      $pick_points_cases = array();
      foreach ($pick_points_cases_array as $point) {
        $pick_points_cases[] = json_decode(json_encode($point), false);
      }
      $this->data->pickPoints = $pick_points_cases;
      return true;
    }
    return false;
  }

  /**
   * Возвращает выбранный покупаталем пункт самовывоза из объекта PayQR "Счет на оплату"
   * Выбранный пункт самовывоза полностью соответствует одной из записей в предложенных интернет-сайтом пунктах самовывоза или принимает значение null.
   * 
   * @return bool
   */
  public function getPickPoints()
  {
    payqr_logs::log('payqr_invoice::getPickPoints()');
    if(isset($this->data->pickPointsSelected)){
      return $this->data->pickPointsSelected;
    }
    return false;
  }

  /**
   * Возвращает выбранный покупаталем способ доставки из объекта PayQR "Счет на оплату"
   * Выбранный способ доставки полностью соответствует одной из записей в предложенных интернет-сайом способах доставки или принимает значение null.
   *
   * @return bool
   */
  public function getDeliveryCasesSelected()
  {
    payqr_logs::log('payqr_invoice::getDeliveryCasesSelected()');
    if(isset($this->data->deliveryCasesSelected)){
      return $this->data->deliveryCasesSelected;
    }
    return false;
  }

  /**
   * Возвращает промо-идентификатор, если его указал покупатель, из объекта PayQR "Счет на оплату"
   * @return mixed
   */
  public function getPromo()
  {
    payqr_logs::log('payqr_invoice::getPromo()');
    return $this->data->promo;
  }

 /**
   * Возвращает номер товарной группы (orderGroup) из объекта PayQR "Счет на оплату"
   * @return mixed
   */
  public function getOrderGroup()
  {
    payqr_logs::log('payqr_invoice::getOrderGroup()');
    return $this->data->orderGroup;
  }

  /**
   * Возвращает использованную служебную информацию (userData) из объекта PayQR "Счет на оплату"
   * @return mixed
   */
  public function getUserData()
  {
    payqr_logs::log('payqr_invoice::getUserData()');
    return $this->data->userData;
  }

  /**
   * Возвращает срок ожидания оплаты этого счета из объекта PayQR "Счет на оплату"
   * @return mixed
   */
  public function getConfirmWaitingInMinutes()
  {
    payqr_logs::log('payqr_invoice::getConfirmWaitingInMinutes()');
    return $this->data->confirmWaitingInMinutes;
  }

  /**
   * Изменяет срок ожидания оплаты этого счета в объекте PayQR "Счет на оплату"
   * @return mixed
   */
  public function setConfirmWaitingInMinutes($min)
  {
    payqr_logs::log('payqr_invoice::setConfirmWaitingInMinutes()');
    if (isset($this->data)) {
      $this->data->confirmWaitingInMinutes = $min;
      return true;
    }
    return false;
  }

  /**
   * Отменяет заказ (счет) до его оплаты (не через отдельный запрос в PayQR, а ответом в PayQR на полученное уведомление как HTTP/1.1 409 Conflict, поэтому подходит для использования только на этапе получения уведомления о событии invoice.order.creating)
   */
  public function cancelOrder()
  {
    payqr_logs::log('payqr_invoice::cancelOrder()');
    $this->cancel = true;
  }

} 