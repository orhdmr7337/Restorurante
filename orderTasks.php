<?php
/**
 * Secure Order Tasks Handler
 * Restaurant ERP System v2.0
 * Enhanced Security Implementation
 */

require_once "inc/global.php";

// Güvenlik kontrolleri
Security::checkRateLimit('order_operations', 25, 300);

// Authentication check
if (!isset($_SESSION['user_session'])) {
    Security::logSecurityEvent('UNAUTHORIZED_ORDER_ACCESS');
    apiResponse([], false, 'Oturum bulunamadı.', 401);
}

// Get user info for logging
$userObj = new User();
$orderObj = new Order();
$userId = $_SESSION['user_session'];
$userInfo = $userObj->getOneUser($userId);

$task = Security::sanitizeInput($_GET['task'] ?? '');

// CSRF validation for state-changing operations
$csrf_protected_tasks = ['add', 'delete', 'cancel', 'complete', 'move', 'finish'];
if (in_array($task, $csrf_protected_tasks) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Security::validateCSRFToken($_POST['csrf_token'] ?? '')) {
        Security::logSecurityEvent('ORDER_CSRF_VIOLATION', [
            'task' => $task,
            'user_id' => $userId
        ]);
        apiResponse([], false, 'Güvenlik doğrulaması başarısız.', 403);
    }
}

try {
    switch ($task) {
        case "add":
            handleAddProductToOrder();
            break;

        case "delete":
            handleDeleteProductFromOrder();
            break;

        case "cancel":
            handleCancelOrder();
            break;

        case "complete":
            handleCompleteOrder();
            break;

        case "move":
            handleMoveOrder();
            break;

        case "finish":
            handleFinishOrder();
            break;

        case "updateQuantity":
            handleUpdateQuantity();
            break;

        case "addNote":
            handleAddNote();
            break;

        case "getOrderSummary":
            handleGetOrderSummary();
            break;

        case "splitOrder":
            handleSplitOrder();
            break;

        default:
            Security::logSecurityEvent('INVALID_ORDER_TASK', [
                'task' => $task,
                'user_id' => $userId
            ]);
            apiResponse([], false, 'Geçersiz işlem.', 400);
    }

} catch (Exception $e) {
    Security::logSecurityEvent('ORDER_TASK_EXCEPTION', [
        'task' => $task,
        'message' => $e->getMessage(),
        'user_id' => $userId
    ]);

    if (DEBUG_MODE) {
        apiResponse([], false, 'Hata: ' . $e->getMessage(), 500);
    } else {
        apiResponse([], false, 'İşlem sırasında bir hata oluştu.', 500);
    }
}

/**
 * Handle add product to order
 */
function handleAddProductToOrder() {
    global $orderObj, $userId;

    $productId = (int)($_GET['productId'] ?? 0);
    $tableId = (int)($_GET['tableId'] ?? 0);
    $quantity = (int)($_GET['quantity'] ?? 1);

    // Validation
    if ($productId <= 0) {
        redirectWithError('table.php?id=' . $tableId, 'Geçersiz ürün ID.');
    }

    if ($tableId <= 0) {
        redirectWithError('index.php', 'Geçersiz masa ID.');
    }

    if ($quantity <= 0 || $quantity > 50) {
        redirectWithError('table.php?id=' . $tableId, 'Geçersiz miktar. (1-50 arası olmalı)');
    }

    // Check if table exists and is available
    $tableObj = new Table();
    $table = $tableObj->getTable($tableId);
    if (!$table) {
        redirectWithError('index.php', 'Masa bulunamadı.');
    }

    // Check if product exists and is active
    $menuObj = new Menu();
    $product = $menuObj->getProduct($productId);
    if (!$product || $product['status'] != 1) {
        redirectWithError('table.php?id=' . $tableId, 'Ürün bulunamadı veya aktif değil.');
    }

    // Add product to order
    $result = $orderObj->addProductToTableOrder($productId, $tableId, $quantity);

    if ($result) {
        logActivity('PRODUCT_ADDED_TO_ORDER', [
            'product_id' => $productId,
            'product_name' => $product['name'],
            'table_id' => $tableId,
            'quantity' => $quantity,
            'user_id' => $userId
        ]);

        redirectWithSuccess('table.php?id=' . $tableId, 'Ürün siparişe eklendi.');
    } else {
        redirectWithError('table.php?id=' . $tableId, 'Ürün eklenirken hata oluştu.');
    }
}

