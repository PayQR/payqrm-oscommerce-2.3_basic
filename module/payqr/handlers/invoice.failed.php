<?php
/**
 * Код в этом файле будет выполнен, когда интернет-сайт получит уведомление от PayQR о сбое в совершении покупки (завершении операции).
 * Это означает, что что-то пошло не так в процессе совершения покупки (например, интернет-сайт не ответил во время на уведомление от PayQR), поэтому операция прекращена.
 *
 * $Payqr->objectOrder содержит объект "Счет на оплату" (подробнее об объекте "Счет на оплату" на https://payqr.ru/api/ecommerce#invoice_object)
 *
 * Ниже можно вызвать функции своей учетной системы, чтобы особым образом отреагировать на уведомление от PayQR о событии invoice.failed.
 *
 * Получить orderId из объекта "Счет на оплату", по которому произошло событие, можно через $Payqr->objectOrder->getOrderId();
 */