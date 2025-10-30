<?php
require "inc/global.php";
require "controller/products.php";

$materialObj = new Material();
$lowStockCount = count($materialObj->getLowStock());

require "view/products_layout.php";