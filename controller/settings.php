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

// Sistem ayarlarını çek
$pdo = new PDO('mysql:host=localhost;dbname=restaurant', 'root', '');
try {
    $stmt = $pdo->query("SELECT * FROM settings");
    $settings = [];
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
} catch (Exception $e) {
    $settings = [
        'app_name' => 'Restaurant ERP',
        'currency' => 'TRY',
        'tax_rate' => '18',
        'low_stock_alert' => '1',
        'email_notifications' => '1',
        'qr_order_enabled' => '0',
        'debt_alert_days' => '7'
    ];
}
