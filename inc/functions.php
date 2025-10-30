<?php
/**
 * Enhanced Functions Library
 * Restaurant ERP System v2.0
 * Security and Utility Functions
 */

// Auto-loading sınıfları
spl_autoload_register(function ($className) {
    // API klasöründen çağrılıyorsa
    if (file_exists("../model/" . $className . ".php")) {
        require_once "../model/" . $className . ".php";
    }
    // Ana klasörden çağrılıyorsa
    elseif (file_exists("model/" . $className . ".php")) {
        require_once "model/" . $className . ".php";
    }
    // Inc klasöründen çağrılıyorsa
    elseif (file_exists("inc/" . $className . ".php")) {
        require_once "inc/" . $className . ".php";
    }
});

/**
 * Debug function - Variable dump and die
 */
function dd($var, $label = null)
{
    if (!DEBUG_MODE) {
        Security::logSecurityEvent('DEBUG_ATTEMPT', ['variable' => gettype($var)]);
        return;
    }

    echo "<div style='background: #f8f9fa; border: 1px solid #dee2e6; padding: 15px; margin: 10px; border-radius: 5px;'>";
    if ($label) {
        echo "<h4 style='color: #495057; margin-top: 0;'>" . htmlspecialchars($label) . "</h4>";
    }
    echo "<pre style='background: white; padding: 10px; border-radius: 3px; overflow-x: auto;'>";
    var_dump($var);
    echo "</pre></div>";
    die();
}

/**
 * Enhanced tag fixing with XSS protection
 */
function fixTags($text, $allow_html = false)
{
    if (empty($text)) return '';

    $text = trim($text);

    if (!$allow_html) {
        return htmlspecialchars($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    // Güvenli HTML tag'leri için
    $allowed_tags = '<p><br><strong><b><em><i><u><ul><ol><li><a>';
    $text = strip_tags($text, $allowed_tags);

    // XSS koruması
    $text = preg_replace('/javascript:/i', '', $text);
    $text = preg_replace('/on\w+\s*=/i', '', $text);

    return $text;
}

/**
 * Safe redirect function
 */
function redirect($url, $permanent = false)
{
    // URL validation
    if (!filter_var($url, FILTER_VALIDATE_URL) && !preg_match('/^[a-zA-Z0-9\/_\-\.?&=]+$/', $url)) {
        Security::logSecurityEvent('INVALID_REDIRECT', ['url' => $url]);
        $url = 'index.php';
    }

    // Prevent redirect loops
    if (isset($_SESSION['redirect_count'])) {
        $_SESSION['redirect_count']++;
        if ($_SESSION['redirect_count'] > 5) {
            Security::logSecurityEvent('REDIRECT_LOOP', ['url' => $url]);
            die('Redirect loop detected');
        }
    } else {
        $_SESSION['redirect_count'] = 1;
    }

    // Clean output buffer
    if (ob_get_level()) {
        ob_end_clean();
    }

    $code = $permanent ? 301 : 302;
    header("Location: $url", true, $code);
    exit();
}

/**
 * Generate secure random string
 */
function generateSecureToken($length = 32)
{
    if (function_exists('random_bytes')) {
        return bin2hex(random_bytes($length / 2));
    } elseif (function_exists('openssl_random_pseudo_bytes')) {
        return bin2hex(openssl_random_pseudo_bytes($length / 2));
    } else {
        // Fallback for older PHP versions
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $token = '';
        for ($i = 0; $i < $length; $i++) {
            $token .= $chars[mt_rand(0, strlen($chars) - 1)];
        }
        return $token;
    }
}

/**
 * Format currency
 */
function formatCurrency($amount, $currency = CURRENCY_SYMBOL)
{
    if (!is_numeric($amount)) return '0.00 ' . $currency;
    return number_format($amount, 2, ',', '.') . ' ' . $currency;
}

/**
 * Format date for Turkish locale
 */
function formatDate($date, $format = 'd.m.Y H:i')
{
    if (empty($date)) return '';

    if (!($date instanceof DateTime)) {
        $date = new DateTime($date);
    }

    return $date->format($format);
}

/**
 * Get time ago string in Turkish
 */
function timeAgo($datetime)
{
    if (empty($datetime)) return '';

    $now = new DateTime();
    $past = new DateTime($datetime);
    $diff = $now->diff($past);

    if ($diff->y > 0) return $diff->y . ' yıl önce';
    if ($diff->m > 0) return $diff->m . ' ay önce';
    if ($diff->d > 0) return $diff->d . ' gün önce';
    if ($diff->h > 0) return $diff->h . ' saat önce';
    if ($diff->i > 0) return $diff->i . ' dakika önce';

    return 'Az önce';
}

/**
 * File upload with security checks
 */
function uploadFile($file, $target_dir = 'uploads/', $allowed_types = null)
{
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'Dosya yükleme hatası'];
    }

    // Allowed file types
    if ($allowed_types === null) {
        $allowed_types = ALLOWED_EXTENSIONS;
    }

    // File size check
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'error' => 'Dosya boyutu çok büyük'];
    }

    // File extension check
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($file_extension, $allowed_types)) {
        return ['success' => false, 'error' => 'Desteklenmeyen dosya türü'];
    }

    // MIME type check
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $allowed_mimes = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'pdf' => 'application/pdf',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ];

    if (!isset($allowed_mimes[$file_extension]) || $mime_type !== $allowed_mimes[$file_extension]) {
        return ['success' => false, 'error' => 'Güvenlik: Dosya türü uyumsuz'];
    }

    // Generate secure filename
    $filename = generateSecureToken(16) . '.' . $file_extension;
    $target_path = rtrim($target_dir, '/') . '/' . $filename;

    // Create directory if not exists
    if (!is_dir(dirname($target_path))) {
        mkdir(dirname($target_path), 0755, true);
    }

    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        // Set file permissions
        chmod($target_path, 0644);

        return [
            'success' => true,
            'filename' => $filename,
            'path' => $target_path,
            'original_name' => $file['name']
        ];
    }

    return ['success' => false, 'error' => 'Dosya kaydedilemedi'];
}

