<?php
require "inc/global.php";
require "controller/purchases.php";

require_once "model/Material.php";
$materialObj = new Material();
$lowStockCount = count($materialObj->getLowStock());

require "view/purchases_layout.php";
