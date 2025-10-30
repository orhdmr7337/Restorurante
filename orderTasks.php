<?php
require_once "inc/global.php";

$orderCont = new Order();
$usrObj = new User();
//İşlemi en son kimin yaptığını kayıt etmek için session ile id alıyoruz
$userId = $_SESSION['user_session'];
$userInfo = $usrObj->getOneUser($userId);

switch($_GET['task']){
    case "add":
        // masa siparişine ürün ekleme
        $orderCont->addProductToTableOrder($_GET['productId'], $_GET['tableId']);
        break;
    case "delete":
        // siparişten ürün silme
        $orderId = $_GET['orderId'] ?? $_GET['orderProductId'] ?? null;
        if ($orderId) {
            $orderCont->deleteProductFromOrder($orderId);
        }
        break;
    case "cancel":
        $reason = $_GET['reason'] ?? 'Belirtilmedi';
        $note = $_GET['note'] ?? '';
        // İptal nedenini kaydet (gelecekte log tablosuna eklenebilir)
        $orderCont->cancelTableOrder($_GET["tableId"]);
        break;
    case "complete":
        // Sipariş tamamlama
        $orderCont->closeTableOrder($_GET['tableId'], $userInfo["id"]);
        break;
    case "move":
        $orderCont->moveTableOrder($_GET['fromTableId'], $_GET['tableId']);
        break;
    case "finish":
        $orderCont->closeTableOrder($_GET['tableId'], $userInfo["id"]);
        break;
}

// Başarı mesajı ile yönlendir
$successMessages = [
    'add' => 'Ürün eklendi',
    'delete' => 'Ürün silindi',
    'cancel' => 'Sipariş iptal edildi',
    'complete' => 'Sipariş tamamlandı',
    'finish' => 'Sipariş tamamlandı'
];

$task = $_GET['task'];
$message = $successMessages[$task] ?? 'İşlem başarılı';

header("Location: table.php?id=".$_GET['tableId']."&success=".urlencode($message));
