<?php
/**
 * Скрипт принимает и обрабатывает уведомления от PayQR
 */

require_once '../payqr_config.php'; // подключаем основной класс
try{
$Payqr = new payqr_receiver(); // создаем объект payqr_receiver
$Payqr->receiving(); // получаем Id счета на оплату
  // проверяем тип уведомления от PayQR
  switch ($Payqr->getType()) {
    case 'invoice.deliverycases.updating':
      // нужно вернуть в PayQR список способов доставки для покупателя
	  // возвращаем конкретный список способов доставки
      $delivery_cases = array(
        array(
          'article' => '2002',
          'number' => '1.2',
          'name' => 'Почта России',
          'description' => 'до 3 недель',
          'amountFrom' => '0',
          'amountTo' => '0',
        ),
        array(
          'article' => '2001',
          'number' => '1.1',
          'name' => 'DHL',
          'description' => '1-2 дня',
          'amountFrom' => '0',
          'amountTo' => '70',
        ),
        array(
          'article' => '2003',
          'number' => '1.3',
          'name' => 'PickPoint',
          'description' => '2-3 дня',
          'amountFrom' => '50',
          'amountTo' => '50',
        ),
      );
      $Payqr->objectOrder->setDeliveryCases($delivery_cases);
      break;
    case 'invoice.pickpoints.updating':
      // нужно вернуть в PayQR список пунктов самовывоза для покупателя
	  // возвращаем конкретный список пунктов самовывоза
      $delivery_cases = array(
        array(
          'article' => '1003',
          'number' => '1.3',
          'name' => 'Наш пункт самовывоза 3',
          'description' => 'с 10:00 до 23:00',
          'amountFrom' => '0',
          'amountTo' => '130',
        ),
        array(
          'article' => '1002',
          'number' => '1.1',
          'name' => 'Наш пункт самовывоза 2',
          'description' => 'с 10:00 до 20:00',
          'amountFrom' => '150',
          'amountTo' => '150',
        ),
        array(
          'article' => '1001',
          'number' => '1.1',
          'name' => 'Наш пункт самовывоза 1',
          'description' => 'с 10:00 до 22:00',
          'amountFrom' => '90',
          'amountTo' => '140',
        ),
		        array(
          'article' => '1004',
          'number' => '1.4',
          'name' => 'Наш пункт самовывоза 4',
          'description' => 'круглосуточно',
          'amountFrom' => '130',
          'amountTo' => '0',
        ),
      );
      $Payqr->objectOrder->setPickPointsCases($delivery_cases);
      break;
    case 'invoice.order.creating':
      // нужно создать заказ в своей учетной системе, если заказ еще не был создан, и вернуть в PayQR полученный номер заказа (orderId), если его еще не было или он изменился

      // получаем объект доставки
      $deliveryObject = $Payqr->objectOrder->getDelivery();

      // получаем объект корзины
      $cartObject = $Payqr->objectOrder->getCart();
      // теперь мы можем проверить товары в корзине, изменить содержимое корзины при необходимости
      //
      // обновляем содержимое корзины в счете на оплату в PayQR
      $Payqr->objectOrder->setCart($cartObject);

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
      // ниже можно вызвать функции своей учетной системы для того чтобы изменить статус заказа как успешно оплаченный
      break;
    case 'invoice.failed':
      // ошибка совершения покупки, операция дальше продолжаться не будет
      // получим orderId
      $orderID = $Payqr->objectOrder->getOrderId();
      // ниже можно вызвать функции своей учетной системы для того, чтобы изменить статус заказа
      break;
    case 'invoice.cancelled':
      // PayQR зафиксировал отмену конкретного заказа до его оплаты
      // получим orderId
      $orderID = $Payqr->objectOrder->getOrderId();
      // ниже можно вызвать функции своей учетной системы для того, чтобы изменить статус заказа
      break;
    case 'invoice.reverted':
      // PayQR зафиксировал полную отмену конкретного счета (заказа) и возврат всей суммы денежных средств по нему
      // получим orderId
      $orderID = $Payqr->objectOrder->getOrderId();
      // ниже можно вызвать функции своей учетной системы для того, чтобы изменить статус заказа
      break;
    case 'revert.failed':
      // PayQR отказал интернет-сайту в отмене счета и возврате денежных средств покупателю
      // получим orderId
      $orderID = $Payqr->objectOrder->getOrderId();
      // ниже можно вызвать функции своей учетной системы для того, чтобы изменить статус заказа
      break;
    case 'revert.succeeded':
      // PayQR зафиксировал отмену счета интернет-сайтом и вернул денежные средства покупателю
      // получим orderId
      $orderID = $Payqr->objectOrder->getOrderId();
      // ниже можно вызвать функции своей учетной системы для того, чтобы изменить статус заказа
      break;
    default:
  }
  $Payqr->response();
}
catch (payqr_exeption $e){
  print_r($e->getMessage());
}

