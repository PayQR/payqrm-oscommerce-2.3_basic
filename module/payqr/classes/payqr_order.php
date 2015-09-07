<?php

class payqr_order
{
  private $type_cart = "cart";
  private $type_product = "product";
  private $type_category = "category";

  private $payqr;
  private $customerObject;
  private $cartObject;
  private $deliveryObject;
  private $selectedDelivery;
  private $userdata;
  private $cart;
  private $customer_id;
  private $total_count;
  private $total_weight;
  private $order_id;
  private $invoice_id;

  public function __construct($Payqr)
  {
    global $cart, $total_weight, $total_count, $customer_id;

    $this->payqr = $Payqr;
    $this->customerObject = $Payqr->objectOrder->getCustomer();
    $this->cartObject = $Payqr->objectOrder->getCart();
    $this->deliveryObject = $Payqr->objectOrder->getDelivery();
    $this->selectedDelivery = $Payqr->objectOrder->getDeliveryCasesSelected();
    $this->order_id = $Payqr->objectOrder->getOrderId();
    $this->invoice_id = $Payqr->objectOrder->getInvId();
    $this->userdata = json_decode($Payqr->objectOrder->getUserData());

    $this->cart = &$cart;
    $this->total_weight = &$total_weight;
    $this->total_count = &$total_count;
    $this->customer_id = &$customer_id;

    $this->setUpSession();
    $this->setUpCart();
  }
  //set up session (if it's cart) and customer
  private function setUpSession()
  {
    session_start();
    session_id($this->userdata->session_id);

    if($this->userdata->customer_id)
    {
      $this->customer_id = $this->userdata->customer_id;
    }
    else
    {
      $email_address = tep_db_prepare_input($this->customerObject->email);

      $query = tep_db_query("select * from ".TABLE_CUSTOMERS." where customers_email_address='$email_address'");
      if($item = tep_db_fetch_array($query))
      {
        $this->customer_id = $item["customers_id"];
      }
      else
      {
        //create new customer
        $helper = new payqr_helper();
        $password = $helper->rand_string(8);
        $sql_data_array = array('customers_firstname' => tep_db_prepare_input($this->customerObject->firstName) . ($this->customerObject->middleName ? " " . tep_db_prepare_input($this->customerObject->middleName) : ""),
                                'customers_lastname' => tep_db_prepare_input($this->customerObject->lastName),
                                'customers_email_address' => $email_address,
                                'customers_telephone' => tep_db_prepare_input($this->customerObject->phone),
                                'customers_fax' => "",
                                'customers_newsletter' => "",
                                'customers_password' => tep_encrypt_password($password));

        tep_db_perform(TABLE_CUSTOMERS, $sql_data_array);

        $name = $firstname . ' ' . $lastname;
        $email_text = "Пароль: $password";
        tep_mail($name, $email_address, EMAIL_SUBJECT, $email_text, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);

        $customer_id = tep_db_insert_id();
        $this->customer_id = $customer_id;
        $address_id = $this->updateDeliveryInfo();
        tep_db_query("update " . TABLE_CUSTOMERS . " set customers_default_address_id = '" . (int)$address_id . "' where customers_id = '" . (int)$customer_id . "'");
       }
    }

    tep_session_register("customer_id");
    $this->cart->restore_contents();
  }
  public function updateDeliveryInfo()
  {
    $address_id = 0;
    $query = tep_db_query("select * from ".TABLE_CUSTOMERS." where customers_id={$this->customer_id}");
    if($item = tep_db_fetch_array($query))
    {
      $delivery = $this->deliveryObject;
      $city = tep_db_prepare_input($delivery->city);
      $postcode = tep_db_prepare_input($delivery->zip);
      $home = (!empty($delivery->house) ? " Дом-" . tep_db_prepare_input($delivery->house) : "") .
              (!empty($delivery->unit) ? " копрус-" . tep_db_prepare_input($delivery->unit) : "") .
              (!empty($delivery->building) ? " строение-" . tep_db_prepare_input($delivery->building) : "") .
              (!empty($delivery->flat) ? " кв-" . tep_db_prepare_input($delivery->flat) : "") .
              (!empty($delivery->hallway) ? " подъезд-" . tep_db_prepare_input($delivery->hallway) : "") .
              (!empty($delivery->floor) ? " этаж-" . tep_db_prepare_input($delivery->floor) : "");

      $street = "ул. " . tep_db_prepare_input($this->deliveryObject->street) . ($home ? ", " . $home : "");
      $query = tep_db_query("select * from ".TABLE_ADDRESS_BOOK." where customers_id={$item["customers_id"]}");
      if($address = tep_db_fetch_array($query))
      {
        $address_id = $address["address_id"];
        $query = "update ".TABLE_ADDRESS_BOOK." set
                  entry_street_address='$street',
                  entry_postcode='$postcode',
                  entry_city='$city'
                  where customers_id={$item["customers_id"]}";
        tep_db_query($query);
      }
      else
      {
        $sql_data_array = array('customers_id' => $item["customers_id"],
                                'entry_firstname' => $item["customers_firstname"],
                                'entry_lastname' => $item["customers_lastname"],
                                'entry_street_address' => $street,
                                'entry_postcode' => $postcode,
                                'entry_city' => $city,
                              );
        tep_db_perform(TABLE_ADDRESS_BOOK, $sql_data_array);
        $address_id = tep_db_insert_id();
      }
    }
    return $address_id;
  }

