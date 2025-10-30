<?php
require "inc/global.php";
require "controller/userList.php";

require_once "model/Material.php";
$materialObj = new Material();
$lowStockCount = count($materialObj->getLowStock());

require "view/userList_layout.php";
