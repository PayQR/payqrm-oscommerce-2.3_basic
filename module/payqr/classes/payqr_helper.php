<?php

class payqr_helper
{
  private $type_cart = "cart";
  private $type_product = "product";
  private $type_category = "category";
  private $scenario = "buy";
  private $options = array();

    private function getScript()
    {
      $url = "https://payqr.ru/popup.js?merchId=" . $this->getOption("merch_id");
      return $url;
    }
    public function showScript()
    {
      $script = "<script type='text/javascript' src='{$this->getScript()}'></script>";
      $script .= "<script type='text/javascript'>
      $(function() {
          payQR.onPaid(function(data) {
            console.log(data);
            var msg = 'Ваш заказ # ' + data.orderId + ' успешно создан и оплачен';
            alert(msg);
            window.location.href = '/';
          });
      });
      </script>";
      return $script;
    }
    public function rand_string($length)
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        return substr(str_shuffle($chars),0,$length);
    }

    private function get_button_html($scenario, $products, $type)
    {
      $data = $this->get_data($scenario, $products, $type);
      $html = "<button";
      foreach($data as $attr=>$value)
      {
        if(is_array($value))
        {
          $value = implode(" ", $value);
        }
        if(!empty($value))
        {
          $html .= " $attr='$value'";
        }
      }
      $html .= ">buy</button>";
      return $html;
    }
    /**
     * @param $scenario
     * @param array $data
     * @return array|bool
     */
    private function get_data($scenario, $products, $type) {
      global $customer_id;
      $allowed_scenario = array('buy', 'pay');
      if (!in_array($scenario, $allowed_scenario)) {
        watchdog('commerce PayQR', 'Unallowed scenario');
        return FALSE;
      }
      $data = array();
      $data['data-scenario'] = $scenario;


      $cart_data = $products;
      $data_amount = 0;
      foreach ($cart_data as $item) {
        $data_amount += $item['amount'];
      }
      $data['data-amount'] = $data_amount;
      $data['data-cart'] = json_encode($cart_data);
      $data['data-firstname-required'] = "required";
      $data['data-lastname-required'] = "required";
      $data['data-middlename-required'] = $this->getOption('data-middlename-required');
      $data['data-phone-required'] = $this->getOption('data-phone-required');
      $data['data-email-required'] = "required";
      $data['data-delivery-required'] = $this->getOption('data-delivery-required');
      $data['data-deliverycases-required'] = $this->getOption('data-deliverycases-required');
      $data['data-pickpoints-required'] = $this->getOption('data-pickpoints-required');
      $data['data-promo-required'] = $this->getOption('data-promo-required');
      $data['data-promo-description'] = $this->getOption('data-promo-description');
      $data['data-message-text'] = $this->getOption('data-message-text');
      $data['data-message-imageurl'] = $this->getOption('data-message-imageurl');
      $data['data-message-url'] = $this->getOption('data-message-url');

      $userdata = array(
        "customer_id" => $customer_id,
        "session_id" => session_id(),
        "type" => $type,
      );
      $data['data-userdata'] = json_encode($userdata);

      $data['data-commissionpercent'] = $this->getOption('data-commissionpercent');
      $button_style = $this->get_button_style($type);
      $data['class'] = $button_style['class'];
      $data['style'] = $button_style['style'];

      return $data;
    }
    /**
   * Get PayQR button style.
   *
   * @return array
   */
  private function get_button_style($type){
    $type .= "_";
    $style = array();
    $style['class'][] = 'payqr-button';
    $style['class'][] = $this->getOption($type . 'button_color');
    $style['class'][] = $this->getOption($type . 'button_form');
    $style['class'][] = $this->getOption($type . 'button_gradient');
    $style['class'][] = $this->getOption($type . 'button_text_case');
    $style['class'][] = $this->getOption($type . 'button_text_width');
    $style['class'][] = $this->getOption($type . 'button_text_size');
    $style['class'][] = $this->getOption($type . 'button_shadow');
    $style['style'][] = 'height:' . $this->getOption($type . 'button_height') . ';';
    $style['style'][] = 'width:' . $this->getOption($type . 'button_width') . ';';
    return $style;
  }
  public function getOption($key)
  {
    if(empty($this->options))
    {
      $this->fillOptions();
    }
    if(isset($this->options[$key]))
    {
      return $this->options[$key];
    }
  }
  private function fillOptions()
  {
    $query = tep_db_query("select * from payqr_settings");
    while($item = tep_db_fetch_array($query))
    {
      $this->options[$item["key"]] = $item["value"];
    }
  }
  private function getImageUrl($name)
  {
    $url = "http://" . $_SERVER["HTTP_HOST"] . "/" . DIR_WS_IMAGES . $name;
    return $url;
  }

  public function showCartButton($cart)
  {
    $html = "";
    if($this->getOption("button-show-on-cart") == 1)
    {
      $products = array();
      foreach ($cart->get_products() as $item)
      {
        //если есть атрибуты у товара то передаём article как json строку с атрибутами
        if($item["attributes"])
        {
          $article = json_encode(array(
            "id" => $item["id"],
            "attributes" => $item["attributes"],
          ));
        }
        else
        {
          $article = $item["id"];
        }
        $products[] = array(
              "article" => $article,
              "name" => payqr_json_validator::escape_quotes($item["name"]),
              "imageUrl" => $this->getImageUrl($item["image"]),
              "amount"=> $item["final_price"] * $item["quantity"],
              "quantity" => $item["quantity"],
          );
      }
      $html = $this->get_button_html($this->scenario, $products, $this->type_cart);
    }
    return $html;
  }

  public function showProductButton($id = 0)
  {
    $html = "";
    $id = isset($_GET["products_id"]) ? (int)$_GET["products_id"] : $id;
    if($this->getOption("button-show-on-product") == 1 && $id > 0)
    {
      $product_info_query = tep_db_query("select * from " . TABLE_PRODUCTS . " p JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd on pd.products_id = p.products_id where p.products_status = '1' and p.products_id = $id");
      if($item = tep_db_fetch_array($product_info_query))
      {
        $price = $item['products_price'];
        if ($new_price = tep_get_products_special_price($item['products_id']))
        {
          $price = $new_price;
        }
        $products[] = array(
              "article" => $id,
              "name" => payqr_json_validator::escape_quotes($item["products_name"]),
              "imageUrl" => $this->getImageUrl($item["products_image"]),
              "amount"=> $price,
              "quantity" => 1,
        );
        $html = $this->get_button_html($this->scenario, $products, $this->type_product);
      }
    }
    return $html;
  }



  public function cancelOrder($id)
  {
    $status = $this->getOption("order_status_cancelled");
    $query = "update ".TABLE_ORDERS." set orders_status=$status where orders_id={$id}";
    tep_db_query($query);
  }
}
