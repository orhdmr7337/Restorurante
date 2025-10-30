<?php
header('Content-Type: application/json');
require "../inc/global.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$tableId = $data['tableId'] ?? 1;
$items = $data['items'] ?? [];
$paymentType = $data['paymentType'] ?? 'cash';
$discount = $data['discount'] ?? 0;
$customerId = $data['customerId'] ?? null;
$note = $data['note'] ?? '';

if (empty($items)) {
    echo json_encode(['success' => false, 'message' => 'Sepet boş!']);
    exit;
}

try {
    require_once "../model/Order.php";
    require_once "../model/Finance.php";
    
    $orderObj = new Order();
    $financeObj = new Finance();
    
    // Sipariş oluştur
    $orderId = $orderObj->createOrder($tableId);
    
    if (!$orderId) {
        throw new Exception('Sipariş oluşturulamadı');
    }
    
    // Ürünleri ekle
    $total = 0;
    foreach ($items as $item) {
        $orderObj->addProductToOrder($item['id'], $orderId);
        $total += $item['price'] * $item['quantity'];
        
        // Miktar 1'den fazlaysa ekstra ekle
        for ($i = 1; $i < $item['quantity']; $i++) {
            $orderObj->addProductToOrder($item['id'], $orderId);
        }
    }
    
    // KDV hesapla
    $tax = $total * 0.18;
    $finalTotal = $total + $tax - $discount;
    
    // Ödeme tipine göre işlem
    if ($paymentType === 'cash') {
        // Kasa girişi
        $financeObj->addCashTransaction('in', $finalTotal, 'POS Satış - Sipariş #' . $orderId);
    } elseif ($paymentType === 'card') {
        // Banka girişi (kart)
        $financeObj->addBankTransaction('POS Cihazı', 'in', $finalTotal, 'Kart Ödemesi - Sipariş #' . $orderId);
    } elseif ($paymentType === 'bank') {
        // Banka transferi
        $financeObj->addBankTransaction('Banka Hesabı', 'in', $finalTotal, 'Transfer - Sipariş #' . $orderId);
    } elseif ($paymentType === 'debt' && $customerId) {
        // Veresiye - cari hesaba ekle
        require_once "../model/Account.php";
        $accountObj = new Account();
        $accountObj->addTransaction($customerId, 'debit', $finalTotal, 'Veresiye Satış - Sipariş #' . $orderId);
    }
    
    // Gelir kaydet
    $financeObj->addIncome([
        'category' => 'Satış',
        'amount' => $finalTotal,
        'description' => 'POS Satış - Sipariş #' . $orderId . ($note ? ' - ' . $note : ''),
        'income_date' => date('Y-m-d'),
        'payment_method' => $paymentType,
        'reference_type' => 'order',
        'reference_id' => $orderId,
        'user_id' => $_SESSION['user_session'] ?? null
    ]);
    
    // Siparişi kapat
    $orderObj->closeOrder($orderId);
    
    echo json_encode([
        'success' => true,
        'message' => 'Sipariş kaydedildi',
        'orderId' => $orderId,
        'total' => $finalTotal
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Hata: ' . $e->getMessage()
    ]);
}
?>
