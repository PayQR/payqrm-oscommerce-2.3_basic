<?php

require('includes/application_top.php');

require(DIR_WS_INCLUDES . 'template_top.php');

$html = "";

$order_id = isset($_GET["order_id"]) ? $_GET["order_id"] : 0;
if($order_id > 0)
{
  $order_id = tep_db_prepare_input($order_id);
  $query = tep_db_query("select * from payqr_invoice where order_id='{$order_id}'");
  if($item = tep_db_fetch_array($query))
  {
    $file = realpath(dirname(__FILE__) . "/../payqr/payqr_config.php");
    if(file_exists($file))
    {
      require_once $file;
      $payqr = new PayqrInvoice();
      $payqr->updateOrder();
      $item = (object)$item;
      $html = $payqr->showOrderInfo($item);
    }
  }
}
else
{
  $query = tep_db_query("select * from payqr_invoice order by id desc");
  $html .= "<table style='width:500px'>";
  $html .= "<tr><th>Номер заказа</th><th>Редактировать заказ в PayQR</th><th>Перейти в заказ</th></tr>";
  while($item = tep_db_fetch_array($query))
  {
    $html .= "<tr><td>{$item["order_id"]}</td><td><a href='/admin/payqr_orders.php?order_id={$item["order_id"]}'>редактировать</a></td><td><a href='/admin/invoice.php?oID={$item["order_id"]}'>перейти</a></td></tr>";
  }
  $html .= "</table>";
}


echo $html;

