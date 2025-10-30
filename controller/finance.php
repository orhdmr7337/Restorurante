<?php
require_once "model/Finance.php";
require_once "model/User.php";

$financeObj = new Finance();
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

$dailyReport = $financeObj->getDailyReport();
$monthlyReport = $financeObj->getMonthlyReport();
$cashBalance = $financeObj->getCashBalance();
$bankBalance = $financeObj->getBankBalance();