/**
 * Image resize function
 */
function resizeImage($source, $destination, $width, $height, $quality = 80)
{
    if (!extension_loaded('gd')) {
        return false;
    }

    $info = getimagesize($source);
    if (!$info) return false;

    $mime = $info['mime'];

    switch ($mime) {
        case 'image/jpeg':
            $image = imagecreatefromjpeg($source);
            break;
        case 'image/png':
            $image = imagecreatefrompng($source);
            break;
        case 'image/gif':
            $image = imagecreatefromgif($source);
            break;
        default:
            return false;
    }

    $original_width = imagesx($image);
    $original_height = imagesy($image);

    // Calculate new dimensions
    $ratio = min($width / $original_width, $height / $original_height);
    $new_width = $original_width * $ratio;
    $new_height = $original_height * $ratio;

    // Create new image
    $new_image = imagecreatetruecolor($new_width, $new_height);

    // Preserve transparency for PNG and GIF
    if ($mime == 'image/png' || $mime == 'image/gif') {
        imagecolortransparent($new_image, imagecolorallocatealpha($new_image, 0, 0, 0, 127));
        imagealphablending($new_image, false);
        imagesavealpha($new_image, true);
    }

    // Resize
    imagecopyresampled($new_image, $image, 0, 0, 0, 0, $new_width, $new_height, $original_width, $original_height);

    // Save
    switch ($mime) {
        case 'image/jpeg':
            imagejpeg($new_image, $destination, $quality);
            break;
        case 'image/png':
            imagepng($new_image, $destination);
            break;
        case 'image/gif':
            imagegif($new_image, $destination);
            break;
    }

    imagedestroy($image);
    imagedestroy($new_image);

    return true;
}

/**
 * Generate QR Code (simple implementation)
 */
function generateQRCode($data, $size = 200)
{
    // This is a placeholder - you'd integrate with a QR library like endroid/qr-code
    return "https://api.qrserver.com/v1/create-qr-code/?size={$size}x{$size}&data=" . urlencode($data);
}

/**
 * Send notification (email, SMS, push)
 */
function sendNotification($type, $recipient, $subject, $message, $data = [])
{
    if (!ENABLE_NOTIFICATIONS) {
        return false;
    }

    switch ($type) {
        case 'email':
            return sendEmail($recipient, $subject, $message, $data);
        case 'sms':
            return sendSMS($recipient, $message, $data);
        case 'push':
            return sendPushNotification($recipient, $subject, $message, $data);
        default:
            return false;
    }
}

/**
 * Simple email function (can be enhanced with PHPMailer)
 */
function sendEmail($to, $subject, $message, $headers = [])
{
    $default_headers = [
        'From: ' . APP_NAME . ' <noreply@restaurant.com>',
        'Reply-To: noreply@restaurant.com',
        'Content-Type: text/html; charset=UTF-8',
        'X-Mailer: PHP/' . phpversion()
    ];

    $headers = array_merge($default_headers, $headers);
    $header_string = implode("\r\n", $headers);

    return mail($to, $subject, $message, $header_string);
}

