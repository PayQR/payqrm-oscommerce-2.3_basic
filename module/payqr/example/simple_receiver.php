<?php
/**
 * Скрипт принимает и обрабатывает уведомления от PayQR (максимально упрощенный вариант)
 */

require_once '../payqr_config.php'; // подключаем основной класс

try{
  $Payqr = new payqr_receiver(); // создаем объект payqr_receiver
  $idInvoce = $Payqr->receiving(); // получаем Id счета на оплату
  // проверяем тип уведомления от PayQR
  switch ($Payqr->getType()) {
    case 'invoice.order.creating':
      // нужно создать заказ в своей учетной системе, если заказ еще не был создан, и вернуть в PayQR полученный номер заказа (orderId), если его еще не было или он изменился

      // получаем объект доставки
      $deliveryObject = $Payqr->objectOrder->getDelivery();

      // получаем объект корзины
      $cartObject = $Payqr->objectOrder->getCart();

      // получаем информацию о покупателе
      $customerObject = $Payqr->objectOrder->getCustomer();
      // теперь мы можем использовать эти данные, чтобы создать заказ в своей учетной системе

      // после создания заказа в своей учетной системе передаем в PayQR из своей учетной системы orderId созданного заказа
      // для примера получаем случайным образом число
      $orderId = rand(1000, 99999);
      // устанавливаем orderId из своей учетной системы в PayQR
      $Payqr->objectOrder->setOrderId($orderId);

      // при крайней необходимости, изменяем итоговую сумму заказа в счете в PayQR
      // для примера получаем текущую сумму заказа из счета на оплату в PayQR
      $amount = $Payqr->objectOrder->getAmount();
      // для примера изменяем сумму, добавляя 2
      $Payqr->objectOrder->setAmount($amount + 2);

      // если по каким-то причинам нам нужно отменить этот заказ сейчас
      // $Payqr->objectOrder->cancelOrder(); вызов этого метода отменит заказ
      break;
    case 'invoice.paid':
      // нужно зафиксировать успешную оплату конкретного заказа
      // помечаем в своей учетной системе заказ с полученным orderId как успешно оплаченный
      // получим orderId
      $orderID = $Payqr->objectOrder->getOrderId();
      // ниже можно вызвать функции своей учетной системы для того, чтобы изменить статус заказа как успешно оплаченный
      break;
    case 'invoice.failed':
      // ошибка совершения покупки, операция дальше продолжаться не будет
      // получим orderId
      $orderID = $Payqr->objectOrder->getOrderId();
      // ниже можно вызвать функции своей учетной системы для того, чтобы изменить статус заказа

      break;
    default:
  }
  $Payqr->response();
}
catch (payqr_exeption $e){
  $e->getMessage();
}