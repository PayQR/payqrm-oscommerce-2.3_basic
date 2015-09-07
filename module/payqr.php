<?php

require('includes/application_top.php');
require_once 'payqr/payqr_config.php';

$types = array(
  "log" => "payqr/log.php",
  "handler" => "payqr/payqr_receiver.php",
);
$type = isset($_GET["type"]) ? $_GET["type"] : "";
if(isset($types[$type]))
{
  $file = dirname(__FILE__) . "/" . $types[$type];
  if(file_exists($file))
  {
    require_once $file;
  }
}
