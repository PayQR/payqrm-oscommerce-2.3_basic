/**
 * Библиотека для быстрой интеграции PayQR на интернет-сайты (PHP 5.x)
 * Версия библиотеки: 1.1.1
 * Помощь, вопросы и сообщения об ошибках: api@payqr.ru
 *
 * Рекомендуемые сферы применения: интернет-магазины, онлайн-сервисы, браузерные игры
 *
 * API PayQR и библиотека PayQR обладают большим количеством функций и возможностей, часть из которых нужна только небольшому количеству интернет-сайтов. Для ускорения и упрощения интеграции реализуйте у себя только то, что актуально для бизнес-логики конкретно вашего интернет-сайта, остальное игнорируйте и оставляйте "как есть" (оно по умолчанию работоспособно в библиотеке и уже корректно взаимодействует с API PayQR).
 */


___
Структура библиотеки:
payqr_config.php // файл конфигурации, подключает все необходимые файлы для работы
|- classes
  |- payqr_authentication.php // класс проверки валидности уведомлений и ответов от PayQR (проверки секретных ключей)
  |- payqr_autoload.php // класс автозагрузки классов библиотеки PayQR
  |- payqr_base.php // класс проверки конфигурации библиотеки PayQR
  |- payqr_base_request.php // класс реализации запросов в PayQR
  |- payqr_button.php // класс конструктора кнопки PayQ
  |- payqr_curl.php // класс обертки для cURL
  |- payqr_curl_response.php // класс обработки результатов запросов cURL
  |- payqr_no_curl.php // класс замены cURL в отправке запросов в PayQR, если cURL отсутствует
  |- payqr_exeption.php // класс исключения во время работы с API PayQR
  |- payqr_invoice.php // класс работы с объектами "Счет на оплату"
  |- payqr_invoice_action.php // класс методов по объектам "Счет на оплату"
  |- payqr_json_validator.php // класс JSON валидатора
  |- payqr_logs.php // класс ведения отладочных логов
  |- payqr_numeric_validator.php // класс валидатора чисел
  |- payqr_receiver.php // класс приема уведомлений от PayQR
  |- payqr_revert.php // класс работы с объектами "Возвраты"
  |- payqr_revert_action.php // класс методов по объектам "Возвраты"
|- example // примеры для ознакомления по принятию уведомлений от PayQR и направлению запросов в PayQR
  |- button.php // файл примера работы с конструктором кнопки PayQR
  |- receiver.php // файл примера обработчика уведомлений от PayQR
  |- sender.php // файл примера работы с запросами в PayQR
  |- simple_receiver.php // файл примера обработчика уведомлений от PayQR (минимальная версия обработчика)
|- handlers // файлы обработчиков для уведомлений от PayQR о событиях в PayQR
|- handlers_errors // файлы обработчиков ошибок
  |- invoice_action_error.php // файл обработки ошибок по запросам в PayQR по объектам "Счет на оплату"
  |- revert_action_error.php // файл обработки ошибок по запросам в PayQR по объектам "Возвраты"
  |- reciver_error.php // файл обработки ошибок во время получения уведомлений от PayQR
|-payqr_receiver.php // основной файл, принимающий уведомления от PayQR (абсолютная ссылка на него указывается в личном кабинете PayQR в поле "URL для уведомлений")


___
Для быстрой интеграции с PayQR достаточно:
1. Распаковать архив данной библиотеки PayQR на хостинг своего интернет-сайта (желательно в отдельную папку payqr)
2. Указать в личном кабинете PayQR в настройках своего "Магазина" в поле "URL для уведомлений" ссылку на файл payqr_receiver.php из состава библиотеки PayQR
3. Внести номер магазина (merchId) и секретные ключи (SecretKeyIn и SecretKeyOut) из личного кабинета PayQR в файл payqr_config.php из состава библиотеки PayQR

Этого хватит для того, чтобы совершение покупки в PayQR осуществлялось покупателем без ошибок. Дальше останется только прописать реакцию магазин на те или иные уведомления в файлах папки handlers из состава библиотеки PayQR.


___
Чтобы вы смогли корректно учитывать данные о покупателях и покупках в учетной системе своего интернет-сайта, а также взаимодействовать с PayQR по обмену информацией, вам необходимо вставить свой код реализации логики обработки уведомлений от PayQR в соответствующие файлы из папки handlers.

