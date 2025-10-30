<?php
/**
 * Global Configuration and Security Setup
 * Restaurant ERP System v2.0
 * Enhanced Security Implementation
 */

// Güvenli session başlatma
if (session_status() === PHP_SESSION_NONE) {
    // Session güvenlik ayarları
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
    ini_set('session.cookie_samesite', 'Strict');
    ini_set('session.use_strict_mode', 1);
    ini_set('session.gc_maxlifetime', 3600); // 1 saat
    ini_set('session.name', 'RESTAURANT_SESSION');

    session_start();

    // Session regeneration for security
    if (!isset($_SESSION['created'])) {
        $_SESSION['created'] = time();
    } else if (time() - $_SESSION['created'] > 1800) { // 30 dakikada bir regenerate
        session_regenerate_id(true);
        $_SESSION['created'] = time();
    }
}

// Required files
require_once "functions.php";
require_once "Security.php";

// Güvenlik header'larını ayarla
Security::setSecurityHeaders();

// Error handling configuration
if (!defined('DEBUG_MODE')) {
    define('DEBUG_MODE', false); // Production'da false yapın
}

if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('log_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
}

// Log klasörünü oluştur
$log_dir = dirname(__DIR__) . '/logs';
if (!is_dir($log_dir)) {
    mkdir($log_dir, 0755, true);
}
ini_set('error_log', $log_dir . '/error_' . date('Y-m-d') . '.log');

// Timezone ayarı
date_default_timezone_set('Europe/Istanbul');

// Site path configuration
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'];
$script_path = dirname($_SERVER['PHP_SELF']);
$sitePath = $protocol . $host . rtrim($script_path, '/') . '/';

// Database configuration constants
define('DB_HOST', 'localhost');
define('DB_NAME', 'restaurant');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Application constants
define('APP_NAME', 'Restaurant ERP');
define('APP_VERSION', '2.0.0');
define('UPLOAD_PATH', dirname(__DIR__) . '/uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx']);

// Currency and locale settings
define('CURRENCY_SYMBOL', '₺');
define('CURRENCY_CODE', 'TRY');
define('LOCALE', 'tr_TR.UTF-8');

// Security constants
define('PASSWORD_MIN_LENGTH', 8);
define('SESSION_TIMEOUT', 3600); // 1 saat
define('LOGIN_ATTEMPTS_LIMIT', 5);
define('LOGIN_BLOCK_TIME', 900); // 15 dakika

// Feature flags
define('ENABLE_API', true);
define('ENABLE_EXTERNAL_ORDERS', false);
define('ENABLE_QR_MENU', false);
define('ENABLE_NOTIFICATIONS', true);

// Upload klasörünü oluştur
if (!is_dir(UPLOAD_PATH)) {
    mkdir(UPLOAD_PATH, 0755, true);
    // Güvenlik için .htaccess ekle
    file_put_contents(UPLOAD_PATH . '.htaccess', "
Options -Indexes
<Files *.php>
    Deny from all
</Files>
<FilesMatch '\.(jpg|jpeg|png|gif|pdf|doc|docx)$'>
    Allow from all
</FilesMatch>
");
}

// Global exception handler
set_exception_handler(function($exception) {
    Security::logSecurityEvent('UNCAUGHT_EXCEPTION', [
        'message' => $exception->getMessage(),
        'file' => $exception->getFile(),
        'line' => $exception->getLine(),
        'trace' => $exception->getTraceAsString()
    ]);

    if (DEBUG_MODE) {
        echo '<div style="background: #f8d7da; color: #721c24; padding: 15px; border: 1px solid #f5c6cb; border-radius: 4px; margin: 10px;">';
        echo '<h3>Uncaught Exception:</h3>';
        echo '<p><strong>Message:</strong> ' . htmlspecialchars($exception->getMessage()) . '</p>';
        echo '<p><strong>File:</strong> ' . htmlspecialchars($exception->getFile()) . '</p>';
        echo '<p><strong>Line:</strong> ' . $exception->getLine() . '</p>';
        echo '</div>';
    } else {
        echo '<div style="background: #f8d7da; color: #721c24; padding: 15px; text-align: center;">';
        echo '<h3>Bir hata oluştu</h3>';
        echo '<p>Lütfen sistem yöneticisi ile iletişime geçin.</p>';
        echo '</div>';
    }
});

// Güvenlik kontrolleri
if (!empty($_POST) || !empty($_FILES)) {
    // CSRF token kontrolü (API istekleri hariç)
    $skip_csrf = ['/api/', 'ajax.php', 'upload.php'];
    $request_uri = $_SERVER['REQUEST_URI'] ?? '';
    $skip_csrf_check = false;

    foreach ($skip_csrf as $skip_path) {
        if (strpos($request_uri, $skip_path) !== false) {
            $skip_csrf_check = true;
            break;
        }
    }

    if (!$skip_csrf_check) {
        Security::validateRequest();
    }
}

// Input sanitization for all requests
if (!empty($_GET)) {
    $_GET = Security::sanitizeInput($_GET);
}

if (!empty($_POST)) {
    $_POST = Security::sanitizeInput($_POST);
}

// Rate limiting for sensitive operations
$sensitive_pages = ['login.php', 'userTasks.php'];
$current_page = basename($_SERVER['PHP_SELF']);

if (in_array($current_page, $sensitive_pages)) {
    Security::checkRateLimit('sensitive_operation', 10, 300); // 10 attempts in 5 minutes
}

// Session timeout check
if (isset($_SESSION['user_session'])) {
    $last_activity = $_SESSION['last_activity'] ?? time();
    if ((time() - $last_activity) > SESSION_TIMEOUT) {
        session_destroy();
        header('Location: login.php?timeout=1');
        exit();
    }
    $_SESSION['last_activity'] = time();
}

// HTTPS redirect (production için)
if (!DEBUG_MODE && !isset($_SERVER['HTTPS']) && $_SERVER['SERVER_PORT'] != 443) {
    $redirect_url = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header('Location: ' . $redirect_url, true, 301);
    exit();
}

// Content type header
header('Content-Type: text/html; charset=UTF-8');

// PHP Version check
if (version_compare(PHP_VERSION, '7.4.0', '<')) {
    die('Bu uygulama PHP 7.4 veya üstü gerektirir. Mevcut sürüm: ' . PHP_VERSION);
}

// Memory limit check
$memory_limit = ini_get('memory_limit');
if ($memory_limit !== '-1') {
    $memory_limit_bytes = return_bytes($memory_limit);
    if ($memory_limit_bytes < 128 * 1024 * 1024) { // 128MB
        ini_set('memory_limit', '128M');
    }
}

/**
 * Convert memory limit string to bytes
 */
function return_bytes($val) {
    $val = trim($val);
    $last = strtolower($val[strlen($val)-1]);
    $val = (int)$val;
    switch($last) {
        case 'g':
            $val *= 1024;
        case 'm':
            $val *= 1024;
        case 'k':
            $val *= 1024;
    }
    return $val;
}
