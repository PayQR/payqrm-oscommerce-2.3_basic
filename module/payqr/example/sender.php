<?php
/**
 * Примеры запросов в PayQR
 */

require_once '../payqr_config.php';

date_default_timezone_set("Europe/Moscow");

// Методы для работы с объектами "Счет на оплату"

$Payqr_invoice = new payqr_invoice_action();

/**
 * 1. Аннулировать счет на заказ (отображается только у «Счетов на оплату» со статусом new – это значит, что магазин уже обработал уведомление invoice.order.creating, но еще не получил уведомление о событии invoice.paid)
 * Отменить счет до оплаты (отказаться от оплаты) целиком
 * Подробнее https://payqr.ru/api/ecommerce#invoice_cancel
 */
//$r = $Payqr_invoice->invoice_cancel("inv_fm1zJkaSw0IFWYTQPOsOoE");

/**
 * 2. Отменить и вернуть деньги (отображается только у «Счетов на оплату» со статусом paid или revertedPartially).
 * Отменить счет после оплаты (вернуть деньги) на определенную указанную сумму
 * Подробнее https://payqr.ru/api/ecommerce#invoice_revert
 */
//$r = $Payqr_invoice->invoice_revert("inv_gU7URY7x6RxsXUffZqXLCC", '4.0');

/**
 * 3. Досрочно запустить расчеты (отображается только у «Счетов на оплату» со статусами paid, revertedPartially или reverted, и если у них статус подтверждения none)
 * Досрочно подтвердить оплату по счету (запустить финансовые расчеты)
 * Подробнее https://payqr.ru/api/ecommerce#invoice_confirm
 */
//$r = $Payqr_invoice->invoice_confirm("inv_gYmLjgYx4jLj664WdzH4l7");

/**
 * 4. Подтвердить исполнение заказа (отображается только у «Счетов на оплату» со статусами paid или revertedPartially, и если у них статус исполнения заказа none).
 * Подтвердить исполнение заказа по счету (товар доставлен/услуга оказана)
 * Подробнее https://payqr.ru/api/ecommerce#invoice_execution_confirm
 */
//$r = $Payqr_invoice->invoice_execution_confirm("inv_gYmLjgYx4jLj664WdzH4l7");

/**
 * 5. Дослать/изменить сообщение (отображается только у «Счетов на оплату» со статусами paid, revertedPartially или reverted, и если с даты создания «Счета на оплату» из параметра created прошло не больше 259200 минут).
 * Дослать/изменить текстовое сообщение в счете
 * Подробнее https://payqr.ru/api/ecommerce#invoice_message
 *
 * Принимает 4 параметра (идентификатор счета и 3 необязательных), можно указать только необходимые для сообщения.
 */
//$r = $Payqr_invoice->invoice_message("inv_gU7URY7x6RxsXUffZqXLCC", "Сообщение", "http://goods.ru/message.jpg", "http://goods.ru/details");

/**
 * 6. Синхронизировать статус с PayQR (отображается у покупок/заказов с любым «Счетом на оплату» PayQR).
 * 7. Показать историю возвратов (отображается только у «Счетов на оплату» со статусами revertedPartially или reverted).
 * Получить информацию о счете по его идентификатору в PayQR (актуализировать)
 * Подробнее https://payqr.ru/api/ecommerce#invoice_get
 */
//$r = $Payqr_invoice->get_invoice("inv_hDgl5ZXmi6bZLU402aZ7qi");


// Методы для работы с объектами "Возвраты"

$Payqr_revert = new payqr_revert_action();

/**
 * Получить информацию о возврате по его идентификатору в PayQR (актуализировать)
 * Подробнее https://payqr.ru/api/ecommerce#revert_get
 */
//$r = $Payqr_revert->get_revert("rvt_hgBh5pbI0h8zl2kE5vBUfg");