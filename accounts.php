<?php
require "inc/global.php";
require "controller/accounts.php";

require_once "model/Material.php";
$materialObj = new Material();
$lowStockCount = count($materialObj->getLowStock());

require "view/accounts_layout.php";