/**
 * Handle delete product from order
 */
function handleDeleteProductFromOrder() {
    global $orderObj, $userId;

    $orderId = (int)($_GET['orderId'] ?? $_GET['orderProductId'] ?? 0);
    $tableId = (int)($_GET['tableId'] ?? 0);

    if ($orderId <= 0) {
        redirectWithError('table.php?id=' . $tableId, 'Geçersiz sipariş ID.');
    }

    if ($tableId <= 0) {
        redirectWithError('index.php', 'Geçersiz masa ID.');
    }

    // Check if order item exists
    $orderItem = $orderObj->getOrderItem($orderId);
    if (!$orderItem) {
        redirectWithError('table.php?id=' . $tableId, 'Sipariş kalemi bulunamadı.');
    }

    // Check permission - only same user or admin can delete
    if ($orderItem['user_id'] != $userId && !hasPermission(1)) {
        Security::logSecurityEvent('UNAUTHORIZED_ORDER_DELETE', [
            'order_id' => $orderId,
            'order_user_id' => $orderItem['user_id'],
            'current_user_id' => $userId
        ]);
        redirectWithError('table.php?id=' . $tableId, 'Bu siparişi silme yetkiniz bulunmuyor.');
    }

    // Delete order item
    $result = $orderObj->deleteProductFromOrder($orderId);

    if ($result) {
        logActivity('PRODUCT_REMOVED_FROM_ORDER', [
            'order_id' => $orderId,
            'table_id' => $tableId,
            'user_id' => $userId
        ]);

        redirectWithSuccess('table.php?id=' . $tableId, 'Ürün siparişten silindi.');
    } else {
        redirectWithError('table.php?id=' . $tableId, 'Ürün silinirken hata oluştu.');
    }
}

/**
 * Handle cancel order
 */
function handleCancelOrder() {
    global $orderObj, $userId;

    $tableId = (int)($_GET['tableId'] ?? 0);
    $reason = Security::sanitizeInput($_GET['reason'] ?? 'Belirtilmedi');
    $note = Security::sanitizeInput($_GET['note'] ?? '');

    if ($tableId <= 0) {
        redirectWithError('index.php', 'Geçersiz masa ID.');
    }

    // Permission check - only admin and manager can cancel orders
    if (!hasPermission(2)) {
        Security::logSecurityEvent('UNAUTHORIZED_ORDER_CANCEL', [
            'table_id' => $tableId,
            'user_id' => $userId
        ]);
        redirectWithError('table.php?id=' . $tableId, 'Sipariş iptal etme yetkiniz bulunmuyor.');
    }

    // Check if table has active orders
    $tableOrders = $orderObj->getTableOrders($tableId);
    if (empty($tableOrders)) {
        redirectWithError('table.php?id=' . $tableId, 'Bu masada iptal edilecek sipariş bulunmuyor.');
    }

    // Cancel order
    $result = $orderObj->cancelTableOrder($tableId, $reason, $note);

    if ($result) {
        logActivity('ORDER_CANCELLED', [
            'table_id' => $tableId,
            'reason' => $reason,
            'note' => $note,
            'cancelled_by' => $userId
        ]);

        Security::logSecurityEvent('ORDER_CANCELLED', [
            'table_id' => $tableId,
            'reason' => $reason,
            'user_id' => $userId
        ]);

        redirectWithSuccess('index.php', 'Sipariş iptal edildi.');
    } else {
        redirectWithError('table.php?id=' . $tableId, 'Sipariş iptal edilirken hata oluştu.');
    }
}

/**
 * Handle complete order
 */
