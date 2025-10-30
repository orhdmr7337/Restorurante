<?php
/**
 * Secure Products Tasks Handler
 * Restaurant ERP System v2.0
 * Enhanced Security Implementation
 */

require_once "inc/global.php";

// Güvenlik kontrolleri
Security::checkRateLimit('product_operations', 20, 300);

// Authentication check
if (!isset($_SESSION['user_session'])) {
    Security::logSecurityEvent('UNAUTHORIZED_PRODUCT_ACCESS');
    apiResponse([], false, 'Oturum bulunamadı.', 401);
}

// Permission check - only admin and manager can manage products
if (!hasPermission(2)) {
    Security::logSecurityEvent('UNAUTHORIZED_PRODUCT_OPERATION', [
        'user_id' => $_SESSION['user_session'],
        'task' => $_REQUEST['task'] ?? 'unknown'
    ]);
    apiResponse([], false, 'Bu işlem için yetkiniz bulunmuyor.', 403);
}

$menuObj = new Menu();
$userId = $_SESSION['user_session'];
$task = Security::sanitizeInput($_REQUEST['task'] ?? '');

// CSRF validation for state-changing operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Security::validateCSRFToken($_POST['csrf_token'] ?? '')) {
        Security::logSecurityEvent('PRODUCT_CSRF_VIOLATION', [
            'task' => $task,
            'user_id' => $userId
        ]);
        apiResponse([], false, 'Güvenlik doğrulaması başarısız.', 403);
    }
}

