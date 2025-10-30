<?php
/**
 * Secure Menu Tasks Handler
 * Restaurant ERP System v2.0
 * Enhanced Security Implementation
 */

require_once "inc/global.php";

// Güvenlik kontrolleri
Security::checkRateLimit('menu_operations', 20, 300);

// Authentication check
if (!isset($_SESSION['user_session'])) {
    Security::logSecurityEvent('UNAUTHORIZED_MENU_ACCESS');
    apiResponse([], false, 'Oturum bulunamadı.', 401);
}

// Permission check - only admin and manager can manage menu
if (!hasPermission(2)) {
    Security::logSecurityEvent('UNAUTHORIZED_MENU_OPERATION', [
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
        Security::logSecurityEvent('MENU_CSRF_VIOLATION', [
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

        case "productAdd":
            handleAddProduct($menuObj);
            break;

        case "catDelete":
            handleDeleteCategory($menuObj);
            break;

        case "productDelete":
            handleDeleteProduct($menuObj);
            break;

        case "catEdit":
            handleEditCategory($menuObj);
            break;

        case "productEdit":
            handleEditProduct($menuObj);
            break;

        case "toggleProductStatus":
            handleToggleProductStatus($menuObj);
            break;

        case "getProductDetails":
            handleGetProductDetails($menuObj);
            break;

        case "updateProductImage":
            handleUpdateProductImage($menuObj);
            break;

        case "getMenuReport":
            handleGetMenuReport($menuObj);
            break;

        default:
            Security::logSecurityEvent('INVALID_MENU_TASK', [
                'task' => $task,
                'user_id' => $_SESSION['user_session']
            ]);
            apiResponse([], false, 'Geçersiz işlem.', 400);
    }

} catch (Exception $e) {
    Security::logSecurityEvent('MENU_TASK_EXCEPTION', [
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
    $categoryName = Security::sanitizeInput($_POST['catName'] ?? '');

    // Validation
    if (empty($categoryName)) {
        redirectWithMessage('categories.php', 'Kategori adı gereklidir.', 'error');
    }

    if (strlen($categoryName) < 2 || strlen($categoryName) > 100) {
        redirectWithMessage('categories.php', 'Kategori adı 2-100 karakter arasında olmalıdır.', 'error');
    }

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
 * Handle add product
 */
function handleAddProduct($menuObj) {
    $productName = Security::sanitizeInput($_POST['productName'] ?? '');
    $productPrice = (float)($_POST['productPrice'] ?? 0);
    $categoryId = (int)($_POST['catId'] ?? 0);
    $description = Security::sanitizeInput($_POST['description'] ?? '');

    // Validation
    if (empty($productName)) {
        redirectWithMessage('products.php', 'Ürün adı gereklidir.', 'error');
    }

    if (strlen($productName) < 2 || strlen($productName) > 150) {
        redirectWithMessage('products.php', 'Ürün adı 2-150 karakter arasında olmalıdır.', 'error');
    }

    if ($productPrice <= 0) {
        redirectWithMessage('products.php', 'Ürün fiyatı sıfırdan büyük olmalıdır.', 'error');
    }

    if ($productPrice > 999999.99) {
        redirectWithMessage('products.php', 'Ürün fiyatı çok yüksek.', 'error');
    }

    if ($categoryId <= 0) {
        redirectWithMessage('products.php', 'Geçerli bir kategori seçiniz.', 'error');
    }

    // Check if category exists
    if (!$menuObj->categoryExists($categoryId)) {
        redirectWithMessage('products.php', 'Seçilen kategori bulunamadı.', 'error');
    }

    // Check if product name already exists in this category
    if ($menuObj->productExistsInCategory($productName, $categoryId)) {
        redirectWithMessage('products.php', 'Bu kategoride aynı isimde ürün zaten mevcut.', 'error');
    }

    // Add product
    $result = $menuObj->productAdd($productName, $productPrice, $categoryId, $description);

    if ($result) {
        logActivity('PRODUCT_ADDED', [
            'product_name' => $productName,
            'product_price' => $productPrice,
            'category_id' => $categoryId,
            'user_id' => $_SESSION['user_session']
        ]);

        redirectWithMessage('products.php', 'Ürün başarıyla eklendi.', 'success');
    } else {
        redirectWithMessage('products.php', 'Ürün eklenirken hata oluştu.', 'error');
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

        redirectWithMessage('categories.php', 'Kategori başarıyla silindi.', 'success');
    } else {
        redirectWithMessage('categories.php', 'Kategori silinirken hata oluştu.', 'error');
    }
}

/**
 * Handle delete product
 */
function handleDeleteProduct($menuObj) {
    $productId = (int)($_GET['id'] ?? 0);

    if ($productId <= 0) {
        redirectWithMessage('products.php', 'Geçersiz ürün ID.', 'error');
    }

    // Check if product exists
    $product = $menuObj->getProduct($productId);
    if (!$product) {
        redirectWithMessage('products.php', 'Ürün bulunamadı.', 'error');
    }

    // Check if product is used in active orders
    $activeOrderCount = $menuObj->getProductActiveOrderCount($productId);
    if ($activeOrderCount > 0) {
        redirectWithMessage('products.php',
            'Bu ürün aktif siparişlerde kullanılıyor. Silemezsiniz.',
            'error');
    }

    // Delete product
    $result = $menuObj->productDelete($productId);

    if ($result) {
        logActivity('PRODUCT_DELETED', [
            'product_id' => $productId,
            'product_name' => $product['name'] ?? 'Unknown',
            'user_id' => $_SESSION['user_session']
        ]);

        Security::logSecurityEvent('PRODUCT_DELETED', [
            'product_id' => $productId,
            'product_name' => $product['name'] ?? 'Unknown'
        ]);

        redirectWithMessage('products.php', 'Ürün başarıyla silindi.', 'success');
    } else {
        redirectWithMessage('products.php', 'Ürün silinirken hata oluştu.', 'error');
    }
}

/**
 * Handle edit category
 */
function handleEditCategory($menuObj) {
    $categoryId = (int)($_POST['catId'] ?? 0);
    $categoryName = Security::sanitizeInput($_POST['catName'] ?? '');

    if ($categoryId <= 0) {
        redirectWithMessage('categories.php', 'Geçersiz kategori ID.', 'error');
    }

    if (empty($categoryName) || strlen($categoryName) < 2 || strlen($categoryName) > 100) {
        redirectWithMessage('categories.php', 'Kategori adı 2-100 karakter arasında olmalıdır.', 'error');
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
 * Handle edit product
 */
function handleEditProduct($menuObj) {
    $productId = (int)($_POST['productId'] ?? 0);
    $categoryId = (int)($_POST['catId'] ?? 0);
    $productName = Security::sanitizeInput($_POST['productName'] ?? '');
    $productPrice = (float)($_POST['productPrice'] ?? 0);
    $description = Security::sanitizeInput($_POST['description'] ?? '');

    // Validation
    if ($productId <= 0) {
        redirectWithMessage('products.php', 'Geçersiz ürün ID.', 'error');
    }

    if (empty($productName) || strlen($productName) < 2 || strlen($productName) > 150) {
        redirectWithMessage('products.php', 'Ürün adı 2-150 karakter arasında olmalıdır.', 'error');
    }

    if ($productPrice <= 0 || $productPrice > 999999.99) {
        redirectWithMessage('products.php', 'Geçerli bir fiyat giriniz.', 'error');
    }

    if ($categoryId <= 0) {
        redirectWithMessage('products.php', 'Geçerli bir kategori seçiniz.', 'error');
    }

    // Check if product exists
    $existingProduct = $menuObj->getProduct($productId);
    if (!$existingProduct) {
        redirectWithMessage('products.php', 'Ürün bulunamadı.', 'error');
    }

    // Check if category exists
    if (!$menuObj->categoryExists($categoryId)) {
        redirectWithMessage('products.php', 'Seçilen kategori bulunamadı.', 'error');
    }

    // Check if product name already exists in this category (excluding current product)
    if ($menuObj->productExistsInCategory($productName, $categoryId, $productId)) {
        redirectWithMessage('products.php', 'Bu kategoride aynı isimde başka bir ürün zaten mevcut.', 'error');
    }

    // Update product
    $result = $menuObj->editProduct($productId, $categoryId, $productName, $productPrice, $description);

    if ($result) {
        logActivity('PRODUCT_UPDATED', [
            'product_id' => $productId,
            'old_name' => $existingProduct['name'] ?? 'Unknown',
            'new_name' => $productName,
            'new_price' => $productPrice,
            'new_category_id' => $categoryId,
            'user_id' => $_SESSION['user_session']
        ]);

        redirectWithMessage('products.php', 'Ürün başarıyla güncellendi.', 'success');
    } else {
        redirectWithMessage('products.php', 'Ürün güncellenirken hata oluştu.', 'error');
    }
}

/**
 * Handle toggle product status
 */
function handleToggleProductStatus($menuObj) {
    $productId = (int)($_POST['product_id'] ?? 0);
    $status = (bool)($_POST['status'] ?? false);

    if ($productId <= 0) {
        apiResponse([], false, 'Geçersiz ürün ID.', 400);
    }

    $product = $menuObj->getProduct($productId);
    if (!$product) {
        apiResponse([], false, 'Ürün bulunamadı.', 404);
    }

    $result = $menuObj->toggleProductStatus($productId, $status);

    if ($result) {
        logActivity('PRODUCT_STATUS_CHANGED', [
            'product_id' => $productId,
            'product_name' => $product['name'],
            'new_status' => $status ? 'active' : 'inactive',
            'user_id' => $_SESSION['user_session']
        ]);

        apiResponse([], true, 'Ürün durumu güncellendi.');
    } else {
        apiResponse([], false, 'Ürün durumu güncellenemedi.', 500);
    }
}

/**
 * Handle get product details
 */
function handleGetProductDetails($menuObj) {
    $productId = (int)($_GET['product_id'] ?? 0);

    if ($productId <= 0) {
        apiResponse([], false, 'Geçersiz ürün ID.', 400);
    }

    $product = $menuObj->getProductDetails($productId);
    if (!$product) {
        apiResponse([], false, 'Ürün bulunamadı.', 404);
    }

    // Sanitize output
    $product['name'] = htmlspecialchars($product['name']);
    $product['description'] = htmlspecialchars($product['description'] ?? '');
    $product['price'] = (float)$product['price'];

    apiResponse($product, true, 'Ürün detayları getirildi.');
}

/**
 * Handle update product image
 */
function handleUpdateProductImage($menuObj) {
    $productId = (int)($_POST['product_id'] ?? 0);

    if ($productId <= 0) {
        apiResponse([], false, 'Geçersiz ürün ID.', 400);
    }

    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        apiResponse([], false, 'Dosya yüklenemedi.', 400);
    }

    // Check if product exists
    $product = $menuObj->getProduct($productId);
    if (!$product) {
        apiResponse([], false, 'Ürün bulunamadı.', 404);
    }

    // Upload image
    $uploadResult = uploadFile($_FILES['image'], 'uploads/products/', ['jpg', 'jpeg', 'png', 'gif']);

    if (!$uploadResult['success']) {
        apiResponse([], false, $uploadResult['error'], 400);
    }

    // Resize image
    $originalPath = $uploadResult['path'];
    $thumbnailPath = str_replace($uploadResult['filename'], 'thumb_' . $uploadResult['filename'], $originalPath);

    if (resizeImage($originalPath, $thumbnailPath, 300, 300)) {
        // Update product image in database
        $result = $menuObj->updateProductImage($productId, $uploadResult['filename']);

        if ($result) {
            // Delete old image if exists
            if (!empty($product['image'])) {
                $oldImagePath = 'uploads/products/' . $product['image'];
                $oldThumbPath = 'uploads/products/thumb_' . $product['image'];
                if (file_exists($oldImagePath)) unlink($oldImagePath);
                if (file_exists($oldThumbPath)) unlink($oldThumbPath);
            }

            logActivity('PRODUCT_IMAGE_UPDATED', [
                'product_id' => $productId,
                'product_name' => $product['name'],
                'new_image' => $uploadResult['filename'],
                'user_id' => $_SESSION['user_session']
            ]);

            apiResponse([
                'image_url' => $sitePath . 'uploads/products/' . $uploadResult['filename'],
                'thumbnail_url' => $sitePath . 'uploads/products/thumb_' . $uploadResult['filename']
            ], true, 'Ürün görseli güncellendi.');
        } else {
            // Delete uploaded file if database update failed
            unlink($originalPath);
            if (file_exists($thumbnailPath)) unlink($thumbnailPath);
            apiResponse([], false, 'Veritabanı güncelleme hatası.', 500);
        }
    } else {
        // Delete uploaded file if resize failed
        unlink($originalPath);
        apiResponse([], false, 'Görsel işlenirken hata oluştu.', 500);
    }
}

/**
 * Handle get menu report
 */
function handleGetMenuReport($menuObj) {
    if (!hasPermission(2)) {
        apiResponse([], false, 'Bu rapor için yetkiniz bulunmuyor.', 403);
    }

    $dateFrom = $_GET['date_from'] ?? date('Y-m-d', strtotime('-30 days'));
    $dateTo = $_GET['date_to'] ?? date('Y-m-d');

    // Validate dates
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFrom) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateTo)) {
        apiResponse([], false, 'Geçersiz tarih formatı.', 400);
    }

    $report = $menuObj->getMenuReport($dateFrom, $dateTo);

    logActivity('MENU_REPORT_GENERATED', [
        'date_from' => $dateFrom,
        'date_to' => $dateTo,
        'user_id' => $_SESSION['user_session']
    ]);

    apiResponse($report, true, 'Menu raporu oluşturuldu.');
}

/**
 * Helper function for redirecting with messages
 */
function redirectWithMessage($url, $message, $type = 'info') {
    FlashMessage::set($message, $type);
    redirect($url);
}
