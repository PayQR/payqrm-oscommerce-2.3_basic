<?php
/**
 * Прием уведомлений от PayQR
 */

class payqr_receiver
{

  public $objectResponse; // объект уведомления

    /**
     * @var payqr_invoice
     */
  public $objectOrder; // объект события

  public function __construct()
  {
    payqr_base::checkConfig();
    payqr_logs::log(str_repeat("=", 200) . "\n" . file_get_contents("php://input"));
    $this->objectResponse = false;
    $this->objectOrder = false;
    $this->orderId = 0;
    $this->errors = array();
  }

  /**
   * Получаем уведомление
   */
  public function receiving()
  {
    // Проверяем что уведомление действительно от PayQR (по секретному ключу SecretKeyIn)
    if (!payqr_authentication::checkHeader(payqr_config::$secretKeyIn)) {
      // если уведомление пришло не от PayQR, вызываем исключение
      throw new payqr_exeption("Неверный параметр ['PQRSecretKey'] в header уведомления", 1);
    }
    // Получаем данные из тела запроса
    $json = file_get_contents('php://input');
    // Проверяем валидность JSON данных
    payqr_json_validator::validate($json);
    // Получаем объект события из уведомления
    $this->objectEvent = json_decode($json);
    // Проверяем наличие свойства типа объекта
    if (!isset($this->objectEvent->object) || !isset($this->objectEvent->id) || !isset($this->objectEvent->type)) {
      throw new payqr_exeption("В уведомлении отстутствуют обязательные параметры object id type", 1, $json);
    }
    // Сохраняем тип уведомления
    $this->type = $this->objectEvent->type;

    payqr_logs::log("invoice.type: " . $this->type);

    // В зависимости от того какого типа уведомление, создаем объект
    switch ($this->objectEvent->data->object) {
      case 'invoice':
        $this->objectOrder = new payqr_invoice($this->objectEvent->data, $this->objectEvent->livemode);
        break;
      case 'revert':
        $this->objectOrder = new payqr_revert($this->objectEvent->data, $this->objectEvent->livemode);
        break;
      default:
        throw new payqr_exeption("В уведомлении отстутствуют обязательные параметры object id type", 1, $json);
        return false;
    }
    payqr_logs::log("Идентификатор счета на оплату: " . $this->objectEvent->id);
    // если все прошло успешно, возвращаем идентификатор счета на оплату
    return $this->objectEvent->id;
  }

  /**
   * Ответ на уведомление
   */
  public function response()
  {
    header("PQRSecretKey:" . payqr_config::$secretKeyOut);
    if($this->objectOrder->cancel){ // если счет отмечен как отмененный
      header("HTTP/1.1 409 Conflict");
      payqr_logs::log('payqr_receiver::response() - Send HTTP/1.1 409 Conflict');
      return;
    }
    header("HTTP/1.1 200 OK");
      echo json_encode($this->objectEvent);
//      require_once __DIR__ . "/../../payqr_for_php4/classes/payqr_json.php";
//      $json = new Services_JSON;
//      $json_encode =  $json->encode($this->objectEvent);
//      echo $json_encode;
//      payqr_logs::log($json_encode."\n\r");
    payqr_logs::log('payqr_receiver::response()');
      payqr_logs::log(json_encode($this->objectEvent)."\n\r");
  }


  /**
   * Возвращает тип уведомления
   * @return mixed
   */
  public function getType()
  {
    return $this->type;
  }

}
