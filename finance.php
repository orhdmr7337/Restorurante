<?php
require "inc/global.php";
require "controller/finance.php";

require_once "model/Material.php";
$materialObj = new Material();
$lowStockCount = count($materialObj->getLowStock());

require "view/finance_layout.php";
