<?php
require "inc/global.php";
require_once "model/Table.php";
require_once "model/Order.php";

$tableObj = new Table();
$orderObj = new Order();

$task = $_GET['task'] ?? '';
$tableId = $_GET['tableId'] ?? 0;
$targetTableId = $_GET['targetTableId'] ?? 0;

switch($task) {
    case 'move':
        // Masa taşıma
        if($tableId && $targetTableId) {
            // Kaynak masanın aktif siparişini bul
            $sourceOrder = $orderObj->getActiveOrderByTable($tableId);
            
            if($sourceOrder) {
                // Siparişin masa_id'sini değiştir
                $orderObj->moveOrderToTable($sourceOrder['id'], $targetTableId);
                
                // Kaynak masayı pasif yap
                $tableObj->deactive($tableId);
                
                // Hedef masayı aktif yap
                $tableObj->active($targetTableId);
                
                echo json_encode(['success' => true, 'message' => 'Masa başarıyla taşındı']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Kaynak masada aktif sipariş yok']);
            }
        }
        break;
        
    case 'merge':
        // Masa birleştirme
        if($tableId && $targetTableId) {
            $sourceOrder = $orderObj->getActiveOrderByTable($tableId);
            $targetOrder = $orderObj->getActiveOrderByTable($targetTableId);
            
            if($sourceOrder && $targetOrder) {
                // Kaynak masanın ürünlerini hedef masaya taşı
                $orderObj->mergeOrders($sourceOrder['id'], $targetOrder['id']);
                
                // Kaynak masayı kapat
                $orderObj->closeOrder($sourceOrder['id']);
                $tableObj->deactive($tableId);
                
                echo json_encode(['success' => true, 'message' => 'Masalar başarıyla birleştirildi']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Her iki masada da aktif sipariş olmalı']);
            }
        }
        break;
}
?>
