<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$helper = new payqr_helper();
if(isset($_GET["key"]) && $_GET["key"] == $helper->getOption("log_key"))
{
    $file = realpath(dirname(__FILE__) . "/../") . "/" . $helper->getOption("log_path");
    if(file_exists($file))
    {
      $log = file_get_contents($file, "");
      echo nl2br($log);
    }
}
