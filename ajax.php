<?php
/**
 * AJAX Request Handler with Security Middleware
 * Restaurant ERP System v2.0
 * Secure AJAX Operations
 */

// Prevent direct access
if (!defined('AJAX_REQUEST')) {
    define('AJAX_REQUEST', true);
}

require_once "inc/global.php";

// Force JSON response
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

// Only allow AJAX requests
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) ||
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {

    Security::logSecurityEvent('NON_AJAX_REQUEST_BLOCKED', [
        'url' => $_SERVER['REQUEST_URI'] ?? '',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
    ]);

    http_response_code(403);
    echo json_encode([
        'success' => false,
        'error' => 'Only AJAX requests allowed'
    ]);
    exit;
}

// Rate limiting for AJAX requests
Security::checkRateLimit('ajax_requests', 30, 60); // 30 requests per minute

// Authentication check for protected endpoints
$protected_actions = [
    'get_user_info', 'update_profile', 'delete_item',
    'financial_data', 'sensitive_operation', 'admin_action'
];

$action = Security::sanitizeInput($_POST['action'] ?? $_GET['action'] ?? '');

if (in_array($action, $protected_actions)) {
    if (!isset($_SESSION['user_session'])) {
        apiResponse([], false, 'Authentication required', 401);
    }

    // Update last activity
    $userObj = new User();
    $userObj->updateLastActivity($_SESSION['user_session']);
}

// CSRF validation for state-changing operations
$csrf_protected_actions = [
    'update_profile', 'delete_item', 'create_order',
    'update_inventory', 'financial_transaction'
];

if (in_array($action, $csrf_protected_actions)) {
    if (!Security::validateCSRFToken($_POST['csrf_token'] ?? '')) {
        Security::logSecurityEvent('AJAX_CSRF_VIOLATION', [
            'action' => $action,
            'user_id' => $_SESSION['user_session'] ?? null
        ]);
        apiResponse([], false, 'CSRF token validation failed', 403);
    }
}

// Input validation and sanitization
$input = [];
if (!empty($_POST)) {
    $input = array_merge($input, Security::sanitizeInput($_POST));
}
if (!empty($_GET)) {
    $input = array_merge($input, Security::sanitizeInput($_GET));
}

