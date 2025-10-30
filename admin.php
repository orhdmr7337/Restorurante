<?php
require "inc/global.php";
require_once "model/User.php";
require_once "model/Material.php";
require_once "model/Table.php";
require_once "model/Order.php";

$usrObj = new User();
$materialObj = new Material();
$tableObj = new Table();
$orderObj = new Order();

if (!isset($_SESSION['user_session'])) {
    redirect('login.php');
}

$userId = $_SESSION['user_session'];
$userInfo = $usrObj->getOneUser($userId);

if ($userInfo['user_position'] != 1) {
    redirect('index.php');
}

require "controller/admin.php";

$lowStockCount = count($materialObj->getLowStock());
$allTables = $tableObj->getAllTables();

require "view/admin_layout.php";
