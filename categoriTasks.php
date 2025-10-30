<?php
/**
 * Secure Category Tasks Handler
 * Restaurant ERP System v2.0
 * Enhanced Security Implementation
 */

require_once "inc/global.php";

// Güvenlik kontrolleri
Security::checkRateLimit('category_operations', 15, 300);

// Authentication check
if (!isset($_SESSION['user_session'])) {
    Security::logSecurityEvent('UNAUTHORIZED_CATEGORY_ACCESS');
    apiResponse([], false, 'Oturum bulunamadı.', 401);
}

// Permission check - only admin and manager can manage categories
if (!hasPermission(2)) {
    Security::logSecurityEvent('UNAUTHORIZED_CATEGORY_OPERATION', [
        'user_id' => $_SESSION['user_session'],
        'task' => $_REQUEST['task'] ?? 'unknown'
    ]);
    apiResponse([], false, 'Bu işlem için yetkiniz bulunmuyor.', 403);
}

$menuObj = new Menu();
$task = Security::sanitizeInput($_REQUEST['task'] ?? '');

// CSRF validation for state-changing operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Security::validateCSRFToken($_POST['csrf_token'] ?? '')) {
        Security::logSecurityEvent('CATEGORY_CSRF_VIOLATION', [
            'task' => $task,
            'user_id' => $_SESSION['user_session']
        ]);
        apiResponse([], false, 'Güvenlik doğrulaması başarısız.', 403);
    }
}

try {
    switch ($task) {
        case "catAdd":
            handleAddCategory($menuObj);
            break;

        case "catDelete":
            handleDeleteCategory($menuObj);
            break;

        case "catEdit":
            handleEditCategory($menuObj);
            break;

        case "getCategoryList":
            handleGetCategoryList($menuObj);
            break;

        case "getCategoryProducts":
            handleGetCategoryProducts($menuObj);
            break;

        default:
            Security::logSecurityEvent('INVALID_CATEGORY_TASK', [
                'task' => $task,
                'user_id' => $_SESSION['user_session']
            ]);
            apiResponse([], false, 'Geçersiz işlem.', 400);
    }

} catch (Exception $e) {
    Security::logSecurityEvent('CATEGORY_TASK_EXCEPTION', [
        'task' => $task,
        'message' => $e->getMessage(),
        'user_id' => $_SESSION['user_session']
    ]);

    if (DEBUG_MODE) {
        apiResponse([], false, 'Hata: ' . $e->getMessage(), 500);
    } else {
        apiResponse([], false, 'İşlem sırasında bir hata oluştu.', 500);
    }
}

/**
 * Handle add category
 */
function handleAddCategory($menuObj) {
    // Input validation
    $categoryName = Security::sanitizeInput($_POST['catName'] ?? '');

    if (empty($categoryName)) {
        redirectWithMessage('categories.php', 'Kategori adı gereklidir.', 'error');
    }

    if (strlen($categoryName) < 2) {
        redirectWithMessage('categories.php', 'Kategori adı en az 2 karakter olmalıdır.', 'error');
    }

    if (strlen($categoryName) > 100) {
        redirectWithMessage('categories.php', 'Kategori adı çok uzun.', 'error');
    }

    // Check for invalid characters
    if (!preg_match('/^[a-zA-ZğüşıöçĞÜŞIÖÇ0-9\s\-\_\.\,\!\?]+$/u', $categoryName)) {
        redirectWithMessage('categories.php', 'Kategori adında geçersiz karakterler bulunuyor.', 'error');
    }

    // Check if category already exists
    if ($menuObj->categoryExists($categoryName)) {
        redirectWithMessage('categories.php', 'Bu kategori zaten mevcut.', 'error');
    }

    // Add category
    $result = $menuObj->categoryAdd($categoryName);

    if ($result) {
        logActivity('CATEGORY_ADDED', [
            'category_name' => $categoryName,
            'user_id' => $_SESSION['user_session']
        ]);

        redirectWithMessage('categories.php', 'Kategori başarıyla eklendi.', 'success');
    } else {
        redirectWithMessage('categories.php', 'Kategori eklenirken hata oluştu.', 'error');
    }
}

/**
 * Handle delete category
 */
