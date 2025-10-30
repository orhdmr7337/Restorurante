<?php
require "inc/global.php";
require "controller/suppliers.php";

require_once "model/Material.php";
$materialObj = new Material();
$lowStockCount = count($materialObj->getLowStock());

require "view/suppliers_layout.php";
