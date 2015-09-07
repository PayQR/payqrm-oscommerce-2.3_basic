<?php

require('includes/application_top.php');

require(DIR_WS_INCLUDES . 'template_top.php');
$cl_box_groups = array();

$install = isset($_GET["install"]) ? $_GET["install"] : 0;
if($install == 1)
{
    $query = tep_db_query("SHOW TABLES LIKE 'payqr_settings'");
    $res = tep_db_fetch_array($query);
    if($res)
    {
      echo "Модуль уже установлен";
    }
    else
    {
      //установка таблицы payqr_invoice
      $query = "CREATE TABLE IF NOT EXISTS `payqr_invoice` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
                `order_id` int(11) NOT NULL,
                `invoice_id` varchar(255) NOT NULL,
                PRIMARY KEY (`id`)
              ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";
      tep_db_query($query);

      //установка таблички с настройками кнопки
      $query = "CREATE TABLE IF NOT EXISTS `payqr_settings` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                  `key` varchar(255) NOT NULL,
                  `name` varchar(255) NOT NULL,
                  `value` varchar(255) DEFAULT NULL,
                  `value_list` text,
                  `parent_id` int(11) NOT NULL DEFAULT '0',
                  `static` int(11) NOT NULL DEFAULT '0',
                  `published` int(11) NOT NULL DEFAULT '1',
                  `sort_order` int(11) NOT NULL DEFAULT '0',
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB AUTO_INCREMENT=64 DEFAULT CHARSET=utf8;";
      tep_db_query($query);

      $log_key = md5(uniqid());
      $query = "INSERT INTO `payqr_settings` (`id`, `key`, `name`, `value`, `value_list`, `parent_id`, `static`, `published`, `sort_order`) VALUES
                (1, 'base_options', 'Базовые настройки', NULL, NULL, 0, 0, 1, 10),
                (2, 'button-options', 'Настройки кнопки', NULL, NULL, 0, 0, 1, 20),
                (3, 'order-options', 'Статусы заказов', NULL, NULL, 0, 0, 1, 40),
                (4, 'required-options', 'Запрашиваемые поля', NULL, NULL, 0, 0, 1, 30),
                (6, 'handle_url', 'URL PayQR обработчика', 'http://{$_SERVER['SERVER_NAME']}/payqr.php?type=handler', '', 1, 1, 1, 10),
                (8, 'log_url', 'URL PayQR логов', 'http://{$_SERVER['SERVER_NAME']}/payqr.php?type=log&key={$log_key}', '', 1, 1, 1, 30),
                (9, 'merch_id', 'PayQR merchant ID', '', '', 1, 0, 1, 40),
                (10, 'sercer_key_in', 'PayQR SecretKeyIn', '', '', 1, 0, 1, 50),
                (11, 'secret_key_out', 'PayQR SecretKeyOut', '', '', 1, 0, 1, 60),
                (14, 'data-middlename-required', 'Запрашивать отчество покупателя', 'deny', '{\"deny\":\"Нет\",\"required\":\"Да\"}', 4, 0, 1, 30),
                (15, 'data-phone-required', 'Запрашивать номер телефона покупателя', 'deny', '{\"deny\":\"Нет\",\"required\":\"Да\"}', 4, 0, 1, 40),
                (17, 'data-delivery-required', 'Запрашивать адрес доставки', 'deny', '{\"deny\":\"Нет\",\"required\":\"Да\",\"notrequired\":\"Не обязательно\"}', 4, 0, 1, 120),
                (18, 'data-deliverycases-required', 'Могут ли быть в магазине способы доставки', 'deny', '{\"deny\":\"Нет\",\"required\":\"Да\"}', 4, 0, 1, 130),
                (19, 'data-pickpoints-required', 'Могут ли быть в магазине точки самовывоза', 'deny', '{\"deny\":\"Нет\",\"required\":\"Да\"}', 4, 0, 1, 140),
                (20, 'data-promo-required', 'Предлагать ввести промо-идентификатор', 'deny', '{\"deny\":\"Нет\",\"required\":\"Да\"}', 4, 0, 1, 150),
                (21, 'data-promo-description', 'Текстовое название промо-идентификатора', '', '', 4, 0, 1, 160),
                (22, 'data-message-text', 'Сообщение в покупке после ее совершения', '', NULL, 4, 0, 1, 170),
                (23, 'data-message-imageurl', 'URL изображения в покупке после ее совершения', '', NULL, 4, 0, 1, 180),
                (24, 'data-message-url', 'URL ссылка на сайт продавца в покупке после ее совершения', '', NULL, 4, 0, 1, 190),
                (25, 'button-show-on-cart', 'Показывать кнопку PayQR на страничке корзины', '0', '[\"Нет\",\"Да\"]', 2, 0, 1, 200),
                (26, 'button-show-on-product', 'Показывать кнопку PayQR на страничке карточки товара', '0', '[\"Нет\",\"Да\"]', 2, 0, 1, 210),
                (27, 'button-show-on-category', 'Показывать кнопку PayQR на страничке категории товаров', '0', '[\"Нет\",\"Да\"]', 2, 0, 1, 220),
                (28, 'cart_button_color', 'Цвет кнопки (корзина)', 'payqr-button_red', '{\"default\":\"По умолчанию\",\"payqr-button_green\":\"Зелёный\",\"payqr-button_blue\":\"Синий\",\"payqr-button_orange\":\"Оранжевый\",\"payqr-button_red\":\"Красный\"}', 25, 0, 1, 230),
                (29, 'cart_button_form', 'Округление краев кнопки (корзина)', 'default', '{\"default\":\"По умолчанию\",\"payqr-button_sharp\":\"без округления\",\"payqr-button_rude\":\"минимальное округление\",\"payqr-button_soft\":\"мягкое округление\",\"payqr-button_sleek\":\"значительное округление\",\"payqr-button_oval\":\"максимальное округление\"}', 25, 0, 1, 240),
                (30, 'cart_button_shadow', 'Тень кнопки (корзина)', 'default', '{\"default\":\"По умолчанию\",\"payqr-button_shadow\":\"включена\",\"payqr-button_noshadow\":\"отключена\"}', 25, 0, 1, 250),
                (31, 'cart_button_gradient', 'Градиент кнопки (корзина)', 'payqr-button_flat', '{\"default\":\"По умолчанию\",\"payqr-button_flat\":\"отключен\",\"payqr-button_gradient\":\"включен\"}', 25, 0, 1, 260),
                (32, 'cart_button_text_size', 'Размер текста кнопки (корзина)', 'default', '{\"default\":\"По умолчанию\",\"payqr-button_text-small\":\"мелко\",\"payqr-button_text-medium\":\"средне\",\"payqr-button_text-large\":\"крупно\"}', 25, 0, 1, 270),
                (33, 'cart_button_text_width', 'Текст кнопки жирным (корзина)', 'default', '{\"default\":\"По умолчанию\",\"payqr-button_text-normal\":\"отключен\",\"payqr-button_text-bold\":\"включен\"}', 25, 0, 1, 280),
                (34, 'cart_button_text_case', 'Регистр текста кнопки (корзина)', 'default', '{\"default\":\"По умолчанию\",\"payqr-button_text-lowercase\":\"нижний\",\"payqr-button_text-standartcase\":\"стандартный\",\"payqr-button_text-uppercase\":\"верхний\"}', 25, 0, 1, 290),
                (35, 'cart_button_height', 'Высота кнопки (корзина)', 'auto', NULL, 25, 0, 1, 300),
                (36, 'cart_button_width', 'Ширина кнопки (корзина)', 'auto', NULL, 25, 0, 1, 310),
                (37, 'product_button_color', 'Цвет кнопки (карточка товара)', 'default', '{\"default\":\"По умолчанию\",\"payqr-button_green\":\"Зелёный\",\"payqr-button_blue\":\"Синий\",\"payqr-button_orange\":\"Оранжевый\",\"payqr-button_red\":\"Красный\"}', 26, 0, 1, 320),
                (38, 'product_button_form', 'Округление краев кнопки (карточка товара)', 'default', '{\"default\":\"По умолчанию\",\"payqr-button_sharp\":\"без округления\",\"payqr-button_rude\":\"минимальное округление\",\"payqr-button_soft\":\"мягкое округление\",\"payqr-button_sleek\":\"Sleek\",\"payqr-button_oval\":\"Oval\"}', 26, 0, 1, 330),
                (39, 'product_button_shadow', 'Тень кнопки (карточка товара)', 'default', '{\"default\":\"По умолчанию\",\"payqr-button_shadow\":\"включена\",\"payqr-button_noshadow\":\"отключена\"}', 26, 0, 1, 340),
                (40, 'product_button_gradient', 'Градиент кнопки (карточка товара)', 'default', '{\"default\":\"По умолчанию\",\"payqr-button_flat\":\"отключен\",\"payqr-button_gradient\":\"включен\"}', 26, 0, 1, 350),
                (41, 'product_button_text_size', 'Размер текста кнопки (карточка товара)', 'default', '{\"default\":\"По умолчанию\",\"payqr-button_text-small\":\"мелко\",\"payqr-button_text-medium\":\"средне\",\"payqr-button_text-large\":\"крупно\"}', 26, 0, 1, 360),
                (42, 'product_button_text_width', 'Текст кнопки жирным (карточка товара)', 'default', '{\"default\":\"По умолчанию\",\"payqr-button_text-normal\":\"отключен\",\"payqr-button_text-bold\":\"включен\"}', 26, 0, 1, 370),
                (43, 'product_button_text_case', 'Регистр текста кнопки (карточка товара)', 'default', '{\"default\":\"По умолчанию\",\"payqr-button_text-lowercase\":\"нижний\",\"payqr-button_text-standartcase\":\"стандартный\",\"payqr-button_text-uppercase\":\"верхний\"}', 26, 0, 1, 380),
                (44, 'product_button_height', 'Высота кнопки (карточка товара)', 'auto', NULL, 26, 0, 1, 390),
                (45, 'product_button_width', 'Ширина кнопки (карточка товара)', 'auto', NULL, 26, 0, 1, 400),
                (46, 'category_button_color', 'Цвет кнопки (категория товаров)', 'default', '{\"default\":\"По умолчанию\",\"payqr-button_green\":\"Зелёный\",\"payqr-button_blue\":\"Синий\",\"payqr-button_orange\":\"Оранжевый\",\"payqr-button_red\":\"Красный\"}', 27, 0, 1, 410),
                (47, 'category_button_form', 'Округление краев кнопки (категория товаров)', 'default', '{\"default\":\"По умолчанию\",\"payqr-button_sharp\":\"без округления\",\"payqr-button_rude\":\"минимальное округление\",\"payqr-button_soft\":\"мягкое округление\",\"payqr-button_sleek\":\"Sleek\",\"payqr-button_oval\":\"Oval\"}', 27, 0, 1, 420),
                (48, 'category_button_shadow', 'Тень кнопки (категория товаров)', 'default', '{\"default\":\"По умолчанию\",\"payqr-button_shadow\":\"включена\",\"payqr-button_noshadow\":\"отключена\"}', 27, 0, 1, 430),
                (49, 'category_button_gradient', 'Градиент кнопки (категория товаров)', 'default', '{\"default\":\"По умолчанию\",\"payqr-button_flat\":\"отключен\",\"payqr-button_gradient\":\"включен\"}', 27, 0, 1, 440),
                (50, 'category_button_text_size', 'Размер текста кнопки (категория товаров)', 'default', '{\"default\":\"По умолчанию\",\"payqr-button_text-small\":\"мелко\",\"payqr-button_text-medium\":\"средне\",\"payqr-button_text-large\":\"крупно\"}', 27, 0, 1, 450),
                (51, 'category_button_text_width', 'Текст кнопки жирным (категория товаров)', 'default', '{\"default\":\"По умолчанию\",\"payqr-button_text-normal\":\"отключен\",\"payqr-button_text-bold\":\"включен\"}', 27, 0, 1, 460),
                (52, 'category_button_text_case', 'Регистр текста кнопки (категория товаров)', 'default', '{\"default\":\"По умолчанию\",\"payqr-button_text-lowercase\":\"нижний\",\"payqr-button_text-standartcase\":\"стандартный\",\"payqr-button_text-uppercase\":\"верхний\"}', 27, 0, 1, 470),
                (53, 'category_button_height', 'Высота кнопки (категория товаров)', 'auto', NULL, 27, 0, 1, 480),
                (54, 'category_button_width', 'Ширина кнопки (категория товаров)', 'auto', NULL, 27, 0, 1, 490),
                (55, 'order_status_created', 'Заказ создан но не оплачен (invoice.order.creating)', '2', '', 3, 0, 1, 500),
                (56, 'order_status_paid', 'Заказ оплачен (invoice.paid)', '2', '', 3, 0, 1, 510),
                (57, 'order_status_canceled', 'Заказ отменён', '1', '', 3, 0, 1, 520),
                (59, 'log_path', 'Путь к файлу логов', 'payqr/logs/payqr.log', NULL, 1, 0, 1, 11),
                (60, 'log_key', 'Ключ доступа к логам', '{$log_key}', NULL, 1, 0, 1, 12),
                (61, 'data-firstname-required', 'Запрашивать имя покупателя', NULL, '{\"deny\":\"Нет\",\"required\":\"Да\"}', 4, 0, 0, 10),
                (62, 'data-lastname-required', 'Запрашивать фамилию покупателя', NULL, '{\"deny\":\"Нет\",\"required\":\"Да\"}', 4, 0, 0, 20),
                (63, 'data-email-required', 'Запрашивать адрес электронной почты', NULL, '{\"deny\":\"Нет\",\"required\":\"Да\"}', 4, 0, 0, 50);";

      tep_db_query($query);
    }
}

