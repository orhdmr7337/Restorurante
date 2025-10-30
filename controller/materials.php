<?php
require_once "model/Material.php";
require_once "model/User.php";

$materialObj = new Material();
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

$materials = $materialObj->getAllMaterials();
$lowStock = $materialObj->getLowStock();
