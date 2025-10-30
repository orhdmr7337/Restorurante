<?php
require "inc/global.php";
require "controller/categories.php";

require_once "model/Material.php";
$materialObj = new Material();
$lowStockCount = count($materialObj->getLowStock());

require "view/categories_layout.php";
