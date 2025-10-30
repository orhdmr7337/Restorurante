<?php
/**
 * Secure Table Tasks Handler
 * Restaurant ERP System v2.0
 * Enhanced Security Implementation
 */

require_once "inc/global.php";

// Güvenlik kontrolleri
Security::checkRateLimit('table_operations', 20, 300);

// Authentication check
if (!isset($_SESSION['user_session'])) {
    Security::logSecurityEvent('UNAUTHORIZED_TABLE_ACCESS');
    apiResponse([], false, 'Oturum bulunamadı.', 401);
}

$tableObj = new Table();
$orderObj = new Order();
$userId = $_SESSION['user_session'];
$task = Security::sanitizeInput($_GET['task'] ?? '');

// CSRF validation for state-changing operations
$csrf_protected_tasks = ['open', 'close', 'reserve', 'clean', 'merge', 'split'];
if (in_array($task, $csrf_protected_tasks) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Security::validateCSRFToken($_POST['csrf_token'] ?? '')) {
        Security::logSecurityEvent('TABLE_CSRF_VIOLATION', [
            'task' => $task,
            'user_id' => $userId
        ]);
        apiResponse([], false, 'Güvenlik doğrulaması başarısız.', 403);
    }
}

try {
    switch ($task) {
        case "open":
            handleOpenTable();
            break;

        case "close":
            handleCloseTable();
            break;

        case "clean":
            handleCleanTable();
            break;

        case "reserve":
            handleReserveTable();
            break;

        case "changeStatus":
            handleChangeTableStatus();
            break;

        case "moveOrder":
            handleMoveOrder();
            break;

        case "mergeTables":
            handleMergeTables();
            break;

        case "splitTable":
            handleSplitTable();
            break;

        case "getTableInfo":
            handleGetTableInfo();
            break;

        case "updateTableCapacity":
            handleUpdateTableCapacity();
            break;

        case "getTableHistory":
            handleGetTableHistory();
            break;

        default:
            Security::logSecurityEvent('INVALID_TABLE_TASK', [
                'task' => $task,
                'user_id' => $userId
            ]);
            apiResponse([], false, 'Geçersiz işlem.', 400);
    }

} catch (Exception $e) {
    Security::logSecurityEvent('TABLE_TASK_EXCEPTION', [
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
 * Handle open table
 */
function handleOpenTable() {
    global $tableObj, $userId;

    $tableId = (int)($_GET['tableId'] ?? 0);

    if ($tableId <= 0) {
        redirectWithError('index.php', 'Geçersiz masa ID.');
    }

    // Check if table exists
    $table = $tableObj->getTable($tableId);
    if (!$table) {
        redirectWithError('index.php', 'Masa bulunamadı.');
    }

    // Check if table is available
    if ($table['status'] != 0) { // 0 = empty/available
        redirectWithError('index.php', 'Masa müsait değil.');
    }

    // Open table (set status to occupied)
    $result = $tableObj->updateTableStatus($tableId, 1); // 1 = occupied

    if ($result) {
        logActivity('TABLE_OPENED', [
            'table_id' => $tableId,
            'opened_by' => $userId
        ]);

        redirectWithSuccess('table.php?id=' . $tableId, 'Masa açıldı.');
    } else {
        redirectWithError('index.php', 'Masa açılırken hata oluştu.');
    }
}

/**
 * Handle close table
 */
function handleCloseTable() {
    global $tableObj, $orderObj, $userId;

    $tableId = (int)($_GET['tableId'] ?? 0);

    if ($tableId <= 0) {
        redirectWithError('index.php', 'Geçersiz masa ID.');
    }

    // Check if table exists
    $table = $tableObj->getTable($tableId);
    if (!$table) {
        redirectWithError('index.php', 'Masa bulunamadı.');
    }

    // Check if table has active orders
    $activeOrders = $orderObj->getTableOrders($tableId);
    if (!empty($activeOrders)) {
        redirectWithError('table.php?id=' . $tableId, 'Masada aktif sipariş bulunuyor. Önce siparişleri tamamlayın.');
    }

    // Close table (set status to dirty)
    $result = $tableObj->updateTableStatus($tableId, 2); // 2 = dirty/needs cleaning

    if ($result) {
        logActivity('TABLE_CLOSED', [
            'table_id' => $tableId,
            'closed_by' => $userId
        ]);

        redirectWithSuccess('index.php', 'Masa kapatıldı. Temizlenmesi gerekiyor.');
    } else {
        redirectWithError('table.php?id=' . $tableId, 'Masa kapatılırken hata oluştu.');
    }
}

/**
 * Handle clean table
 */
function handleCleanTable() {
    global $tableObj, $userId;

    $tableId = (int)($_GET['tableId'] ?? 0);

    if ($tableId <= 0) {
        redirectWithError('index.php', 'Geçersiz masa ID.');
    }

    // Check if table exists
    $table = $tableObj->getTable($tableId);
    if (!$table) {
        redirectWithError('index.php', 'Masa bulunamadı.');
    }

    // Clean table (set status to available)
    $result = $tableObj->updateTableStatus($tableId, 0); // 0 = available/clean

    if ($result) {
        logActivity('TABLE_CLEANED', [
            'table_id' => $tableId,
            'cleaned_by' => $userId
        ]);

        redirectWithSuccess('index.php', 'Masa temizlendi ve müsait duruma getirildi.');
    } else {
        redirectWithError('index.php', 'Masa temizlenirken hata oluştu.');
    }
}

/**
 * Handle reserve table
 */
function handleReserveTable() {
    global $tableObj, $userId;

    $tableId = (int)($_POST['tableId'] ?? 0);
    $customerName = Security::sanitizeInput($_POST['customerName'] ?? '');
    $customerPhone = Security::sanitizeInput($_POST['customerPhone'] ?? '');
    $reservationTime = Security::sanitizeInput($_POST['reservationTime'] ?? '');
    $partySize = (int)($_POST['partySize'] ?? 1);
    $notes = Security::sanitizeInput($_POST['notes'] ?? '');

    // Validation
    if ($tableId <= 0) {
        apiResponse([], false, 'Geçersiz masa ID.', 400);
    }

    if (empty($customerName)) {
        apiResponse([], false, 'Müşteri adı gereklidir.', 400);
    }

    if (strlen($customerName) < 2 || strlen($customerName) > 100) {
        apiResponse([], false, 'Müşteri adı 2-100 karakter arasında olmalıdır.', 400);
    }

    if (!empty($customerPhone) && !Security::validatePhone($customerPhone)) {
        apiResponse([], false, 'Geçerli bir telefon numarası giriniz.', 400);
    }

    if ($partySize < 1 || $partySize > 20) {
        apiResponse([], false, 'Kişi sayısı 1-20 arasında olmalıdır.', 400);
    }

    // Validate reservation time
    if (!empty($reservationTime)) {
        $reservationDateTime = DateTime::createFromFormat('Y-m-d H:i', $reservationTime);
        if (!$reservationDateTime || $reservationDateTime < new DateTime()) {
            apiResponse([], false, 'Geçerli bir rezervasyon tarihi giriniz.', 400);
        }
    }

    // Check if table exists
    $table = $tableObj->getTable($tableId);
    if (!$table) {
        apiResponse([], false, 'Masa bulunamadı.', 404);
    }

    // Check table capacity
    if ($table['capacity'] < $partySize) {
        apiResponse([], false, 'Masa kapasitesi yetersiz.', 400);
    }

    // Make reservation
    $result = $tableObj->makeReservation($tableId, $customerName, $customerPhone, $reservationTime, $partySize, $notes);

    if ($result) {
        logActivity('TABLE_RESERVED', [
            'table_id' => $tableId,
            'customer_name' => $customerName,
            'party_size' => $partySize,
            'reservation_time' => $reservationTime,
            'reserved_by' => $userId
        ]);

        apiResponse([], true, 'Masa rezervasyonu oluşturuldu.');
    } else {
        apiResponse([], false, 'Rezervasyon oluşturulurken hata oluştu.', 500);
    }
}

/**
 * Handle change table status
 */
function handleChangeTableStatus() {
    global $tableObj, $userId;

    // Permission check - only admin and manager can change table status
    if (!hasPermission(2)) {
        Security::logSecurityEvent('UNAUTHORIZED_TABLE_STATUS_CHANGE', [
            'user_id' => $userId
        ]);
        apiResponse([], false, 'Bu işlem için yetkiniz bulunmuyor.', 403);
    }

    $tableId = (int)($_POST['table_id'] ?? 0);
    $newStatus = (int)($_POST['status'] ?? 0);

    if ($tableId <= 0) {
        apiResponse([], false, 'Geçersiz masa ID.', 400);
    }

    if (!in_array($newStatus, [0, 1, 2, 3])) { // 0=available, 1=occupied, 2=dirty, 3=reserved
        apiResponse([], false, 'Geçersiz durum kodu.', 400);
    }

    // Check if table exists
    $table = $tableObj->getTable($tableId);
    if (!$table) {
        apiResponse([], false, 'Masa bulunamadı.', 404);
    }

    // Update table status
    $result = $tableObj->updateTableStatus($tableId, $newStatus);

    if ($result) {
        $statusNames = [0 => 'Müsait', 1 => 'Dolu', 2 => 'Kirli', 3 => 'Rezerve'];

        logActivity('TABLE_STATUS_CHANGED', [
            'table_id' => $tableId,
            'old_status' => $table['status'],
            'new_status' => $newStatus,
            'changed_by' => $userId
        ]);

        apiResponse([], true, 'Masa durumu "' . $statusNames[$newStatus] . '" olarak güncellendi.');
    } else {
        apiResponse([], false, 'Masa durumu güncellenirken hata oluştu.', 500);
    }
}

/**
 * Handle move order between tables
 */
function handleMoveOrder() {
    global $orderObj, $userId;

    // Permission check - only admin and manager can move orders
    if (!hasPermission(2)) {
        Security::logSecurityEvent('UNAUTHORIZED_ORDER_MOVE', [
            'user_id' => $userId
        ]);
        apiResponse([], false, 'Bu işlem için yetkiniz bulunmuyor.', 403);
    }

    $fromTableId = (int)($_POST['from_table_id'] ?? 0);
    $toTableId = (int)($_POST['to_table_id'] ?? 0);

    if ($fromTableId <= 0 || $toTableId <= 0) {
        apiResponse([], false, 'Geçersiz masa ID\'leri.', 400);
    }

    if ($fromTableId === $toTableId) {
        apiResponse([], false, 'Aynı masaya taşıma yapılamaz.', 400);
    }

    // Move order
    $result = $orderObj->moveTableOrder($fromTableId, $toTableId);

    if ($result) {
        logActivity('ORDER_MOVED_BETWEEN_TABLES', [
            'from_table_id' => $fromTableId,
            'to_table_id' => $toTableId,
            'moved_by' => $userId
        ]);

        apiResponse([], true, 'Sipariş Masa ' . $toTableId . '\'ye taşındı.');
    } else {
        apiResponse([], false, 'Sipariş taşınırken hata oluştu.', 500);
    }
}

/**
 * Handle merge tables
 */
function handleMergeTables() {
    global $tableObj, $userId;

    // Permission check - only admin and manager can merge tables
    if (!hasPermission(2)) {
        Security::logSecurityEvent('UNAUTHORIZED_TABLE_MERGE', [
            'user_id' => $userId
        ]);
        apiResponse([], false, 'Bu işlem için yetkiniz bulunmuyor.', 403);
    }

    $mainTableId = (int)($_POST['main_table_id'] ?? 0);
    $mergeTableIds = $_POST['merge_table_ids'] ?? [];

    if ($mainTableId <= 0) {
        apiResponse([], false, 'Ana masa ID gereklidir.', 400);
    }

    if (empty($mergeTableIds) || !is_array($mergeTableIds)) {
        apiResponse([], false, 'Birleştirilecek masalar seçilmelidir.', 400);
    }

    // Validate and sanitize merge table IDs
    $mergeTableIds = array_map('intval', $mergeTableIds);
    $mergeTableIds = array_filter($mergeTableIds, function($id) use ($mainTableId) {
        return $id > 0 && $id !== $mainTableId;
    });

    if (empty($mergeTableIds)) {
        apiResponse([], false, 'Geçerli birleştirme masası bulunamadı.', 400);
    }

    // Merge tables
    $result = $tableObj->mergeTables($mainTableId, $mergeTableIds);

    if ($result) {
        logActivity('TABLES_MERGED', [
            'main_table_id' => $mainTableId,
            'merged_table_ids' => $mergeTableIds,
            'merged_by' => $userId
        ]);

        apiResponse([], true, 'Masalar başarıyla birleştirildi.');
    } else {
        apiResponse([], false, 'Masalar birleştirilirken hata oluştu.', 500);
    }
}

/**
 * Handle split table
 */
function handleSplitTable() {
    global $tableObj, $userId;

    // Permission check - only admin and manager can split tables
    if (!hasPermission(2)) {
        Security::logSecurityEvent('UNAUTHORIZED_TABLE_SPLIT', [
            'user_id' => $userId
        ]);
        apiResponse([], false, 'Bu işlem için yetkiniz bulunmuyor.', 403);
    }

    $tableId = (int)($_POST['table_id'] ?? 0);

    if ($tableId <= 0) {
        apiResponse([], false, 'Geçersiz masa ID.', 400);
    }

    // Split table
    $result = $tableObj->splitTable($tableId);

    if ($result) {
        logActivity('TABLE_SPLIT', [
            'table_id' => $tableId,
            'split_by' => $userId
        ]);

        apiResponse([], true, 'Masa ayrımı yapıldı.');
    } else {
        apiResponse([], false, 'Masa ayrımı yapılırken hata oluştu.', 500);
    }
}

/**
 * Handle get table info
 */
function handleGetTableInfo() {
    global $tableObj, $orderObj;

    $tableId = (int)($_GET['table_id'] ?? 0);

    if ($tableId <= 0) {
        apiResponse([], false, 'Geçersiz masa ID.', 400);
    }

    // Get table information
    $table = $tableObj->getTable($tableId);
    if (!$table) {
        apiResponse([], false, 'Masa bulunamadı.', 404);
    }

    // Get table orders
    $orders = $orderObj->getTableOrders($tableId);

    // Calculate totals
    $orderTotal = 0;
    $itemCount = 0;
    foreach ($orders as $order) {
        $orderTotal += $order['total_price'];
        $itemCount += $order['quantity'];
    }

    // Get table history (last 10 activities)
    $history = $tableObj->getTableHistory($tableId, 10);

    $response = [
        'table' => $table,
        'orders' => $orders,
        'summary' => [
            'total_amount' => $orderTotal,
            'item_count' => $itemCount,
            'order_count' => count($orders)
        ],
        'history' => $history
    ];

    apiResponse($response, true, 'Masa bilgileri getirildi.');
}

/**
 * Handle update table capacity
 */
function handleUpdateTableCapacity() {
    global $tableObj, $userId;

    // Permission check - only admin can update table capacity
    if (!hasPermission(1)) {
        Security::logSecurityEvent('UNAUTHORIZED_TABLE_CAPACITY_UPDATE', [
            'user_id' => $userId
        ]);
        apiResponse([], false, 'Bu işlem için yetkiniz bulunmuyor.', 403);
    }

    $tableId = (int)($_POST['table_id'] ?? 0);
    $capacity = (int)($_POST['capacity'] ?? 0);

    if ($tableId <= 0) {
        apiResponse([], false, 'Geçersiz masa ID.', 400);
    }

    if ($capacity < 1 || $capacity > 20) {
        apiResponse([], false, 'Masa kapasitesi 1-20 arasında olmalıdır.', 400);
    }

    // Check if table exists
    $table = $tableObj->getTable($tableId);
    if (!$table) {
        apiResponse([], false, 'Masa bulunamadı.', 404);
    }

    // Update table capacity
    $result = $tableObj->updateTableCapacity($tableId, $capacity);

    if ($result) {
        logActivity('TABLE_CAPACITY_UPDATED', [
            'table_id' => $tableId,
            'old_capacity' => $table['capacity'],
            'new_capacity' => $capacity,
            'updated_by' => $userId
        ]);

        apiResponse([], true, 'Masa kapasitesi güncellendi.');
    } else {
        apiResponse([], false, 'Masa kapasitesi güncellenirken hata oluştu.', 500);
    }
}

/**
 * Handle get table history
 */
function handleGetTableHistory() {
    global $tableObj;

    // Permission check - only admin and manager can view table history
    if (!hasPermission(2)) {
        apiResponse([], false, 'Bu rapor için yetkiniz bulunmuyor.', 403);
    }

    $tableId = (int)($_GET['table_id'] ?? 0);
    $limit = min(100, max(10, (int)($_GET['limit'] ?? 50)));

    if ($tableId <= 0) {
        apiResponse([], false, 'Geçersiz masa ID.', 400);
    }

    // Get table history
    $history = $tableObj->getTableHistory($tableId, $limit);

    apiResponse([
        'table_id' => $tableId,
        'history' => $history,
        'count' => count($history)
    ], true, 'Masa geçmişi getirildi.');
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
