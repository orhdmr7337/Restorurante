<?php
require "inc/global.php";

require_once "model/User.php";
require_once "model/Table.php";
require_once "model/Order.php";
require_once "model/Material.php";

$usrObj = new User();
$tableObj = new Table();
$orderObj = new Order();
$materialObj = new Material();

// Giriş kontrolü - Giriş yapmamışsa login.php'ye yönlendir
if (!isset($_SESSION['user_session'])) {
    redirect('login.php');
}

$userId = $_SESSION['user_session'];
$userInfo = $usrObj->getOneUser($userId);

require "controller/index.php";

$allTables = $tableObj->getAllTables();
$lowStockCount = count($materialObj->getLowStock());

require "view/tables_admin_layout.php";