<?php
require_once "inc/global.php";

$tblObj = new Table();
$usrObj = new User();
$menuObj = new Menu();
$orderObj = new Order();

if($usrObj->isLoggedIn() == "")
{
    $usrObj->redirect('login.php');
}

$tableId = $_GET['id'];
$table = $tblObj->getTable($tableId);
$menu = $menuObj->getFullMenu();
$orders = $orderObj->getOrdersByTable($tableId);
