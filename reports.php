<?php
require "inc/global.php";
require "controller/reports.php";

require_once "model/Material.php";
$materialObj = new Material();
$lowStockCount = count($materialObj->getLowStock());

require "view/reports_layout.php";
