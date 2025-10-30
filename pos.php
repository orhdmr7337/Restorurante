<?php
require "inc/global.php";

// Giriş kontrolü
if (!isset($_SESSION['user_session'])) {
    redirect('login.php');
}

// Kasiyer veya admin erişebilir
$userId = $_SESSION['user_session'];
require_once "model/User.php";
require_once "model/Table.php";
require_once "model/Order.php";

$usrObj = new User();
$tableObj = new Table();
$orderObj = new Order();

$userInfo = $usrObj->getOneUser($userId);

if ($userInfo['user_position'] != 1 && $userInfo['user_position'] != 2) {
    redirect('index.php');
}

// Tüm masaları getir
$allTables = $tableObj->getAllTables();

require_once "model/Material.php";
$materialObj = new Material();
$lowStockCount = count($materialObj->getLowStock());

require "view/pos_layout.php";