if(isset($_POST["PayqrSettings"]))
{
  foreach($_POST["PayqrSettings"] as $key=>$value)
  {
    $key = tep_db_prepare_input($key);
    $query = tep_db_query("select * from payqr_settings where `key`='$key'");
    if($item = tep_db_fetch_array($query))
    {
      if($item["key"] == "log_url")
      {
        $token = "&key=";
        $value = explode($token, $value);
        $value = $value[0] . $token . $_POST["PayqrSettings"]["log_key"];
      }
      $r = tep_db_query("update payqr_settings set `value`='$value' where `key`='$key'");
    }
  }
}


$settings = new PayqrSettingsHtml();
$html = $settings->getHtml();
echo $html;

require(DIR_WS_INCLUDES . 'template_bottom.php');
require(DIR_WS_INCLUDES . 'application_bottom.php');



class PayqrSettingsHtml
{
  public function getHtml()
  {
    $html = "<H1>Настройки PayQR</H1>";
    $html .= "<div class='form'><form method='post'>";
    $html .= $this->getHtmlRec(0);
    $html .= "<div class='row'><input type='submit' value='Сохранить'/></form></div>";
    $html .= "<div><span style='color:red'>*</span>Высота и Ширина кнопки указываются в px или %, например 10px или 20%</div>";
    return $html;
  }
  private function getHtmlRec($id)
  {
    $html = "";
    $query = tep_db_query("select * from payqr_settings where parent_id={$id} and published=1 order by sort_order");
    while($item = tep_db_fetch_array($query))
    {
      $item = (object)$item;
      $html .= "<li class='row' id='{$item->id}'>";
      $html .= $this->getRow($item);
      $html .= "<ul id='child_{$item->id}' class='$item->key children'>";
      $html .= $this->getHtmlRec($item->id);
      $html .= "</ul>";
      $html .= "</li>";
    }
    return $html;
  }
  private function getRow($item)
  {
    $html = "";
    if($item->parent_id == 0)
    {
      $html .= "<a href='javascript:void(0)'>$item->name</a>";
    }
    else
    {
      $html .= "<label for='{$item->key}'>{$item->name}</label>";

      $text_attr = $this->get_attr_str($item, "text");
      $select_attr = $this->get_attr_str($item, "select");

      if(!empty($item->value_list))
      {
        $html .= "<select $select_attr>";
        foreach(json_decode($item->value_list) as $key=>$val)
        {
          $s = "";
          if($key == $item->value){
            $s = "selected='selected'";
          }
          $html .= "<option value='$key' $s>$val</option>";
        }
        $html .= "</select>";
      }
      elseif(substr($item->key, 0, 12) == "order_status")
      {
        $html .= "<select $select_attr>";
        $osQuery = tep_db_query("select * from orders_status where public_flag = 1");
        while($osItem = tep_db_fetch_array($osQuery))
        {
          $s = "";
          if($osItem["orders_status_id"] == $item->value){
            $s = "selected='selected'";
          }
          $html .= "<option value='{$osItem["orders_status_id"]}' $s>{$osItem["orders_status_name"]}</option>";
        }
        $html .= "</select>";
      }
      else{
        $html .= "<input type='text' $text_attr/>";
      }
    }
    return $html;
  }

