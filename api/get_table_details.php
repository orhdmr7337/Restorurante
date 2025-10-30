<?php
// Hata raporlamayı kapat (JSON için)
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');

try {
    require "../inc/global.php";
    require_once "../model/Order.php";
    require_once "../model/Table.php";
    
    $tableId = $_GET['tableId'] ?? 0;

    if (!$tableId) {
        echo json_encode(['success' => false, 'message' => 'Masa ID gerekli']);
        exit;
    }
    
    $orderObj = new Order();
    $tableObj = new Table();
    
    // Masa bilgisini al
    $table = $tableObj->getTable($tableId);
    
    if (!$table) {
        echo json_encode(['success' => false, 'message' => 'Masa bulunamadı']);
        exit;
    }
    
    // Siparişleri al
    $items = [];
    try {
        $items = $orderObj->getTableOrderedItems($tableId);
    } catch (Exception $e) {
        // Sipariş yoksa boş array
        $items = [];
    }
    
    echo json_encode([
        'success' => true,
        'table' => $table,
        'items' => $items
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Sunucu hatası: ' . $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}
?>
