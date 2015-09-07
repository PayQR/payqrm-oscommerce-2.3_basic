<?php
/**
 * Пример работы с конструктором кнопки PayQR
 */
require_once '../payqr_config.php'; // подключаем основной класс

$button = new payqr_button(10); // создаем объект кнопки

// Данные для кнопки PayQR
$button->promo_required = true; // включает сбор промо-кодов или номеров карт лояльности
$button->setPromoDescription("Номер карты Связной-Клуб"); // описание поля для ввода промо-кода или номера карты лояльности

$button->firstname_required = true; // включает запрос имени покупателя
$button->lastname_required = true; // включает запрос фамилии покупателя
$button->phone_required = true; // включает запрос телефона покупателя
$button->email_required = true; // включает запрос e-mail покупателя
$button->delivery_required = true; // включает запрос адреса доставки покупателя
$button->deliverycases_required = true; // включает выбор покупателем способа доставки
$button->pickpoints_required = true; // включает выбор покупателем пункта самовывоза

$button->setMessageText("Скидки весь январь!"); // устанавливает текст сообщения от продавца к покупкам, совершаемым через PayQR
$button->setMessageImageUrl("http://modastuff.ru/image_promo.png"); // устанавливает изображение к тексту сообщения от продавца к покупкам, совершаемым через PayQR
$button->setMessageUrl("http://modastuff.ru/promo_january"); // устаналивает адрес, куда покупатель будет перенаправляться при нажатии на сообщение от продавца к покупкам, совершаемым через PayQR

$button->setOrderId("123123123"); // устанавливает номер заказа (orderId) для случаев, когда он уже известен на уровне генерации кода кнопки PayQR

$button->setUserData("analytics_id"); // устанавливает userdata (любые дополнительные служебные/аналитические данные в свободном формате)

// Изменение стиля кнопки PayQR
$button->setColor("red"); // изменяет цвет кнопки PayQR (доступные варианты в ключах массива payqr_button::color)
$button->setBorderRadius("sharp"); // изменяет границы кнопки PayQR (доступные варианты в ключах массива payqr_button::borderRadius)
$button->setFontWeight("bold"); // изменяет стиль текста в кнопке PayQR (доступные варианты в ключах массива payqr_button::fontWeight)
$button->setFontSize("medium"); // изменяет размер текста в кнопке PayQR (доступные варианты в ключах массива payqr_button::fontSize)
$button->setTextTransform("upper"); // изменяет трансформацию текста в кнопке PayQR (доступные варианты в ключах массива payqr_button::textTransform)
$button->setGradient('flat'); // изменяет градиент в кнопке PayQR (доступные вариант в ключах массива payqr_button::gradient)
$button->setShadow('shadow'); // изменяет тень на кнопке PayQR (доступные вариант в ключах массива payqr_button::shadow)

// Размеры кнопки PayQR
$button->setHeight("80"); // изменяет высоту кнопки PayQR (px)
$button->setWidth("200"); // изменяет ширину кнопки PayQR (px)

?>
<html>
<head>
  <meta charset="utf-8">
  <?= payqr_button::getJs(); ?>
</head>
<body>
  <?= $button->getHtmlButton(); ?>
</body>
</html>