  private function get_attr_str($item, $type)
  {
    $text = "text";
    $select = "select";

    $attr = array();
    if($item->static == 1){
      $attr["readonly"] = "readonly";
      $attr["style"] = "background-color: #eee;";
    }
    $attr["id"] = $item->key;
    $attr["name"] = "PayqrSettings[{$item->key}]";
    $attr["value"] = $item->value;
    if($type == $text)
    {
      $attr["size"] = strlen($item->value);
    }
    $attr_str = "";
    foreach($attr as $key=>$val)
    {
      $attr_str .= "$key='$val' ";
    }
    $attr_str = trim($attr_str);
    return $attr_str;
  }
}
?>
<style>
div.form .row
{
  margin: 5px 0;
}
div.form label {
  font-weight: bold;
  font-size: 0.9em;
  display: block;
}
div.form input, div.form textarea, div.form select {
  margin: 0.2em 0 0.5em 0;
}
.children
{
  display: none;
}
.base_options
{
  display: block;
}
</style>

<script>
  $("li.row a").click(function(){
    var id = "#child_" + $(this).parent().attr("id");
    $(id).toggle();
  });
  $("li.row select").change(function(){
    var id = "#child_" + $(this).parent().attr("id");
    var val = $(this).val();
    if(val == 1){
      $(id).show();
    }
    else {
      $(id).hide();
    }
  });
  $("li.row select").each(function(){
    var id = "#child_" + $(this).parent().attr("id");
    var val = $(this).val();
    if(val == 1){
      $(id).show();
    }
  });
</script>
