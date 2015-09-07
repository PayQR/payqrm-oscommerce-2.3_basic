<?php

$order = new payqr_order($Payqr);

$cartObject = $order->getCart();
$Payqr->objectOrder->setCart($cartObject);

$amount = $order->getAmount($cartObject);
$Payqr->objectOrder->setAmount($amount);

$orderId = $order->create();
$Payqr->objectOrder->setOrderId($orderId);