try {
    switch ($task) {
        case "productAdd":
            handleAddProduct($menuObj);
            break;

        case "productDelete":
            handleDeleteProduct($menuObj);
            break;

        case "productEdit":
            handleEditProduct($menuObj);
            break;

        case "toggleProductStatus":
            handleToggleProductStatus($menuObj);
            break;

        case "updateProductPrice":
            handleUpdateProductPrice($menuObj);
            break;

        case "uploadProductImage":
            handleUploadProductImage($menuObj);
            break;

        case "deleteProductImage":
            handleDeleteProductImage($menuObj);
            break;

        case "duplicateProduct":
            handleDuplicateProduct($menuObj);
            break;

        case "getProductDetails":
            handleGetProductDetails($menuObj);
            break;

        case "updateProductIngredients":
            handleUpdateProductIngredients($menuObj);
            break;

        case "bulkUpdatePrices":
            handleBulkUpdatePrices($menuObj);
            break;

        case "exportProducts":
            handleExportProducts($menuObj);
            break;

        default:
            Security::logSecurityEvent('INVALID_PRODUCT_TASK', [
                'task' => $task,
                'user_id' => $userId
            ]);
            apiResponse([], false, 'Geçersiz işlem.', 400);
    }

} catch (Exception $e) {
    Security::logSecurityEvent('PRODUCT_TASK_EXCEPTION', [
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
 * Handle add product
 */
function handleAddProduct($menuObj) {
    global $userId;

    $productName = Security::sanitizeInput($_POST['productName'] ?? '');
    $productPrice = (float)($_POST['productPrice'] ?? 0);
    $categoryId = (int)($_POST['catId'] ?? 0);
    $description = Security::sanitizeInput($_POST['description'] ?? '');
    $cost = (float)($_POST['cost'] ?? 0);
    $barcode = Security::sanitizeInput($_POST['barcode'] ?? '');

    // Validation
    if (empty($productName)) {
        redirectWithMessage('products.php', 'Ürün adı gereklidir.', 'error');
    }

    if (strlen($productName) < 2 || strlen($productName) > 200) {
        redirectWithMessage('products.php', 'Ürün adı 2-200 karakter arasında olmalıdır.', 'error');
    }

    if (!preg_match('/^[a-zA-ZğüşıöçĞÜŞIÖÇ0-9\s\-\_\.\,\!\?\&\(\)]+$/u', $productName)) {
        redirectWithMessage('products.php', 'Ürün adında geçersiz karakterler bulunuyor.', 'error');
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

    if ($cost < 0 || $cost >= $productPrice) {
        redirectWithMessage('products.php', 'Maliyet fiyatı geçerli değil.', 'error');
    }

    if (!empty($barcode)) {
        if (!preg_match('/^[0-9]{8,13}$/', $barcode)) {
            redirectWithMessage('products.php', 'Barkod 8-13 haneli sayı olmalıdır.', 'error');
        }

        // Check if barcode already exists
        if ($menuObj->barcodeExists($barcode)) {
            redirectWithMessage('products.php', 'Bu barkod zaten kullanılıyor.', 'error');
        }
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
    $productData = [
        'name' => $productName,
        'price' => $productPrice,
        'category_id' => $categoryId,
        'description' => $description,
        'cost' => $cost,
        'barcode' => $barcode
    ];

    $result = $menuObj->productAdd($productData);

    if ($result) {
        logActivity('PRODUCT_ADDED', [
            'product_name' => $productName,
            'product_price' => $productPrice,
            'category_id' => $categoryId,
            'cost' => $cost,
            'user_id' => $userId
        ]);

        redirectWithMessage('products.php', 'Ürün başarıyla eklendi.', 'success');
    } else {
        redirectWithMessage('products.php', 'Ürün eklenirken hata oluştu.', 'error');
    }
}

/**
 * Handle delete product
 */
function handleDeleteProduct($menuObj) {
    global $userId;

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
            'Bu ürün ' . $activeOrderCount . ' aktif siparişte kullanılıyor. Silemezsiniz.',
            'error');
    }

    // Check if product has sales history (last 30 days)
    $recentSalesCount = $menuObj->getProductRecentSalesCount($productId, 30);
    if ($recentSalesCount > 0) {
        // Don't delete, just deactivate
        $result = $menuObj->toggleProductStatus($productId, 0); // 0 = inactive

        if ($result) {
            logActivity('PRODUCT_DEACTIVATED_INSTEAD_OF_DELETE', [
                'product_id' => $productId,
                'product_name' => $product['name'],
                'reason' => 'Has recent sales',
                'user_id' => $userId
            ]);

            redirectWithMessage('products.php',
                'Ürün son 30 günde satış geçmişi olduğu için silinmedi, pasif hale getirildi.',
                'warning');
        } else {
            redirectWithMessage('products.php', 'Ürün pasif hale getirilemedi.', 'error');
        }
        return;
    }

    // Delete product
    $result = $menuObj->productDelete($productId);

    if ($result) {
        // Delete product image if exists
        if (!empty($product['image'])) {
            $imagePath = 'uploads/products/' . $product['image'];
            $thumbnailPath = 'uploads/products/thumb_' . $product['image'];

            if (file_exists($imagePath)) unlink($imagePath);
            if (file_exists($thumbnailPath)) unlink($thumbnailPath);
        }

        logActivity('PRODUCT_DELETED', [
            'product_id' => $productId,
            'product_name' => $product['name'] ?? 'Unknown',
            'product_price' => $product['price'] ?? 0,
            'user_id' => $userId
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
 * Handle edit product
 */
function handleEditProduct($menuObj) {
    global $userId;

    $productId = (int)($_POST['productId'] ?? 0);
    $categoryId = (int)($_POST['catId'] ?? 0);
    $productName = Security::sanitizeInput($_POST['productName'] ?? '');
    $productPrice = (float)($_POST['productPrice'] ?? 0);
    $description = Security::sanitizeInput($_POST['description'] ?? '');
    $cost = (float)($_POST['cost'] ?? 0);
    $barcode = Security::sanitizeInput($_POST['barcode'] ?? '');

    // Validation
    if ($productId <= 0) {
        redirectWithMessage('products.php', 'Geçersiz ürün ID.', 'error');
    }

    if (empty($productName) || strlen($productName) < 2 || strlen($productName) > 200) {
        redirectWithMessage('products.php', 'Ürün adı 2-200 karakter arasında olmalıdır.', 'error');
    }

    if ($productPrice <= 0 || $productPrice > 999999.99) {
        redirectWithMessage('products.php', 'Geçerli bir fiyat giriniz (0-999999.99).', 'error');
    }

    if ($categoryId <= 0) {
        redirectWithMessage('products.php', 'Geçerli bir kategori seçiniz.', 'error');
    }

    if ($cost < 0 || $cost >= $productPrice) {
        redirectWithMessage('products.php', 'Maliyet fiyatı geçerli değil.', 'error');
    }

    if (!empty($barcode)) {
        if (!preg_match('/^[0-9]{8,13}$/', $barcode)) {
            redirectWithMessage('products.php', 'Barkod 8-13 haneli sayı olmalıdır.', 'error');
        }

        // Check if barcode exists for another product
        if ($menuObj->barcodeExists($barcode, $productId)) {
            redirectWithMessage('products.php', 'Bu barkod başka bir ürün tarafından kullanılıyor.', 'error');
        }
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
    $productData = [
        'name' => $productName,
        'price' => $productPrice,
        'category_id' => $categoryId,
        'description' => $description,
        'cost' => $cost,
        'barcode' => $barcode
    ];

    $result = $menuObj->editProduct($productId, $productData);

    if ($result) {
        logActivity('PRODUCT_UPDATED', [
            'product_id' => $productId,
            'old_name' => $existingProduct['name'] ?? 'Unknown',
            'new_name' => $productName,
            'old_price' => $existingProduct['price'] ?? 0,
            'new_price' => $productPrice,
            'new_category_id' => $categoryId,
            'user_id' => $userId
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
    global $userId;

    $productId = (int)($_POST['product_id'] ?? 0);
    $status = (bool)($_POST['status'] ?? false);

    if ($productId <= 0) {
        apiResponse([], false, 'Geçersiz ürün ID.', 400);
    }

    $product = $menuObj->getProduct($productId);
    if (!$product) {
        apiResponse([], false, 'Ürün bulunamadı.', 404);
    }

    $result = $menuObj->toggleProductStatus($productId, $status ? 1 : 0);

    if ($result) {
        logActivity('PRODUCT_STATUS_CHANGED', [
            'product_id' => $productId,
            'product_name' => $product['name'],
            'old_status' => $product['status'],
            'new_status' => $status ? 1 : 0,
            'user_id' => $userId
        ]);

        $statusText = $status ? 'aktif' : 'pasif';
        apiResponse([], true, 'Ürün ' . $statusText . ' duruma getirildi.');
    } else {
        apiResponse([], false, 'Ürün durumu güncellenemedi.', 500);
    }
}

/**
 * Handle update product price
 */
function handleUpdateProductPrice($menuObj) {
    global $userId;

    $productId = (int)($_POST['product_id'] ?? 0);
    $newPrice = (float)($_POST['price'] ?? 0);

    if ($productId <= 0) {
        apiResponse([], false, 'Geçersiz ürün ID.', 400);
    }

    if ($newPrice <= 0 || $newPrice > 999999.99) {
        apiResponse([], false, 'Geçerli bir fiyat giriniz.', 400);
    }

    $product = $menuObj->getProduct($productId);
    if (!$product) {
        apiResponse([], false, 'Ürün bulunamadı.', 404);
    }

    $result = $menuObj->updateProductPrice($productId, $newPrice);

    if ($result) {
        logActivity('PRODUCT_PRICE_UPDATED', [
            'product_id' => $productId,
            'product_name' => $product['name'],
            'old_price' => $product['price'],
            'new_price' => $newPrice,
            'user_id' => $userId
        ]);

        apiResponse([], true, 'Ürün fiyatı güncellendi.');
    } else {
        apiResponse([], false, 'Fiyat güncellenirken hata oluştu.', 500);
    }
}

/**
 * Handle upload product image
 */
function handleUploadProductImage($menuObj) {
    global $userId;

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
    $uploadResult = uploadFile($_FILES['image'], 'uploads/products/', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

    if (!$uploadResult['success']) {
        apiResponse([], false, $uploadResult['error'], 400);
    }

    // Create thumbnail
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

            logActivity('PRODUCT_IMAGE_UPLOADED', [
                'product_id' => $productId,
                'product_name' => $product['name'],
                'new_image' => $uploadResult['filename'],
                'user_id' => $userId
            ]);

            global $sitePath;
            apiResponse([
                'image_url' => $sitePath . 'uploads/products/' . $uploadResult['filename'],
                'thumbnail_url' => $sitePath . 'uploads/products/thumb_' . $uploadResult['filename']
            ], true, 'Ürün görseli yüklendi.');
        } else {
            // Delete uploaded files if database update failed
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
 * Handle delete product image
 */
function handleDeleteProductImage($menuObj) {
    global $userId;

    $productId = (int)($_POST['product_id'] ?? 0);

    if ($productId <= 0) {
        apiResponse([], false, 'Geçersiz ürün ID.', 400);
    }

    $product = $menuObj->getProduct($productId);
    if (!$product) {
        apiResponse([], false, 'Ürün bulunamadı.', 404);
    }

    if (empty($product['image'])) {
        apiResponse([], false, 'Ürünün görseli bulunmuyor.', 400);
    }

    // Delete image files
    $imagePath = 'uploads/products/' . $product['image'];
    $thumbnailPath = 'uploads/products/thumb_' . $product['image'];

    if (file_exists($imagePath)) unlink($imagePath);
    if (file_exists($thumbnailPath)) unlink($thumbnailPath);

    // Update database
    $result = $menuObj->updateProductImage($productId, '');

    if ($result) {
        logActivity('PRODUCT_IMAGE_DELETED', [
            'product_id' => $productId,
            'product_name' => $product['name'],
            'deleted_image' => $product['image'],
            'user_id' => $userId
        ]);

        apiResponse([], true, 'Ürün görseli silindi.');
    } else {
        apiResponse([], false, 'Görsel silinirken hata oluştu.', 500);
    }
}

/**
 * Handle duplicate product
 */
function handleDuplicateProduct($menuObj) {
    global $userId;

    $productId = (int)($_POST['product_id'] ?? 0);
    $newName = Security::sanitizeInput($_POST['new_name'] ?? '');

    if ($productId <= 0) {
        apiResponse([], false, 'Geçersiz ürün ID.', 400);
    }

    $product = $menuObj->getProduct($productId);
    if (!$product) {
        apiResponse([], false, 'Ürün bulunamadı.', 404);
    }

    if (empty($newName)) {
        $newName = $product['name'] . ' (Kopya)';
    }

    // Check if new name already exists
    if ($menuObj->productExistsInCategory($newName, $product['category_id'])) {
        apiResponse([], false, 'Bu isimde ürün zaten mevcut.', 400);
    }

    $result = $menuObj->duplicateProduct($productId, $newName);

    if ($result) {
        logActivity('PRODUCT_DUPLICATED', [
            'original_product_id' => $productId,
            'original_product_name' => $product['name'],
            'new_product_name' => $newName,
            'user_id' => $userId
        ]);

        apiResponse(['new_product_id' => $result], true, 'Ürün kopyalandı.');
    } else {
        apiResponse([], false, 'Ürün kopyalanırken hata oluştu.', 500);
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
    $product['cost'] = (float)($product['cost'] ?? 0);

    // Get sales statistics
    $salesStats = $menuObj->getProductSalesStats($productId, 30); // Last 30 days

    apiResponse([
        'product' => $product,
        'sales_stats' => $salesStats
    ], true, 'Ürün detayları getirildi.');
}

/**
 * Handle bulk update prices
 */
function handleBulkUpdatePrices($menuObj) {
    global $userId;

    // Permission check - only admin can bulk update
    if (!hasPermission(1)) {
        apiResponse([], false, 'Bu işlem için yetkiniz bulunmuyor.', 403);
    }

    $updateType = Security::sanitizeInput($_POST['update_type'] ?? ''); // percentage, fixed
    $updateValue = (float)($_POST['update_value'] ?? 0);
    $categoryId = (int)($_POST['category_id'] ?? 0);
    $productIds = $_POST['product_ids'] ?? [];

    if (!in_array($updateType, ['percentage', 'fixed'])) {
        apiResponse([], false, 'Geçersiz güncelleme türü.', 400);
    }

    if ($updateValue == 0) {
        apiResponse([], false, 'Güncelleme değeri sıfır olamaz.', 400);
    }

    if ($updateType === 'percentage' && ($updateValue < -90 || $updateValue > 1000)) {
        apiResponse([], false, 'Yüzde değeri -90 ile 1000 arasında olmalıdır.', 400);
    }

    $result = $menuObj->bulkUpdatePrices($updateType, $updateValue, $categoryId, $productIds);

    if ($result) {
        logActivity('BULK_PRICE_UPDATE', [
            'update_type' => $updateType,
            'update_value' => $updateValue,
            'category_id' => $categoryId,
            'affected_products' => count($productIds),
            'user_id' => $userId
        ]);

        apiResponse(['updated_count' => $result], true, $result . ' ürünün fiyatı güncellendi.');
    } else {
        apiResponse([], false, 'Toplu fiyat güncellemesi yapılamadı.', 500);
    }
}

/**
 * Handle export products
 */
function handleExportProducts($menuObj) {
    global $userId;

    // Permission check - only admin and manager can export
    if (!hasPermission(2)) {
        apiResponse([], false, 'Bu işlem için yetkiniz bulunmuyor.', 403);
    }

    $format = Security::sanitizeInput($_GET['format'] ?? 'csv'); // csv, excel
    $categoryId = (int)($_GET['category_id'] ?? 0);

    if (!in_array($format, ['csv', 'excel'])) {
        apiResponse([], false, 'Geçersiz format.', 400);
    }

    $products = $menuObj->getProductsForExport($categoryId);

    if (empty($products)) {
        apiResponse([], false, 'Dışa aktarılacak ürün bulunamadı.', 404);
    }

    $filename = 'products_' . date('Y-m-d_H-i-s') . '.' . $format;

    if ($format === 'csv') {
        $csvContent = $menuObj->exportProductsToCSV($products);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($csvContent));

        echo $csvContent;
    } else {
        // Excel export would require additional library like PhpSpreadsheet
        apiResponse([], false, 'Excel formatı henüz desteklenmiyor.', 400);
    }

    logActivity('PRODUCTS_EXPORTED', [
        'format' => $format,
        'category_id' => $categoryId,
        'product_count' => count($products),
        'filename' => $filename,
        'user_id' => $userId
    ]);

    exit;
}

/**
 * Helper function for redirecting with messages
 */
function redirectWithMessage($url, $message, $type = 'info') {
    FlashMessage::set($message, $type);
    redirect($url);
}