class PayqrInvoice
{
  private function validate_payqr_input()
  {
    $action = $_POST["invoice_action"];
    switch($action)
    {
        case "invoice_revert":
            $revert_amount = $_POST["invoice_revert_amount"];
            $invoice_amount = $_POST["invoice_amount"];
            $invoice_revertAmount = $_POST["invoice_revertAmount"];
            if($revert_amount > $invoice_amount-$invoice_revertAmount)
            {
              echo "<strong class='error'>Сумма возврата должна быть не более текущей суммы заказа</strong>";
              return false;
            }
            break;
        case "invoice_message":
            $text = $_POST["invoice_message_text"];
            $image_url = $_POST["invoice_message_image_url"];
            $click_url = $_POST["invoice_message_click_url"];
            if(empty($text) || empty($image_url) || empty($click_url))
            {
                echo "<strong class='error'>Все поля для отправки сообщения должны быть заполнены</strong>";
                return false;
            }
            break;
    }
    return true;
  }
  public function showOrderInfo($order)
  {
    $html = "<div class='form-item'><a href='/admin/payqr_orders.php'><- Вернуться к списку</a></div>";
    $Payqr_invoice = new payqr_invoice_action();
    $invoice = $Payqr_invoice->get_invoice($order->invoice_id);
    $invoice = json_decode($invoice);
    if($invoice)
    {
        $html .= "<form method='post'>";
        $html .= "<div style='margin-bottom:20px'>";
        $html .= "<input type='hidden' name='invoice_id' value='{$invoice->id}'/>";
        $html .= "<div class='item_div'>PayQR.order_info</div>";
        $payqrFields = array(
            "id" => "ID",
            "status" => "Статус",
            "executionStatus" => "Cтатус исполнения заказа",
            "confirmStatus" => "Cтатус подтверждения заказа",
            "payqrNumber" => "Номер инвойса",
            "orderId" => "ID заказа",
            "amount" => "Сумма",
            "revertAmount" => "Сумма возврата",
        );
        $html .= "<table class='payqr'>";
        $k=0;
        foreach($payqrFields as $key=>$field)
        {
            $html .= "<tr class='".($k%2 == 0 ? "odd" : "even")."'><td>{$field}</td><td>{$invoice->$key}</td></tr>";
            $k++;
        }
        $html .= "</table>";
        $html .= "<div class='item_div'>Товары в заказе</div>";
        $html .= "<table class='payqr'><tr><th>ID</th><th>кол-во</th><th>сумма</th></tr>";
        foreach($invoice->cart as $k=>$item)
        {
            $html .= "<tr class='".($k%2 == 0 ? "odd" : "even")."'><td>{$item->article}</td><td>{$item->quantity}</td><td>{$item->amount}</td></tr>";
        }
        $html .= "</table>";
        $html .= "<div class='item_div'>Действия</div>";
        //7 cases for payqr orders
        $html .= "<div class='form-item'><label>Ничего не выполнять: <input type='radio' name='invoice_action' value='invoice_no_action' checked/></label></div>";
        if($invoice->status == "new")
        {
            $html .= "<div class='form-item'><label>Аннулировать счет на заказ: <input type='radio' name='invoice_action' value='invoice_cancel'/></label></div>";
        }
        elseif($invoice->status != "cancelled" && $invoice->status != "failed")
        {
            if($invoice->status == "paid" || $invoice->status == "revertedPartially")
            {
                $html .= "<div class='form-item'><label>Отменить заказ после оплаты: <input class='invoice_check' text='PayQR.invoice_revert' type='radio' name='invoice_action' value='invoice_revert'/></label>";
                $revert_amount_value = $invoice->amount - $invoice->revertAmount;
                $html .= "<input type='hidden' name='invoice_amount' value='{$invoice->amount}'/>";
                $html .= "<input type='hidden' name='invoice_revertAmount' value='{$invoice->revertAmount}'/>";
                $html .= "<div><label>PayQR.invoice_revert_amount: <input type='text' name='invoice_revert_amount' value='$revert_amount_value' class='form-text'/></label><div>";
                $html .= "</div>";
            }
            if(($invoice->status == "paid" || $invoice->status == "revertedPartially" || $invoice->status == "reverted") && $invoice->confirmStatus == "None")
            {
                $html .= "<div class='form-item'><label>PayQR.invoice_confirm: <input class='invoice_check' text='PayQR.invoice_confirm' type='radio' name='invoice_action' value='invoice_confirm'/></label></div>";
            }
            if(($invoice->status == "paid" || $invoice->status == "revertedPartially") && $invoice->executionStatus == "None")
            {
                $html .= "<div class='form-item'><label>Подтвердить исполнение заказа: <input class='invoice_check' text='PayQR.invoice_execution_confirm' type='radio' name='invoice_action' value='invoice_execution_confirm'/></label></div>";
            }
            $time_since_created = round((time()-strtotime($invoice->created))/60);
            if($time_since_created < 259200 && ($invoice->status == "paid" || $invoice->status == "revertedPartially" || $invoice->status == "reverted"));
            {
                $html .= "<div class='form-item'><label>Дослать/изменить сообщение: <input class='invoice_check' text='PayQR.invoice_message' text='PayQR.invoice_message' type='radio' name='invoice_action' value='invoice_message'/></label>";
                $html .= "<div><label>Текст сообщения к покупке: <input type='text' name='invoice_message_text' value='' class='form-text'/></label></div>";
                $html .= "<div><label>URL изображения для сообщения к покупке: <input type='text' name='invoice_message_image_url' value='' class='form-text'/></label></div>";
                $html .= "<div><label>URL сайта для сообщения к покупке: <input type='text' name='invoice_message_click_url' value='' class='form-text'/></label></div></div>";
            }
            $html .= "<div class='form-item'><label>Синхронизировать статус с PayQR: <input class='invoice_check' text='PayQR.invoice_sync_data' type='radio' name='invoice_action' value='invoice_sync_data'/></label></div>";
            $html .= "<div class='form-item'><label>Показать историю возвратов: <input type='radio' name='invoice_action' value='invoice_show_history'/></label></div>";
        }
        $html .= "</div>";
        $html .= "<input type='submit' value='Выполнить'>";
        $html .= "</form>";
    }
    else
    {
        $html = "<strong>Нет данных в системе PayQR</strong>";
    }
    $html .= "<style>
    table.payqr {
        font-size: 0.923em;
        margin: 0 0 10px;
        border: 1px solid #bebfb9;
    }
    tr.even, tr.odd {
        border-width: 0 1px 0 1px;
        border-style: solid;
        border-color: #bebfb9;
        background: #f3f4ee;
    }
    tr.odd {
        background: #fff;
    }
    .form-item {
        padding: 9px 0;
        margin: 0 0 10px;
    }
    .error
    {
      color: red;
    }
    </style>";
    return $html;
  }
  public function updateOrder()
  {
    if(isset($_POST["invoice_action"]))
    {
      $order_id = $_POST["order_id"];
      $action = $_POST["invoice_action"];
      $invoice_id = $_POST["invoice_id"];
      $helper = new payqr_helper();
      if($this->validate_payqr_input())
      {
        switch($action)
        {
            case "invoice_cancel":
                $Payqr_invoice->invoice_cancel($invoice_id);
                $helper->cancelOrder($order_id);
                break;
            case "invoice_revert":
                $revert_amount = $_POST["invoice_revert_amount"];
                $Payqr_invoice->invoice_revert($invoice_id, $revert_amount);
                break;
            case "invoice_confirm":
                $Payqr_invoice->invoice_confirm($invoice_id);
                break;
            case "invoice_execution_confirm":
                $Payqr_invoice->invoice_execution_confirm($invoice_id);
                break;
            case "invoice_execution_confirm":
                $text = $_POST["invoice_message_text"];
                $image_url = $_POST["invoice_message_image_url"];
                $click_url = $_POST["invoice_message_click_url"];
                $Payqr_invoice->invoice_message($invoice_id, $text, $image_url, $click_url);
                break;
            case "invoice_sync_data":
                if($invoice)
                {
                  //удаляем элементы которых нет во втором массиве
                  $order_id = tep_db_prepare_input($order_id);
                  $query = tep_db_query("select * from ".TABLE_ORDERS_PRODUCTS." where orders_id=$order_id");
                  while($item = tep_db_fetch_array($query))
                  {
                    $item = (object)$item;
                    $exist = false;
                    foreach($invoice->cart as $cartItem)
                    {
                      if($cartItem->article == $item->shop_item_id){
                        $exist = true;
                      }
                    }
                    if($exist == false)
                    {
                      tep_db_query("delete ".TABLE_ORDERS_PRODUCTS." where products_id={$item->products_id}");
                    }
                  }

                  //Добавляем элементы которых нет в первом массиве

                  foreach($invoice->cart as $cartItem)
                  {
                    $exist = false;
                    foreach($items as $item)
                    {
                      if($cartItem->article == $item->shop_item_id){
                        $exist = true;
                      }
                    }
                    if($exist == false)
                    {
                      $sql_data_array = array('orders_id' => $order_id,
                                              'products_id' => tep_db_prepare_input($cartItem->article),
                                              'products_quantity' => tep_db_prepare_input($cartItem->quantity));
                      tep_db_perform(TABLE_ORDERS_PRODUCTS, $sql_data_array);
                    }
                  }
                }
                break;
        }
      }
    }
  }
}

require(DIR_WS_INCLUDES . 'template_bottom.php');
require(DIR_WS_INCLUDES . 'application_bottom.php');
