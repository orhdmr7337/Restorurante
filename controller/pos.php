<?php
require_once "model/Order.php";
require_once "model/Table.php";
require_once "model/Menu.php";

$orderObj = new Order();
$tableObj = new Table();
$menuObj = new Menu();

// Bugünkü siparişler
$todayOrders = $orderObj->getTodayOrders();
$todayTotal = 0;
foreach ($todayOrders as $order) {
    $todayTotal += $order['total_amount'];
}

// Aktif masalar
$activeTables = $tableObj->getActiveTables();

// Kategoriler ve ürünler
$categories = $menuObj->getAllCategories();
$menu = $menuObj->getFullMenu();