function handleDeleteCategory($menuObj) {
    $categoryId = (int)($_GET['id'] ?? 0);

    if ($categoryId <= 0) {
        redirectWithMessage('categories.php', 'Geçersiz kategori ID.', 'error');
    }

    // Check if category exists
    $category = $menuObj->getCategory($categoryId);
    if (!$category) {
        redirectWithMessage('categories.php', 'Kategori bulunamadı.', 'error');
    }

    // Check if category has products
    $productCount = $menuObj->getCategoryProductCount($categoryId);
    if ($productCount > 0) {
        redirectWithMessage('categories.php',
            'Bu kategoriye ait ' . $productCount . ' ürün bulunuyor. Önce ürünleri silin veya başka kategoriye taşıyın.',
            'error');
    }

    // Delete category
    $result = $menuObj->categoryDelete($categoryId);

    if ($result) {
        logActivity('CATEGORY_DELETED', [
            'category_id' => $categoryId,
            'category_name' => $category['name'] ?? 'Unknown',
            'user_id' => $_SESSION['user_session']
        ]);

        Security::logSecurityEvent('CATEGORY_DELETED', [
            'category_id' => $categoryId,
            'category_name' => $category['name'] ?? 'Unknown'
        ]);

        redirectWithMessage('categories.php', 'Kategori başarıyla silindi.', 'success');
    } else {
        redirectWithMessage('categories.php', 'Kategori silinirken hata oluştu.', 'error');
    }
}

/**
 * Handle edit category
 */
function handleEditCategory($menuObj) {
    // Input validation
    $categoryId = (int)($_POST['catId'] ?? 0);
    $categoryName = Security::sanitizeInput($_POST['catName'] ?? '');

    if ($categoryId <= 0) {
        redirectWithMessage('categories.php', 'Geçersiz kategori ID.', 'error');
    }

    if (empty($categoryName)) {
        redirectWithMessage('categories.php', 'Kategori adı gereklidir.', 'error');
    }

    if (strlen($categoryName) < 2) {
        redirectWithMessage('categories.php', 'Kategori adı en az 2 karakter olmalıdır.', 'error');
    }

    if (strlen($categoryName) > 100) {
        redirectWithMessage('categories.php', 'Kategori adı çok uzun.', 'error');
    }

    // Check for invalid characters
    if (!preg_match('/^[a-zA-ZğüşıöçĞÜŞIÖÇ0-9\s\-\_\.\,\!\?]+$/u', $categoryName)) {
        redirectWithMessage('categories.php', 'Kategori adında geçersiz karakterler bulunuyor.', 'error');
    }

    // Check if category exists
    $existingCategory = $menuObj->getCategory($categoryId);
    if (!$existingCategory) {
        redirectWithMessage('categories.php', 'Kategori bulunamadı.', 'error');
    }

    // Check if new name is already used by another category
    if ($menuObj->categoryExists($categoryName, $categoryId)) {
        redirectWithMessage('categories.php', 'Bu kategori adı zaten kullanılıyor.', 'error');
    }

    // Update category
    $result = $menuObj->editCategory($categoryId, $categoryName);

    if ($result) {
        logActivity('CATEGORY_UPDATED', [
            'category_id' => $categoryId,
            'old_name' => $existingCategory['name'] ?? 'Unknown',
            'new_name' => $categoryName,
            'user_id' => $_SESSION['user_session']
        ]);

        redirectWithMessage('categories.php', 'Kategori başarıyla güncellendi.', 'success');
    } else {
        redirectWithMessage('categories.php', 'Kategori güncellenirken hata oluştu.', 'error');
    }
}

/**
 * Handle get category list (AJAX)
 */
function handleGetCategoryList($menuObj) {
    $categories = $menuObj->getAllCategories();

    foreach ($categories as &$category) {
        $category['product_count'] = $menuObj->getCategoryProductCount($category['id']);
        $category['name'] = htmlspecialchars($category['name']);
    }

    apiResponse([
        'categories' => $categories,
        'count' => count($categories)
    ], true, 'Kategoriler başarıyla getirildi.');
}

/**
 * Handle get category products (AJAX)
 */
function handleGetCategoryProducts($menuObj) {
    $categoryId = (int)($_GET['category_id'] ?? 0);

    if ($categoryId <= 0) {
        apiResponse([], false, 'Geçersiz kategori ID.', 400);
    }

    $products = $menuObj->getCategoryProducts($categoryId);

    foreach ($products as &$product) {
        $product['name'] = htmlspecialchars($product['name']);
        $product['price'] = (float)$product['price'];
    }

    apiResponse([
        'products' => $products,
        'count' => count($products),
        'category_id' => $categoryId
    ], true, 'Kategori ürünleri başarıyla getirildi.');
}

/**
 * Helper function for redirecting with messages
 */
function redirectWithMessage($url, $message, $type = 'info') {
    FlashMessage::set($message, $type);
    redirect($url);
}
