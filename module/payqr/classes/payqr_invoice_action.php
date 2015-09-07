<?php
/**
 * Методы по объектам "Счет на оплату"
 * Подробнее https://payqr.ru/api/ecommerce#invoices
 */
 
class payqr_invoice_action extends payqr_base_request
{
  /**
   * Отменить счет до оплаты (отказаться от оплаты) целиком
   * Подробнее https://payqr.ru/api/ecommerce#invoice_cancel
   *
   * @param $id
   *
   * POST https://payqr.ru/shop/api/1.0/invoices/{id}/cancel
   *
   * curl https://payqr.ru/shop/api/1.0/invoices/inv_14EeCA2eZvKYlo2C8nDrcXdp/cancel -X=POST
   *
   * Ответ:
   * { "id": "inv_14EeCA2eZvKYlo2C8nDrcXdp", "object": "invoice", "livemode": false, "status": "Cancelled", }
   */
  public function invoice_cancel($id)
  {
    try{
      return $this->post(payqr_base::$apiUrl.'/invoices/'.$id.'/cancel');
    }
    catch(payqr_exeption $e){
      $this->require_handler($e, __FUNCTION__);
    }
  }

  /**
   * Отменить счет после оплаты (вернуть деньги) на определенную указанную сумму
   * Подробнее https://payqr.ru/api/ecommerce#invoice_revert
   *
   * @param $id
   *
   * POST https://payqr.ru/shop/api/1.0/invoices/{id}/revert
   *
   * curl https://payqr.ru/shop/api/1.0/invoices/inv_14EeCA2eZvKYlo2C8nDrcXdp/revert?amount=1000 -X=POST
   *
   * Ответ:
   * { "id": "inv_14EeCA2eZvKYlo2C8nDrcXdp", "object": "invoice", "livemode": false, "revertId": "12000", "amount": "14000", "revertAmount": "12000", }
   */
  public function invoice_revert($id, $amount)
  {
    try{
      return $this->post(payqr_base::$apiUrl.'/invoices/'.$id.'/revert', array('amount'=>round($amount, 2)));
    }
    catch(payqr_exeption $e){
      $this->require_handler($e, __FUNCTION__);
    }
  }

  /**
   * Досрочно подтвердить оплату по счету (запустить финансовые расчеты)
   * Подробнее https://payqr.ru/api/ecommerce#invoice_confirm
   *
   * @param $id
   *
   * POST https://payqr.ru/shop/api/1.0/invoices/{id}/pay/confirm
   *
   * curl https://payqr.ru/shop/api/1.0/invoices/inv_14EeCA2eZvKYlo2C8nDrcXdp/pay/confirm -X=POST
   *
   * Ответ:
   * { "id": "inv_14EeCA2eZvKYlo2C8nDrcXdp", "object": "invoice", "livemode": false, "confirmedStatus": "confirmedEarly", }
   */
  public function invoice_confirm($id)
  {
    try{
      return $this->post(payqr_base::$apiUrl.'/invoices/'.$id.'/pay/confirm');
    }
    catch(payqr_exeption $e){
      $this->require_handler($e, __FUNCTION__);
    }
  }

  /**
   * Подтвердить исполнение заказа по счету (товар доставлен/услуга оказана)
   * Подробнее https://payqr.ru/api/ecommerce#invoice_execution_confirm
   *
   * @param $id
   *
   * POST https://payqr.ru/shop/api/1.0/invoices/{id}/execute
   *
   * curl https://payqr.ru/shop/api/1.0/invoices/inv_14EeCA2eZvKYlo2C8nDrcXdp/execute -X=POST
   *
   * Ответ:
   * { "id": "inv_14EeCA2eZvKYlo2C8nDrcXdp", "object": "invoice", "livemode": false, "executedStatus": "executed", }
   */
  public function invoice_execution_confirm($id)
  {
    try{
      return $this->post(payqr_base::$apiUrl.'/invoices/'.$id.'/execute');
    }
    catch(payqr_exeption $e){
      $this->require_handler($e, __FUNCTION__);
    }

  }

  /**
   * Дослать/изменить текстовое сообщение в счете
   * Подробнее https://payqr.ru/api/ecommerce#invoice_message
   *
   * @param $id
   * @param $text
   * @param $imageUrl
   * @param $url
   *
   * POST https://payqr.ru/shop/api/1.0/invoices/{id}/message
   *
   * curl https://payqr.ru/shop/api/1.0/invoices/inv_14EeCA2eZvKYlo2C8nDrcXdp/message -X=PUT -d
   * '{ "text": "Сообщение", "imageUrl": "http://goods.ru/message.jpg", "url": "http://goods.ru/details" }'
   *
   * Ответ:
   * { "id": "inv_14EeCA2eZvKYlo2C8nDrcXdp", "object": "invoice", "livemode": false, "status": "Cancelled", }
   */
  public function invoice_message($id, $text = "", $imageUrl = "", $url = "")
  {
    $json = json_encode(array('text' => $text, 'imageUrl' => $imageUrl, 'url' => $url));
    $this->curl->headers['Content-Length'] = strlen($json);
    try{
      return $this->post(payqr_base::$apiUrl.'/invoices/'.$id.'/message', $json);
    }
    catch(payqr_exeption $e){
      $this->require_handler($e, __FUNCTION__);
    }
  }

  /**
   * Получить информацию о счете по его идентификатору в PayQR (актуализировать)
   * Подробнее https://payqr.ru/api/ecommerce#invoice_get
   *
   * @param $inv_id
   * @return mixed|string
   *
   * GET https://payqr.ru/shop/api/1.0/invoices/{id}
   *
   * curl "https://payqr.ru/shop/api/1.0/reverts/{id}"
   */
  public function get_invoice($inv_id)
  {
    try{
      return $this->get(payqr_base::$apiUrl.'/invoices/'.$inv_id);
    }
    catch(payqr_exeption $e){
      $this->require_handler($e, __FUNCTION__);
    }
  }

  /**
   * Проверяет наличие файла обработчика и подключает его
   * @param payqr_exeption $e
   * @param $functionName
   */
  private function require_handler(payqr_exeption $e, $functionName)
  {
    if(file_exists(PAYQR_ERROR_HANDLER.'invoice_action_error.php'))
    {
      $response = $e->response;
      require PAYQR_ERROR_HANDLER.'invoice_action_error.php';
    }
  }

} 