  //актуализация корзины
  private function setUpCart()
  {
    $contents = array();
    foreach($this->cartObject as $item)
    {
      $attrs = array();
      $id = $item->article;
      if($arr = json_decode($id, true))
      {
        if(is_array($arr))
        {
          $id = $arr["id"];
          $attrs = $arr["attributes"];
        }
      }
      $contents[$id] = array(
        "qty" => $item->quantity,
        "attrs" => $attrs,
      );
    }
    $this->cart->contents = $contents;
    $this->total_weight = $this->cart->show_weight();
    $this->total_count = $this->cart->count_contents();
  }
  public function getDeliveryCases()
  {
    $delivery_cases = array();

    require(DIR_WS_CLASSES . 'shipping.php');
    $shipping_modules = new shipping;
    $quotes = $shipping_modules->quote();

    $number = 1;
    foreach($quotes as $quote)
    {
      if(isset($quote["methods"]))
      {
        foreach($quote["methods"] as $q)
        {
          $delivery_cases[] = array(
            'article' => $quote["id"] . "_" . $q["id"],
            'number' => $number++,
            'name' => "{$quote["module"]} ({$q["title"]})",
            'amountFrom' => $q["cost"],
            'amountTo' => $q["cost"],
          );
        }
      }
    }
    return $delivery_cases;
  }

  public function getCart()
  {
    foreach($this->cartObject as $item)
    {
      foreach($this->cart->get_products() as $cartItem)
      {
        if($item->article == (int)$cartItem["id"])
        {
          $item->amount = $item->quantity * $cartItem["final_price"];
        }
      }
    }
    return $this->cartObject;
  }
  public function getAmount()
  {
    $amount = $this->cart->total;
    if(isset($this->selectedDelivery->amountFrom))
    {
      $amount += $this->selectedDelivery->amountFrom;
    }
    return $amount;
  }