try {
    switch ($action) {

        // USER OPERATIONS
        case 'get_user_info':
            handleGetUserInfo();
            break;

        case 'search_users':
            handleSearchUsers($input);
            break;

        case 'toggle_user_status':
            handleToggleUserStatus($input);
            break;

        // TABLE OPERATIONS
        case 'get_table_status':
            handleGetTableStatus($input);
            break;

        case 'update_table_status':
            handleUpdateTableStatus($input);
            break;

        case 'get_table_orders':
            handleGetTableOrders($input);
            break;

        // ORDER OPERATIONS
        case 'add_order_item':
            handleAddOrderItem($input);
            break;

        case 'remove_order_item':
            handleRemoveOrderItem($input);
            break;

        case 'update_order_quantity':
            handleUpdateOrderQuantity($input);
            break;

        // INVENTORY OPERATIONS
        case 'get_low_stock_items':
            handleGetLowStockItems();
            break;

        case 'update_stock':
            handleUpdateStock($input);
            break;

        case 'search_materials':
            handleSearchMaterials($input);
            break;

        // FINANCIAL OPERATIONS
        case 'get_daily_sales':
            handleGetDailySales($input);
            break;

        case 'add_expense':
            handleAddExpense($input);
            break;

        // NOTIFICATION OPERATIONS
        case 'mark_notification_read':
            handleMarkNotificationRead($input);
            break;

        case 'get_notifications':
            handleGetNotifications();
            break;

        // SYSTEM OPERATIONS
        case 'backup_database':
            handleBackupDatabase();
            break;

        case 'system_health':
            handleSystemHealth();
            break;

        default:
            Security::logSecurityEvent('INVALID_AJAX_ACTION', [
                'action' => $action,
                'user_id' => $_SESSION['user_session'] ?? null
            ]);
            apiResponse([], false, 'Invalid action', 400);
    }

} catch (Exception $e) {
    Security::logSecurityEvent('AJAX_EXCEPTION', [
        'action' => $action,
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);

    if (DEBUG_MODE) {
        apiResponse([], false, 'Error: ' . $e->getMessage(), 500);
    } else {
        apiResponse([], false, 'An error occurred while processing your request', 500);
    }
}

// ========================================
// USER OPERATIONS
// ========================================

function handleGetUserInfo() {
    if (!hasPermission(2)) {
        apiResponse([], false, 'Insufficient permissions', 403);
    }

    $userObj = new User();
    $userId = $_SESSION['user_session'];
    $userInfo = $userObj->getOneUser($userId);

    if ($userInfo) {
        // Remove sensitive data
        unset($userInfo['password']);
        apiResponse($userInfo, true, 'User info retrieved successfully');
    } else {
        apiResponse([], false, 'User not found', 404);
    }
}

function handleSearchUsers($input) {
    if (!hasPermission(1)) {
        apiResponse([], false, 'Insufficient permissions', 403);
    }

    $searchTerm = $input['search'] ?? '';
    $page = max(1, (int)($input['page'] ?? 1));
    $limit = min(50, max(5, (int)($input['limit'] ?? 20)));
    $offset = ($page - 1) * $limit;

    $userObj = new User();
    $users = $userObj->searchUsers($searchTerm, $limit, $offset);

    // Remove sensitive data
    foreach ($users as &$user) {
        unset($user['password']);
    }

    apiResponse([
        'users' => $users,
        'page' => $page,
        'limit' => $limit
    ], true, 'Users retrieved successfully');
}

function handleToggleUserStatus($input) {
    if (!hasPermission(1)) {
        apiResponse([], false, 'Insufficient permissions', 403);
    }

    $userId = (int)($input['user_id'] ?? 0);
    $status = (bool)($input['status'] ?? false);

    if ($userId <= 0) {
        apiResponse([], false, 'Invalid user ID', 400);
    }

    if ($userId === $_SESSION['user_session']) {
        apiResponse([], false, 'Cannot change your own status', 400);
    }

    $userObj = new User();
    $result = $userObj->toggleUserStatus($userId, $status);

    if ($result) {
        logActivity('USER_STATUS_CHANGED', [
            'target_user_id' => $userId,
            'new_status' => $status ? 'active' : 'inactive'
        ]);

        apiResponse([], true, 'User status updated successfully');
    } else {
        apiResponse([], false, 'Failed to update user status', 500);
    }
}

// ========================================
// TABLE OPERATIONS
// ========================================

function handleGetTableStatus($input) {
    $tableId = (int)($input['table_id'] ?? 0);

    if ($tableId <= 0) {
        apiResponse([], false, 'Invalid table ID', 400);
    }

    $tableObj = new Table();
    $orderObj = new Order();

    $table = $tableObj->getTable($tableId);
    $orders = $orderObj->getTableOrders($tableId);

    if ($table) {
        apiResponse([
            'table' => $table,
            'orders' => $orders,
            'total' => array_sum(array_column($orders, 'total_price'))
        ], true, 'Table status retrieved');
    } else {
        apiResponse([], false, 'Table not found', 404);
    }
}

function handleUpdateTableStatus($input) {
    $tableId = (int)($input['table_id'] ?? 0);
    $status = (int)($input['status'] ?? 0);

    if ($tableId <= 0 || !in_array($status, [0, 1, 2])) {
        apiResponse([], false, 'Invalid parameters', 400);
    }

    $tableObj = new Table();
    $result = $tableObj->updateTableStatus($tableId, $status);

    if ($result) {
        logActivity('TABLE_STATUS_UPDATED', [
            'table_id' => $tableId,
            'new_status' => $status
        ]);

        apiResponse([], true, 'Table status updated');
    } else {
        apiResponse([], false, 'Failed to update table status', 500);
    }
}

// ========================================
// INVENTORY OPERATIONS
// ========================================

function handleGetLowStockItems() {
    if (!hasPermission(2)) {
        apiResponse([], false, 'Insufficient permissions', 403);
    }

    $materialObj = new Material();
    $lowStockItems = $materialObj->getLowStock();

    apiResponse([
        'items' => $lowStockItems,
        'count' => count($lowStockItems)
    ], true, 'Low stock items retrieved');
}

function handleUpdateStock($input) {
    if (!hasPermission(2)) {
        apiResponse([], false, 'Insufficient permissions', 403);
    }

    $materialId = (int)($input['material_id'] ?? 0);
    $quantity = (float)($input['quantity'] ?? 0);
    $type = $input['type'] ?? 'in'; // 'in' or 'out'
    $notes = $input['notes'] ?? '';

    if ($materialId <= 0 || $quantity <= 0 || !in_array($type, ['in', 'out'])) {
        apiResponse([], false, 'Invalid parameters', 400);
    }

    $materialObj = new Material();
    $result = $materialObj->updateStock($materialId, $quantity, $type, 'manual', null, $_SESSION['user_session'], $notes);

    if ($result) {
        logActivity('STOCK_UPDATED', [
            'material_id' => $materialId,
            'quantity' => $quantity,
            'type' => $type
        ]);

        apiResponse([], true, 'Stock updated successfully');
    } else {
        apiResponse([], false, 'Failed to update stock', 500);
    }
}

function handleSearchMaterials($input) {
    $searchTerm = $input['search'] ?? '';
    $limit = min(50, max(5, (int)($input['limit'] ?? 20)));

    $materialObj = new Material();
    $materials = $materialObj->searchMaterials($searchTerm, $limit);

    apiResponse([
        'materials' => $materials,
        'count' => count($materials)
    ], true, 'Materials retrieved');
}

// ========================================
// FINANCIAL OPERATIONS
// ========================================

function handleGetDailySales($input) {
    if (!hasPermission(2)) {
        apiResponse([], false, 'Insufficient permissions', 403);
    }

    $date = $input['date'] ?? date('Y-m-d');

    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        apiResponse([], false, 'Invalid date format', 400);
    }

    $financeObj = new Finance();
    $salesData = $financeObj->getDailySales($date);

    apiResponse($salesData, true, 'Daily sales data retrieved');
}