function handleCompleteOrder() {
    global $orderObj, $userId, $userInfo;

    $tableId = (int)($_GET['tableId'] ?? 0);

    if ($tableId <= 0) {
        redirectWithError('index.php', 'Geçersiz masa ID.');
    }

    // Check if table has orders to complete
    $tableOrders = $orderObj->getTableOrders($tableId);
    if (empty($tableOrders)) {
        redirectWithError('table.php?id=' . $tableId, 'Bu masada tamamlanacak sipariş bulunmuyor.');
    }

    // Calculate total
    $total = array_sum(array_column($tableOrders, 'total_price'));

    // Complete order
    $result = $orderObj->closeTableOrder($tableId, $userId);

    if ($result) {
        logActivity('ORDER_COMPLETED', [
            'table_id' => $tableId,
            'total_amount' => $total,
            'completed_by' => $userId,
            'staff_name' => $userInfo['fullname']
        ]);

        // Add to daily sales
        $financeObj = new Finance();
        $financeObj->addIncome([
            'category' => 'Satış',
            'amount' => $total,
            'description' => 'Masa ' . $tableId . ' siparişi',
            'income_date' => date('Y-m-d'),
            'payment_method' => 'cash',
            'reference_type' => 'order',
            'reference_id' => $tableId,
            'user_id' => $userId
        ]);

        redirectWithSuccess('index.php', 'Sipariş tamamlandı. Toplam: ' . formatCurrency($total));
    } else {
        redirectWithError('table.php?id=' . $tableId, 'Sipariş tamamlanırken hata oluştu.');
    }
}

/**
 * Handle move order
 */
function handleMoveOrder() {
    global $orderObj, $userId;

    $fromTableId = (int)($_GET['fromTableId'] ?? 0);
    $toTableId = (int)($_GET['tableId'] ?? 0);

    if ($fromTableId <= 0 || $toTableId <= 0) {
        redirectWithError('index.php', 'Geçersiz masa ID\'leri.');
    }

    if ($fromTableId === $toTableId) {
        redirectWithError('table.php?id=' . $fromTableId, 'Aynı masaya taşıma yapılamaz.');
    }

    // Permission check - only admin and manager can move orders
    if (!hasPermission(2)) {
        Security::logSecurityEvent('UNAUTHORIZED_ORDER_MOVE', [
            'from_table_id' => $fromTableId,
            'to_table_id' => $toTableId,
            'user_id' => $userId
        ]);
        redirectWithError('table.php?id=' . $fromTableId, 'Sipariş taşıma yetkiniz bulunmuyor.');
    }

    // Check if source table has orders
    $sourceOrders = $orderObj->getTableOrders($fromTableId);
    if (empty($sourceOrders)) {
        redirectWithError('table.php?id=' . $fromTableId, 'Bu masada taşınacak sipariş bulunmuyor.');
    }

    // Check if destination table is available
    $tableObj = new Table();
    $destTable = $tableObj->getTable($toTableId);
    if (!$destTable) {
        redirectWithError('table.php?id=' . $fromTableId, 'Hedef masa bulunamadı.');
    }

    $destOrders = $orderObj->getTableOrders($toTableId);
    if (!empty($destOrders)) {
        redirectWithError('table.php?id=' . $fromTableId, 'Hedef masada zaten sipariş bulunuyor.');
    }

    // Move order
    $result = $orderObj->moveTableOrder($fromTableId, $toTableId);

    if ($result) {
        logActivity('ORDER_MOVED', [
            'from_table_id' => $fromTableId,
            'to_table_id' => $toTableId,
            'moved_by' => $userId
        ]);

        redirectWithSuccess('table.php?id=' . $toTableId, 'Sipariş Masa ' . $toTableId . '\'ye taşındı.');
    } else {
        redirectWithError('table.php?id=' . $fromTableId, 'Sipariş taşınırken hata oluştu.');
    }
}

/**
 * Handle finish order (alias for complete)
 */
function handleFinishOrder() {
    handleCompleteOrder();
}

/**
 * Handle update quantity
 */