Названия файлов соответствуют названиям событий, уведомления о которых PayQR будет направлять вашему интернет-сайту (посредством http/https-запросов по адресу, указанному в личном кабинете PayQR в поле "URL для уведомлений"). Подробнее об этих событиях в документации https://payqr.ru/api/ecommerce#event_types. Подробнее о том, как взаимодействовать с API PayQR по объектам "Счет на оплату" и "Возвраты" в комментариях в начале каждого из файлов в папке handlers.


___
Также ваш интернет-сайт может самостоятельно посылать определенные запросы в PayQR. Для этого используйте класс payqr_invoice_action.php для команд и функций по объектам "Счет на оплату" (подробнее https://payqr.ru/api/ecommerce#invoices) и класс payqr_revert_action.php для команд и функций по объектам "Возвраты" (подробнее https://payqr.ru/api/ecommerce#reverts).


___
Пример простейшего подключения PayQR к учетной системе интернет-сайта:

require_once 'payqr_config.php'; // подключаем основной класс

все дальнейшие операции желательно обернуть в try{}catch(payqr_exeption $e)
так как в случае ошибок при работе с API PayQR будут возникать исключения, отлавливая которые можно легко отследить причину любой проблемы

Основные переменные конфигурации содержатся в классе payqr_config.php

$Payqr = new payqr_receiver(); // создаем объект payqr_receiver
$idInvoce = $Payqr->receiving(); // метод получает тело POST-запроса и, в случае успешного разбора, вернет идентификатор счета на оплату (генерируется на стороне PayQR)

$Payqr->getType(); // этот метод вернет тип уведомления согласно https://payqr.ru/api/ecommerce#event_types

$Payqr->objectOrder->setOrderId($orderId); // устанавливаем в объекте "Счет на оплату" в PayQR номер заказа (orderId) из учетной системы своего интернет-сайта

$Payqr->response(); // отправляем ответ PayQR на уведомление от PayQR



/**
 * Базовая инструкция по интеграции "Быстрый старт": https://payqr.ru/api 
 * Справочник API PayQR: https://payqr.ru/api/ecommerce
 * Полная документация по API PayQR: https://payqr.ru/api/doc
 * Ответы на частые вопросы по интеграциям с PayQR: https://payqr.ru/api/faq
 * 
 * API PayQR использует архитектуру REST (http://wikipedia.org/wiki/Representational_state_transfer).
 * API PayQR поддерживает CORS (http://wikipedia.org/wiki/Cross-origin_resource_sharing) для безопасного взаимодействия с приложениями на клиентской стороне.
 * Данные в рамках API PayQR направляются в формате JSON (http://wikipedia.org/wiki/JSON).
 * Уведомления о событиях от PayQR поступают в интернет-сайт обычными http-/https-запросами (http://wikipedia.org/wiki/Webhook).
 * Обновления API PayQR всегда осуществляются с сохранением обратной совместимости.
 */


___
Изменения в версии 1.0.1:
- Добавлена функция payqr_base::getallheaders() для замены getallheaders(), которая недоступна в PHP ниже 5.4 при использовнии FastCGI. В более раниих версиях она была доступна только если PHP был установлен как модуль Apache. Подробнее http://php.net/manual/ru/function.getallheaders.php.
- Добавлена проверка используемой версии PHP. Данная библиотека предназначена для работы на PHP 5.x, для устаревшей версии PHP 4.x доступна специальная версия библиотеки PayQR, которую нужно скачивать отдельно с сайта PayQR.
- Добавлен класс payqr_no_curl.php. Теперь для отправки запросов в PayQR наличие cURL на сервере интернет-сайта необязательно, но PayQR все равно крайне рекомендует использовать именно cURL для осуществления запросов в PayQR. Если на вашем сервере отсутствует cURL, обратитесь к своему системному администратору или в службу поддержки хостинги для его активации, также вы можете установить cURL самостоятельно (подробнее http://php.net/manual/ru/curl.installation.php).

___
Изменения в версии 1.0.2:
- Улучшена система логирования. Теперь логи стали более информативными и подробными.
- Добавлено удаление пробелов при обработке значений переменных из файла конфигурации.

___
Изменения в версии 1.1:
- Добавлен класс payqr_button.php. Теперь коды кнопок PayQR генерировать и размещать на интернет-сайтах стало еще проще.
- В классы payqr_invoice.php и payqr_revert.php добавлен метод определения режима работы получаемых уведомлений от PayQR ("боевой" или "тестовый").

___
Изменения в версии 1.1.1:
- Обновлены классы payqr_json_validator.php и payqr_button.php.