  public function create()
  {
    global $order, $currencies;

    $shipping = array(
      "id"=>$this->selectedDelivery->article,
      "title"=>$this->selectedDelivery->name,
      "cost"=>$this->selectedDelivery->amountFrom
    );

    require(DIR_WS_CLASSES . 'order.php');
    $order = new order;
    $order->info['shipping_method'] = $shipping["title"];
    $order->info['shipping_cost'] = $shipping["cost"];
    $order->info['total'] = $order->info['total'] + $order->info['shipping_cost'];

    require(DIR_WS_CLASSES . 'payment.php');
    $payment_modules = new payment($payment);

    require(DIR_WS_CLASSES . 'shipping.php');
    $shipping_modules = new shipping($shipping);

    require(DIR_WS_CLASSES . 'order_total.php');
    $order_total_modules = new order_total;
    $order_totals = $order_total_modules->process();

    //address
    $customer_info_query = tep_db_query("select * from " . TABLE_CUSTOMERS . " c left join " . TABLE_ADDRESS_BOOK . " ab on c.customers_id = ab.customers_id where c.customers_id = '" . (int)$this->customer_id . "'");
    $customer_info = tep_db_fetch_array($customer_info_query);

    $order->customer['firstname'] = $customer_info["customers_firstname"];
    $order->customer['lastname'] = $customer_info["customers_lastname"];
    $order->customer['format_id'] = tep_get_address_format_id($customer_info["entry_country_id"]);
    $order->customer['city'] = $customer_info["entry_city"];
    $order->customer['street_address'] = $customer_info["entry_street_address"];
    $order->customer['postcode'] = $customer_info["entry_postcode"];
    $order->customer['telephone'] = $customer_info["customers_telephone"];
    $order->customer['email_address'] = $customer_info["customers_email_address"];

    $validOrder = true;
    $any_out_of_stock = false;
    if (STOCK_CHECK == 'true') {
      for ($i=0, $n=sizeof($order->products); $i<$n; $i++) {
        if (tep_check_stock($order->products[$i]['id'], $order->products[$i]['qty'])) {
          $any_out_of_stock = true;
        }
      }
      // Out of Stock
      if ( (STOCK_ALLOW_CHECKOUT != 'true') && ($any_out_of_stock == true) ) {
        $this->cancelOrder();
        $validOrder = false;
      }
    }

    if($validOrder)
    {
      $order->delivery = $order->billing = $order->customer;

      $sql_data_array = array('customers_id' => $this->customer_id,
                              'customers_name' => $order->customer['firstname'] . ' ' . $order->customer['lastname'],
                              'customers_company' => $order->customer['company'],
                              'customers_street_address' => $order->customer['street_address'],
                              'customers_suburb' => $order->customer['suburb'],
                              'customers_city' => $order->customer['city'],
                              'customers_postcode' => $order->customer['postcode'],
                              'customers_state' => $order->customer['state'],
                              'customers_country' => $order->customer['country']['title'],
                              'customers_telephone' => $order->customer['telephone'],
                              'customers_email_address' => $order->customer['email_address'],
                              'customers_address_format_id' => $order->customer['format_id'],
                              'delivery_name' => trim($order->delivery['firstname'] . ' ' . $order->delivery['lastname']),
                              'delivery_company' => $order->delivery['company'],
                              'delivery_street_address' => $order->delivery['street_address'],
                              'delivery_suburb' => $order->delivery['suburb'],
                              'delivery_city' => $order->delivery['city'],
                              'delivery_postcode' => $order->delivery['postcode'],
                              'delivery_state' => $order->delivery['state'],
                              'delivery_country' => $order->delivery['country']['title'],
                              'delivery_address_format_id' => $order->delivery['format_id'],
                              'billing_name' => $order->billing['firstname'] . ' ' . $order->billing['lastname'],
                              'billing_company' => $order->billing['company'],
                              'billing_street_address' => $order->billing['street_address'],
                              'billing_suburb' => $order->billing['suburb'],
                              'billing_city' => $order->billing['city'],
                              'billing_postcode' => $order->billing['postcode'],
                              'billing_state' => $order->billing['state'],
                              'billing_country' => $order->billing['country']['title'],
                              'billing_address_format_id' => $order->billing['format_id'],
                              'payment_method' => $order->info['payment_method'],
                              'cc_type' => $order->info['cc_type'],
                              'cc_owner' => $order->info['cc_owner'],
                              'cc_number' => $order->info['cc_number'],
                              'cc_expires' => $order->info['cc_expires'],
                              'date_purchased' => 'now()',
                              'orders_status' => $order->info['order_status'],
                              'currency' => $order->info['currency'],
                              'currency_value' => $order->info['currency_value']);
      tep_db_perform(TABLE_ORDERS, $sql_data_array);
      $insert_id = tep_db_insert_id();

      for ($i=0, $n=sizeof($order_totals); $i<$n; $i++) {
        $sql_data_array = array('orders_id' => $insert_id,
                                'title' => $order_totals[$i]['title'],
                                'text' => $order_totals[$i]['text'],
                                'value' => $order_totals[$i]['value'],
                                'class' => $order_totals[$i]['code'],
                                'sort_order' => $order_totals[$i]['sort_order']);
        tep_db_perform(TABLE_ORDERS_TOTAL, $sql_data_array);
      }

      for ($i=0, $n=sizeof($order->products); $i<$n; $i++) {
    // Stock Update - Joao Correia
        if (STOCK_LIMITED == 'true') {
          if (DOWNLOAD_ENABLED == 'true') {
            $stock_query_raw = "SELECT products_quantity, pad.products_attributes_filename
                                FROM " . TABLE_PRODUCTS . " p
                                LEFT JOIN " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                                 ON p.products_id=pa.products_id
                                LEFT JOIN " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " pad
                                 ON pa.products_attributes_id=pad.products_attributes_id
                                WHERE p.products_id = '" . tep_get_prid($order->products[$i]['id']) . "'";
    // Will work with only one option for downloadable products
    // otherwise, we have to build the query dynamically with a loop
            $products_attributes = (isset($order->products[$i]['attributes'])) ? $order->products[$i]['attributes'] : '';
            if (is_array($products_attributes)) {
              $stock_query_raw .= " AND pa.options_id = '" . (int)$products_attributes[0]['option_id'] . "' AND pa.options_values_id = '" . (int)$products_attributes[0]['value_id'] . "'";
            }
            $stock_query = tep_db_query($stock_query_raw);
          } else {
            $stock_query = tep_db_query("select products_quantity from " . TABLE_PRODUCTS . " where products_id = '" . tep_get_prid($order->products[$i]['id']) . "'");
          }
          if (tep_db_num_rows($stock_query) > 0) {
            $stock_values = tep_db_fetch_array($stock_query);
    // do not decrement quantities if products_attributes_filename exists
            if ((DOWNLOAD_ENABLED != 'true') || (!$stock_values['products_attributes_filename'])) {
              $stock_left = $stock_values['products_quantity'] - $order->products[$i]['qty'];
            } else {
              $stock_left = $stock_values['products_quantity'];
            }
            tep_db_query("update " . TABLE_PRODUCTS . " set products_quantity = '" . (int)$stock_left . "' where products_id = '" . tep_get_prid($order->products[$i]['id']) . "'");
            if ( ($stock_left < 1) && (STOCK_ALLOW_CHECKOUT == 'false') ) {
              tep_db_query("update " . TABLE_PRODUCTS . " set products_status = '0' where products_id = '" . tep_get_prid($order->products[$i]['id']) . "'");
            }
          }
        }

    // Update products_ordered (for bestsellers list)
        tep_db_query("update " . TABLE_PRODUCTS . " set products_ordered = products_ordered + " . sprintf('%d', $order->products[$i]['qty']) . " where products_id = '" . tep_get_prid($order->products[$i]['id']) . "'");

        $sql_data_array = array('orders_id' => $insert_id,
                                'products_id' => tep_get_prid($order->products[$i]['id']),
                                'products_model' => $order->products[$i]['model'],
                                'products_name' => $order->products[$i]['name'],
                                'products_price' => $order->products[$i]['price'],
                                'final_price' => $order->products[$i]['final_price'],
                                'products_tax' => $order->products[$i]['tax'],
                                'products_quantity' => $order->products[$i]['qty']);
        tep_db_perform(TABLE_ORDERS_PRODUCTS, $sql_data_array);
        $order_products_id = tep_db_insert_id();

    //------insert customer choosen option to order--------
        $attributes_exist = '0';
        $products_ordered_attributes = '';
        if (isset($order->products[$i]['attributes'])) {
          $attributes_exist = '1';
          for ($j=0, $n2=sizeof($order->products[$i]['attributes']); $j<$n2; $j++) {
            if (DOWNLOAD_ENABLED == 'true') {
              $attributes_query = "select popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix, pad.products_attributes_maxdays, pad.products_attributes_maxcount , pad.products_attributes_filename
                                   from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval, " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                                   left join " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " pad
                                    on pa.products_attributes_id=pad.products_attributes_id
                                   where pa.products_id = '" . (int)$order->products[$i]['id'] . "'
                                    and pa.options_id = '" . (int)$order->products[$i]['attributes'][$j]['option_id'] . "'
                                    and pa.options_id = popt.products_options_id
                                    and pa.options_values_id = '" . (int)$order->products[$i]['attributes'][$j]['value_id'] . "'
                                    and pa.options_values_id = poval.products_options_values_id
                                    and popt.language_id = '" . (int)$languages_id . "'
                                    and poval.language_id = '" . (int)$languages_id . "'";
              $attributes = tep_db_query($attributes_query);
            } else {
              $attributes = tep_db_query("select popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval, " . TABLE_PRODUCTS_ATTRIBUTES . " pa where pa.products_id = '" . (int)$order->products[$i]['id'] . "' and pa.options_id = '" . (int)$order->products[$i]['attributes'][$j]['option_id'] . "' and pa.options_id = popt.products_options_id and pa.options_values_id = '" . (int)$order->products[$i]['attributes'][$j]['value_id'] . "' and pa.options_values_id = poval.products_options_values_id and popt.language_id = '" . (int)$languages_id . "' and poval.language_id = '" . (int)$languages_id . "'");
            }
            $attributes_values = tep_db_fetch_array($attributes);

            $sql_data_array = array('orders_id' => $insert_id,
                                    'orders_products_id' => $order_products_id,
                                    'products_options' => $attributes_values['products_options_name'],
                                    'products_options_values' => $attributes_values['products_options_values_name'],
                                    'options_values_price' => $attributes_values['options_values_price'],
                                    'price_prefix' => $attributes_values['price_prefix']);
            tep_db_perform(TABLE_ORDERS_PRODUCTS_ATTRIBUTES, $sql_data_array);

            if ((DOWNLOAD_ENABLED == 'true') && isset($attributes_values['products_attributes_filename']) && tep_not_null($attributes_values['products_attributes_filename'])) {
              $sql_data_array = array('orders_id' => $insert_id,
                                      'orders_products_id' => $order_products_id,
                                      'orders_products_filename' => $attributes_values['products_attributes_filename'],
                                      'download_maxdays' => $attributes_values['products_attributes_maxdays'],
                                      'download_count' => $attributes_values['products_attributes_maxcount']);
              tep_db_perform(TABLE_ORDERS_PRODUCTS_DOWNLOAD, $sql_data_array);
            }
            $products_ordered_attributes .= "\n\t" . $attributes_values['products_options_name'] . ' ' . $attributes_values['products_options_values_name'];
          }
        }
    //------insert customer choosen option eof ----
        $products_ordered .= $order->products[$i]['qty'] . ' x ' . $order->products[$i]['name'] . ' (' . $order->products[$i]['model'] . ') = ' . $currencies->display_price($order->products[$i]['final_price'], $order->products[$i]['tax'], $order->products[$i]['qty']) . $products_ordered_attributes . "\n";
      }

      //insert payqr_invoice
      $query = "insert into payqr_invoice(order_id, invoice_id) values ({$insert_id}, '{$this->invoice_id}')";
      tep_db_query($query);

      return $insert_id;
    }
  }
  private function cancelOrder()
  {
    $this->payqr->objectOrder->cancelOrder();
  }

  public function completeOrder()
  {
    $helper = new payqr_helper();
    $status = $helper->getOption("order_status_paid");
    $query = "update ".TABLE_ORDERS." set orders_status=$status where orders_id={$this->order_id}";
    tep_db_query($query);
    $this->clearCart();
  }

  private function clearCart()
  {
    tep_session_destroy();
    tep_db_query("delete from ".TABLE_CUSTOMERS_BASKET." where customers_id={$this->customer_id}");
  }
}