function handleUpdateQuantity() {
    global $orderObj, $userId;

    $orderId = (int)($_POST['order_id'] ?? 0);
    $quantity = (int)($_POST['quantity'] ?? 1);
    $tableId = (int)($_POST['table_id'] ?? 0);

    if ($orderId <= 0) {
        apiResponse([], false, 'Geçersiz sipariş ID.', 400);
    }

    if ($quantity <= 0 || $quantity > 50) {
        apiResponse([], false, 'Geçersiz miktar. (1-50 arası olmalı)', 400);
    }

    // Check if order item exists
    $orderItem = $orderObj->getOrderItem($orderId);
    if (!$orderItem) {
        apiResponse([], false, 'Sipariş kalemi bulunamadı.', 404);
    }

    // Update quantity
    $result = $orderObj->updateOrderQuantity($orderId, $quantity);

    if ($result) {
        logActivity('ORDER_QUANTITY_UPDATED', [
            'order_id' => $orderId,
            'old_quantity' => $orderItem['quantity'],
            'new_quantity' => $quantity,
            'table_id' => $tableId,
            'user_id' => $userId
        ]);

        apiResponse([], true, 'Miktar güncellendi.');
    } else {
        apiResponse([], false, 'Miktar güncellenirken hata oluştu.', 500);
    }
}

/**
 * Handle add note to order
 */
function handleAddNote() {
    global $orderObj, $userId;

    $orderId = (int)($_POST['order_id'] ?? 0);
    $note = Security::sanitizeInput($_POST['note'] ?? '');

    if ($orderId <= 0) {
        apiResponse([], false, 'Geçersiz sipariş ID.', 400);
    }

    if (strlen($note) > 500) {
        apiResponse([], false, 'Not çok uzun. (Max 500 karakter)', 400);
    }

    // Check if order item exists
    $orderItem = $orderObj->getOrderItem($orderId);
    if (!$orderItem) {
        apiResponse([], false, 'Sipariş kalemi bulunamadı.', 404);
    }

    // Add note
    $result = $orderObj->addOrderNote($orderId, $note);

    if ($result) {
        logActivity('ORDER_NOTE_ADDED', [
            'order_id' => $orderId,
            'note' => $note,
            'user_id' => $userId
        ]);

        apiResponse([], true, 'Not eklendi.');
    } else {
        apiResponse([], false, 'Not eklenirken hata oluştu.', 500);
    }
}

/**
 * Handle get order summary
 */
function handleGetOrderSummary() {
    global $orderObj;

    $tableId = (int)($_GET['table_id'] ?? 0);

    if ($tableId <= 0) {
        apiResponse([], false, 'Geçersiz masa ID.', 400);
    }

    $orders = $orderObj->getTableOrders($tableId);
    $summary = $orderObj->getOrderSummary($tableId);

    apiResponse([
        'orders' => $orders,
        'summary' => $summary,
        'table_id' => $tableId
    ], true, 'Sipariş özeti getirildi.');
}

/**
 * Handle split order
 */
function handleSplitOrder() {
    global $orderObj, $userId;

    if (!hasPermission(2)) {
        apiResponse([], false, 'Bu işlem için yetkiniz bulunmuyor.', 403);
    }

    $fromTableId = (int)($_POST['from_table_id'] ?? 0);
    $toTableId = (int)($_POST['to_table_id'] ?? 0);
    $orderIds = $_POST['order_ids'] ?? [];

    if ($fromTableId <= 0 || $toTableId <= 0) {
        apiResponse([], false, 'Geçersiz masa ID\'leri.', 400);
    }

    if (empty($orderIds) || !is_array($orderIds)) {
        apiResponse([], false, 'Taşınacak siparişler seçilmelidir.', 400);
    }

    // Validate order IDs
    $orderIds = array_map('intval', $orderIds);
    $orderIds = array_filter($orderIds, function($id) { return $id > 0; });

    if (empty($orderIds)) {
        apiResponse([], false, 'Geçerli sipariş ID\'leri bulunamadı.', 400);
    }

    // Split order
    $result = $orderObj->splitOrder($fromTableId, $toTableId, $orderIds);

    if ($result) {
        logActivity('ORDER_SPLIT', [
            'from_table_id' => $fromTableId,
            'to_table_id' => $toTableId,
            'order_ids' => $orderIds,
            'split_by' => $userId
        ]);

        apiResponse([], true, 'Sipariş bölündü.');
    } else {
        apiResponse([], false, 'Sipariş bölünürken hata oluştu.', 500);
    }
}

/**
 * Helper functions for redirects
 */
function redirectWithSuccess($url, $message) {
    FlashMessage::success($message);
    redirect($url);
}

function redirectWithError($url, $message) {
    FlashMessage::error($message);
    redirect($url);
}