function handleAddExpense($input) {
    if (!hasPermission(2)) {
        apiResponse([], false, 'Insufficient permissions', 403);
    }

    $category = $input['category'] ?? '';
    $amount = (float)($input['amount'] ?? 0);
    $description = $input['description'] ?? '';
    $paymentMethod = $input['payment_method'] ?? 'cash';

    if (empty($category) || $amount <= 0) {
        apiResponse([], false, 'Invalid expense data', 400);
    }

    $financeObj = new Finance();
    $result = $financeObj->addExpense([
        'category' => $category,
        'amount' => $amount,
        'description' => $description,
        'expense_date' => date('Y-m-d'),
        'payment_method' => $paymentMethod,
        'user_id' => $_SESSION['user_session']
    ]);

    if ($result) {
        logActivity('EXPENSE_ADDED', [
            'category' => $category,
            'amount' => $amount
        ]);

        apiResponse([], true, 'Expense added successfully');
    } else {
        apiResponse([], false, 'Failed to add expense', 500);
    }
}

// ========================================
// NOTIFICATION OPERATIONS
// ========================================

function handleMarkNotificationRead($input) {
    $notificationId = (int)($input['notification_id'] ?? 0);

    if ($notificationId <= 0) {
        apiResponse([], false, 'Invalid notification ID', 400);
    }

    $notificationObj = new Notification();
    $result = $notificationObj->markAsRead($notificationId, $_SESSION['user_session']);

    if ($result) {
        apiResponse([], true, 'Notification marked as read');
    } else {
        apiResponse([], false, 'Failed to mark notification', 500);
    }
}

function handleGetNotifications() {
    $notificationObj = new Notification();
    $notifications = $notificationObj->getUserNotifications($_SESSION['user_session'], 20);

    apiResponse([
        'notifications' => $notifications,
        'unread_count' => $notificationObj->getUnreadCount($_SESSION['user_session'])
    ], true, 'Notifications retrieved');
}

// ========================================
// SYSTEM OPERATIONS
// ========================================

function handleBackupDatabase() {
    if (!hasPermission(1)) {
        apiResponse([], false, 'Insufficient permissions', 403);
    }

    $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
    $backup_file = backupDatabase($filename);

    if ($backup_file) {
        logActivity('DATABASE_BACKUP', ['filename' => $filename]);
        apiResponse(['filename' => $filename], true, 'Database backup completed');
    } else {
        apiResponse([], false, 'Backup failed', 500);
    }
}

function handleSystemHealth() {
    if (!hasPermission(1)) {
        apiResponse([], false, 'Insufficient permissions', 403);
    }

    $health = [
        'php_version' => PHP_VERSION,
        'memory_usage' => memory_get_usage(true),
        'memory_limit' => ini_get('memory_limit'),
        'disk_free_space' => disk_free_space('.'),
        'uptime' => $_SESSION['created'] ? time() - $_SESSION['created'] : 0,
        'database_status' => checkDatabaseConnection(),
        'cache_status' => checkCacheDirectory(),
        'log_files_size' => getLogFilesSize()
    ];

    apiResponse($health, true, 'System health retrieved');
}

// Helper functions
function checkDatabaseConnection() {
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        return ['status' => 'ok', 'message' => 'Database connection successful'];
    } catch (Exception $e) {
        return ['status' => 'error', 'message' => 'Database connection failed'];
    }
}

function checkCacheDirectory() {
    $cache_dir = dirname(__DIR__) . '/cache/';
    if (is_dir($cache_dir) && is_writable($cache_dir)) {
        return ['status' => 'ok', 'message' => 'Cache directory is writable'];
    } else {
        return ['status' => 'warning', 'message' => 'Cache directory not writable'];
    }
}

function getLogFilesSize() {
    $log_dir = dirname(__DIR__) . '/logs/';
    $total_size = 0;

    if (is_dir($log_dir)) {
        $files = glob($log_dir . '*.log');
        foreach ($files as $file) {
            $total_size += filesize($file);
        }
    }

    return $total_size;
}
