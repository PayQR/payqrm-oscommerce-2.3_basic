<?php

$order = new payqr_order($Payqr);
$order->updateDeliveryInfo();
$delivery_cases = $order->getDeliveryCases();

$Payqr->objectOrder->setDeliveryCases($delivery_cases);
