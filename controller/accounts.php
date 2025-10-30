<?php
require_once "model/Account.php";
require_once "model/User.php";

$accountObj = new Account();
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

$accounts = $accountObj->getAllAccounts();
$totalDebt = $accountObj->getTotalDebt();
$totalCredit = $accountObj->getTotalCredit();