/**
 * Log activity
 */
function logActivity($action, $details = [], $user_id = null)
{
    global $sitePath;

    if ($user_id === null && isset($_SESSION['user_session'])) {
        $user_id = $_SESSION['user_session'];
    }

    $log_entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'user_id' => $user_id,
        'action' => $action,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        'url' => $_SERVER['REQUEST_URI'] ?? '',
        'details' => $details
    ];

    $log_file = dirname(__DIR__) . '/logs/activity_' . date('Y-m-d') . '.log';
    $log_dir = dirname($log_file);

    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }

    error_log(json_encode($log_entry) . PHP_EOL, 3, $log_file);
}

/**
 * API Response helper
 */
function apiResponse($data = [], $success = true, $message = '', $code = 200)
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');

    $response = [
        'success' => $success,
        'message' => $message,
        'data' => $data,
        'timestamp' => date('Y-m-d H:i:s'),
        'version' => APP_VERSION
    ];

    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit();
}

/**
 * Check if user has permission
 */
function hasPermission($required_role, $user_role = null)
{
    if ($user_role === null) {
        $user_role = $_SESSION['user_role'] ?? 3; // Default to lowest role
    }

    // Role hierarchy: 1 = Admin, 2 = Manager, 3 = Staff
    $role_hierarchy = [1 => 3, 2 => 2, 3 => 1]; // Higher number = more permissions

    return ($role_hierarchy[$user_role] ?? 0) >= ($role_hierarchy[$required_role] ?? 0);
}

/**
 * Pagination helper
 */
function paginate($total_records, $records_per_page = 20, $current_page = 1)
{
    $total_pages = ceil($total_records / $records_per_page);
    $offset = ($current_page - 1) * $records_per_page;

    return [
        'total_records' => $total_records,
        'total_pages' => $total_pages,
        'current_page' => $current_page,
        'records_per_page' => $records_per_page,
        'offset' => $offset,
        'has_prev' => $current_page > 1,
        'has_next' => $current_page < $total_pages,
        'prev_page' => $current_page - 1,
        'next_page' => $current_page + 1
    ];
}

/**
 * Database backup function
 */
function backupDatabase($filename = null)
{
    if (!hasPermission(1)) { // Only admin can backup
        return false;
    }

    if ($filename === null) {
        $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
    }

    $backup_dir = dirname(__DIR__) . '/backups/';
    if (!is_dir($backup_dir)) {
        mkdir($backup_dir, 0755, true);
    }

    $backup_file = $backup_dir . $filename;

    $command = sprintf(
        'mysqldump -h%s -u%s -p%s %s > %s 2>&1',
        DB_HOST,
        DB_USER,
        DB_PASS,
        DB_NAME,
        escapeshellarg($backup_file)
    );

    $output = [];
    $return_code = 0;
    exec($command, $output, $return_code);

    if ($return_code === 0 && file_exists($backup_file)) {
        logActivity('DATABASE_BACKUP', ['filename' => $filename]);
        return $backup_file;
    }

    return false;
}

/**
 * Cache helper functions
 */
function cacheSet($key, $value, $ttl = 3600)
{
    $cache_dir = dirname(__DIR__) . '/cache/';
    if (!is_dir($cache_dir)) {
        mkdir($cache_dir, 0755, true);
    }

    $cache_file = $cache_dir . md5($key) . '.cache';
    $cache_data = [
        'expires' => time() + $ttl,
        'data' => $value
    ];

    return file_put_contents($cache_file, serialize($cache_data)) !== false;
}

function cacheGet($key)
{
    $cache_dir = dirname(__DIR__) . '/cache/';
    $cache_file = $cache_dir . md5($key) . '.cache';

    if (!file_exists($cache_file)) {
        return null;
    }

    $cache_data = unserialize(file_get_contents($cache_file));

    if (!$cache_data || $cache_data['expires'] < time()) {
        unlink($cache_file);
        return null;
    }

    return $cache_data['data'];
}

/**
 * Mobile detection
 */
function isMobile()
{
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    return preg_match('/Mobile|Android|iPhone|iPad/', $user_agent);
}

/**
 * Get client IP address
 */
function getClientIP()
{
    $ip_keys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];

    foreach ($ip_keys as $key) {
        if (array_key_exists($key, $_SERVER)) {
            foreach (explode(',', $_SERVER[$key]) as $ip) {
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP,
                    FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
    }

    return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
}
