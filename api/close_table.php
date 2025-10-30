<?php
// Hata raporlamayı kapat (JSON için)
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');

try {
    require "../inc/global.php";
    require_once "../model/Order.php";
    require_once "../model/Table.php";
    require_once "../model/Finance.php";

    $data = json_decode(file_get_contents('php://input'), true);

    $tableId = $data['tableId'] ?? 0;
    $paymentType = $data['paymentType'] ?? 'cash';
    $amount = $data['amount'] ?? 0;

    if (!$tableId || !$amount) {
        echo json_encode(['success' => false, 'message' => 'Eksik bilgi: tableId veya amount']);
        exit;
    }
    
    $orderObj = new Order();
    $tableObj = new Table();
    $financeObj = new Finance();
    
    // Masanın aktif siparişini bul
    $activeOrder = $orderObj->getActiveOrderByTable($tableId);
    
    if (!$activeOrder) {
        throw new Exception('Aktif sipariş bulunamadı');
    }
    
    // Ödeme tipine göre kasa/banka işlemi
    try {
        if ($paymentType === 'cash') {
            $cashResult = $financeObj->addCashTransaction('in', $amount, 'Masa ' . $tableId . ' ödemesi', 'order', $activeOrder['id'], $_SESSION['user_session'] ?? null);
            if (!$cashResult) {
                throw new Exception('Kasa işlemi kaydedilemedi');
            }
        } elseif ($paymentType === 'card') {
            $bankResult = $financeObj->addBankTransaction('POS Cihazı', 'in', $amount, 'Kart Ödemesi - Masa ' . $tableId, 'POS-' . time());
            if (!$bankResult) {
                throw new Exception('Banka işlemi kaydedilemedi');
            }
        } elseif ($paymentType === 'bank') {
            $bankResult = $financeObj->addBankTransaction('Banka Hesabı', 'in', $amount, 'Transfer - Masa ' . $tableId, 'TRF-' . time());
            if (!$bankResult) {
                throw new Exception('Banka işlemi kaydedilemedi');
            }
        }
    } catch (Exception $e) {
        throw new Exception('Ödeme kaydı hatası: ' . $e->getMessage());
    }
    
    // Gelir kaydet
    try {
        $incomeResult = $financeObj->addIncome([
            'category' => 'Satış',
            'amount' => $amount,
            'description' => 'Masa ' . $tableId . ' - Sipariş #' . $activeOrder['id'],
            'income_date' => date('Y-m-d'),
            'payment_method' => $paymentType,
            'reference_type' => 'order',
            'reference_id' => $activeOrder['id'],
            'user_id' => $_SESSION['user_session'] ?? null
        ]);
        
        if (!$incomeResult) {
            throw new Exception('Gelir kaydı oluşturulamadı');
        }
    } catch (Exception $e) {
        throw new Exception('Gelir kaydı hatası: ' . $e->getMessage());
    }
    
    // Siparişi kapat
    $orderObj->closeOrder($activeOrder['id']);
    
    // Masayı pasif yap
    $tableObj->deactive($tableId);
    
    echo json_encode([
        'success' => true,
        'message' => 'Ödeme alındı ve kayıtlar güncellendi',
        'orderId' => $activeOrder['id'],
        'amount' => $amount,
        'paymentType' => $paymentType
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Sunucu hatası: ' . $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'debug' => [
            'tableId' => $tableId ?? null,
            'amount' => $amount ?? null,
            'paymentType' => $paymentType ?? null
        ]
    ]);
}
?>
