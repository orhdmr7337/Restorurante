<?php
require_once "model/Menu.php";
require_once "model/User.php";

$menuObj = new Menu();
$usrObj = new User();

if($usrObj->isLoggedIn() == "") {
    redirect('login.php');
}

$userId = $_SESSION['user_session'];
$userInfo = $usrObj->getOneUser($userId);

// Sadece admin ve yetkili erişebilir
if ($userInfo['user_position'] != 1 && $userInfo['user_position'] != 2) {
    redirect('index.php');
}

$products = $menuObj->getAllProducts();
$categories = $menuObj->getAllCategories();
