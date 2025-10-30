<?php
require_once "model/User.php";

$usrObj = new User();

if($usrObj->isLoggedIn() == "") {
    redirect('login.php');
}

$userId = $_SESSION['user_session'];
$userInfo = $usrObj->getOneUser($userId);

// Sadece admin erişebilir
if ($userInfo['user_position'] != 1) {
    redirect('index.php');
}

$users = $usrObj->getAllUsers();
