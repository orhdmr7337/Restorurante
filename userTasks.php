<?php
/**
 * Secure User Tasks Handler
 * Restaurant ERP System v2.0
 * Enhanced Security Implementation
 */

require_once "inc/global.php";

// Güvenlik kontrolleri
Security::checkRateLimit('user_operations', 10, 300);

$usrObj = new User();

// POST veya GET işlemi varsa
if (isset($_POST['submit']) || isset($_GET['task'])) {

    // CSRF token kontrolü
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!Security::validateCSRFToken($_POST['csrf_token'] ?? '')) {
            apiResponse([], false, 'Güvenlik doğrulaması başarısız.', 403);
        }
    }

    $task = Security::sanitizeInput($_REQUEST['task'] ?? '');

    // Rate limiting for sensitive operations
    if (in_array($task, ['login', 'register', 'updatePassword'])) {
        Security::checkRateLimit('sensitive_auth', 5, 900); // 5 attempts in 15 minutes
    }

    switch ($task) {
        case "login":
            handleLogin($usrObj);
            break;

        case "register":
            handleRegister($usrObj);
            break;

        case "updateProfile":
            handleUpdateProfile($usrObj);
            break;

        case "updatePassword":
            handleUpdatePassword($usrObj);
            break;

        case "deleteUser":
            handleDeleteUser($usrObj);
            break;

        default:
            logActivity('INVALID_USER_TASK', ['task' => $task]);
            apiResponse([], false, 'Geçersiz işlem.', 400);
    }
}

/**
 * Handle user login
 */
function handleLogin($usrObj) {
    try {
        // Input validation
        $username = Security::sanitizeInput($_POST['username'] ?? '');
        $password = $_POST['password'] ?? ''; // Şifreyi olduğu gibi bırak

        if (empty($username) || empty($password)) {
            Security::logSecurityEvent('LOGIN_EMPTY_CREDENTIALS', ['username' => $username]);
            redirectWithMessage('login.php', 'Kullanıcı adı ve şifre gereklidir.', 'error');
        }

        // Brute force koruması
        $ip = getClientIP();
        $failed_attempts_key = "failed_login_attempts_{$ip}";
        $failed_attempts = $_SESSION[$failed_attempts_key] ?? 0;

        if ($failed_attempts >= LOGIN_ATTEMPTS_LIMIT) {
            $last_attempt_time = $_SESSION["last_failed_login_{$ip}"] ?? 0;
            if ((time() - $last_attempt_time) < LOGIN_BLOCK_TIME) {
                Security::logSecurityEvent('BRUTE_FORCE_BLOCKED', [
                    'username' => $username,
                    'ip' => $ip,
                    'attempts' => $failed_attempts
                ]);

                $remaining_time = LOGIN_BLOCK_TIME - (time() - $last_attempt_time);
                redirectWithMessage('login.php',
                    "Çok fazla başarısız giriş denemesi. {$remaining_time} saniye sonra tekrar deneyin.",
                    'error');
            } else {
                // Block süresi doldu, sıfırla
                unset($_SESSION[$failed_attempts_key]);
                unset($_SESSION["last_failed_login_{$ip}"]);
            }
        }

        // Login attempt
        $loginResult = $usrObj->login($username, $password, $username);

        if ($loginResult) {
            // Başarılı giriş
            unset($_SESSION[$failed_attempts_key]);
            unset($_SESSION["last_failed_login_{$ip}"]);
            unset($_SESSION['redirect_count']);

            $userInfo = $usrObj->getOneUser($_SESSION['user_session']);

            logActivity('USER_LOGIN', [
                'username' => $username,
                'user_id' => $_SESSION['user_session'],
                'role' => $userInfo['user_position']
            ]);

            Security::logSecurityEvent('SUCCESSFUL_LOGIN', [
                'username' => $username,
                'user_id' => $_SESSION['user_session']
            ]);

            // Rol bazlı yönlendirme
            if ($userInfo['user_position'] == 1) {
                redirect('admin.php');
            } else {
                redirect('index.php');
            }

        } else {
            // Başarısız giriş
            $_SESSION[$failed_attempts_key] = $failed_attempts + 1;
            $_SESSION["last_failed_login_{$ip}"] = time();

            Security::logSecurityEvent('FAILED_LOGIN', [
                'username' => $username,
                'ip' => $ip,
                'attempts' => $_SESSION[$failed_attempts_key]
            ]);

            $remaining_attempts = LOGIN_ATTEMPTS_LIMIT - $_SESSION[$failed_attempts_key];
            $message = $remaining_attempts > 0 ?
                "Hatalı giriş bilgileri. {$remaining_attempts} deneme hakkınız kaldı." :
                "Hesap geçici olarak kilitlendi.";

            redirectWithMessage('login.php', $message, 'error');
        }

    } catch (Exception $e) {
        Security::logSecurityEvent('LOGIN_EXCEPTION', [
            'message' => $e->getMessage(),
            'username' => $username ?? 'unknown'
        ]);

        redirectWithMessage('login.php', 'Giriş işlemi sırasında hata oluştu.', 'error');
    }
}

/**
 * Handle user registration
 */
function handleRegister($usrObj) {
    try {
        // Check if registration is allowed
        if (!hasPermission(1)) { // Only admin can register users
            Security::logSecurityEvent('UNAUTHORIZED_REGISTRATION_ATTEMPT');
            apiResponse([], false, 'Bu işlem için yetkiniz bulunmuyor.', 403);
        }

        // Input validation
        $username = Security::sanitizeInput($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $email = Security::sanitizeInput($_POST['email'] ?? '');
        $fullname = Security::sanitizeInput($_POST['name'] ?? '');
        $userPosition = (int)($_POST['userPosition'] ?? 3);
        $roleId = (int)($_POST['roleId'] ?? $userPosition);

        // Validation
        $errors = [];

        if (empty($username) || strlen($username) < 3) {
            $errors[] = 'Kullanıcı adı en az 3 karakter olmalıdır.';
        }

        if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $errors[] = 'Kullanıcı adı sadece harf, rakam ve alt çizgi içerebilir.';
        }

        if (!Security::validateEmail($email)) {
            $errors[] = 'Geçerli bir e-posta adresi giriniz.';
        }

        if (empty($fullname) || strlen($fullname) < 2) {
            $errors[] = 'Ad soyad en az 2 karakter olmalıdır.';
        }

        // Password strength check
        $passwordCheck = Security::checkPasswordStrength($password);
        if (!$passwordCheck['is_strong']) {
            $errors = array_merge($errors, $passwordCheck['feedback']);
        }

        if (!empty($errors)) {
            redirectWithMessage('userList.php', implode(' ', $errors), 'error');
        }

        // Attempt registration
        $result = $usrObj->registerUser($username, $password, $email, $fullname, $userPosition, $roleId);

        if ($result) {
            logActivity('USER_REGISTERED', [
                'new_username' => $username,
                'new_email' => $email,
                'role' => $userPosition
            ]);

            redirectWithMessage('userList.php', 'Kullanıcı başarıyla oluşturuldu.', 'success');
        } else {
            redirectWithMessage('userList.php', 'Kullanıcı adı veya e-posta zaten mevcut.', 'error');
        }

    } catch (Exception $e) {
        Security::logSecurityEvent('REGISTRATION_EXCEPTION', [
            'message' => $e->getMessage(),
            'username' => $username ?? 'unknown'
        ]);

        redirectWithMessage('userList.php', 'Kayıt işlemi sırasında hata oluştu.', 'error');
    }
}

/**
 * Handle profile update
 */
function handleUpdateProfile($usrObj) {
    try {
        // Authentication check
        if (!isset($_SESSION['user_session'])) {
            apiResponse([], false, 'Oturum bulunamadı.', 401);
        }

        $userId = $_SESSION['user_session'];
        $targetUserId = (int)($_POST['userId'] ?? $userId);

        // Permission check
        if ($targetUserId !== $userId && !hasPermission(1)) {
            Security::logSecurityEvent('UNAUTHORIZED_PROFILE_UPDATE', [
                'user_id' => $userId,
                'target_user_id' => $targetUserId
            ]);
            apiResponse([], false, 'Bu işlem için yetkiniz bulunmuyor.', 403);
        }

        // Input validation
        $username = Security::sanitizeInput($_POST['username'] ?? '');
        $email = Security::sanitizeInput($_POST['email'] ?? '');
        $fullname = Security::sanitizeInput($_POST['fullname'] ?? '');
        $userPosition = (int)($_POST['userPosition'] ?? 3);

        // Validation
        if (empty($username) || empty($email) || empty($fullname)) {
            apiResponse([], false, 'Tüm alanlar doldurulmalıdır.', 400);
        }

        if (!Security::validateEmail($email)) {
            apiResponse([], false, 'Geçerli bir e-posta adresi giriniz.', 400);
        }

        // Update user
        $result = $usrObj->userUpdate($targetUserId, $username, $email, $fullname, $userPosition);

        if ($result) {
            logActivity('USER_PROFILE_UPDATED', [
                'user_id' => $targetUserId,
                'updated_by' => $userId
            ]);

            redirectWithMessage('userList.php', 'Profil başarıyla güncellendi.', 'success');
        } else {
            redirectWithMessage('userList.php', 'Profil güncellenirken hata oluştu.', 'error');
        }

    } catch (Exception $e) {
        Security::logSecurityEvent('PROFILE_UPDATE_EXCEPTION', [
            'message' => $e->getMessage(),
            'user_id' => $_SESSION['user_session'] ?? 'unknown'
        ]);

        redirectWithMessage('userList.php', 'Güncelleme işlemi sırasında hata oluştu.', 'error');
    }
}

/**
 * Handle password update
 */
function handleUpdatePassword($usrObj) {
    try {
        // Authentication check
        if (!isset($_SESSION['user_session'])) {
            apiResponse([], false, 'Oturum bulunamadı.', 401);
        }

        $userId = $_SESSION['user_session'];
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // Validation
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            redirectWithMessage('settings.php', 'Tüm şifre alanları doldurulmalıdır.', 'error');
        }

        if ($newPassword !== $confirmPassword) {
            redirectWithMessage('settings.php', 'Yeni şifreler eşleşmiyor.', 'error');
        }

        // Current password verification
        $userInfo = $usrObj->getOneUser($userId);
        if (!password_verify($currentPassword, $userInfo['password'])) {
            Security::logSecurityEvent('INVALID_CURRENT_PASSWORD', ['user_id' => $userId]);
            redirectWithMessage('settings.php', 'Mevcut şifre hatalı.', 'error');
        }

        // New password strength check
        $passwordCheck = Security::checkPasswordStrength($newPassword);
        if (!$passwordCheck['is_strong']) {
            redirectWithMessage('settings.php', implode(' ', $passwordCheck['feedback']), 'error');
        }

        // Update password
        $result = $usrObj->updatePassword($userId, $newPassword);

        if ($result) {
            logActivity('PASSWORD_CHANGED', ['user_id' => $userId]);
            Security::logSecurityEvent('PASSWORD_CHANGED', ['user_id' => $userId]);

            redirectWithMessage('settings.php', 'Şifre başarıyla değiştirildi.', 'success');
        } else {
            redirectWithMessage('settings.php', 'Şifre değiştirilemedi.', 'error');
        }

    } catch (Exception $e) {
        Security::logSecurityEvent('PASSWORD_UPDATE_EXCEPTION', [
            'message' => $e->getMessage(),
            'user_id' => $_SESSION['user_session'] ?? 'unknown'
        ]);

        redirectWithMessage('settings.php', 'Şifre değiştirme sırasında hata oluştu.', 'error');
    }
}

/**
 * Handle user deletion
 */
function handleDeleteUser($usrObj) {
    try {
        // Authentication and permission check
        if (!isset($_SESSION['user_session']) || !hasPermission(1)) {
            Security::logSecurityEvent('UNAUTHORIZED_USER_DELETE_ATTEMPT');
            apiResponse([], false, 'Bu işlem için yetkiniz bulunmuyor.', 403);
        }

        $userId = (int)($_GET['id'] ?? 0);
        $currentUserId = $_SESSION['user_session'];

        if ($userId <= 0) {
            apiResponse([], false, 'Geçersiz kullanıcı ID.', 400);
        }

        if ($userId === $currentUserId) {
            apiResponse([], false, 'Kendi hesabınızı silemezsiniz.', 400);
        }

        // Get user info before deletion for logging
        $targetUser = $usrObj->getOneUser($userId);
        if (!$targetUser) {
            apiResponse([], false, 'Kullanıcı bulunamadı.', 404);
        }

        // Delete user
        $result = $usrObj->deleteUser($userId);

        if ($result) {
            logActivity('USER_DELETED', [
                'deleted_user_id' => $userId,
                'deleted_username' => $targetUser['username'],
                'deleted_by' => $currentUserId
            ]);

            Security::logSecurityEvent('USER_DELETED', [
                'deleted_user_id' => $userId,
                'deleted_username' => $targetUser['username']
            ]);

            redirectWithMessage('userList.php', 'Kullanıcı başarıyla silindi.', 'success');
        } else {
            redirectWithMessage('userList.php', 'Kullanıcı silinemedi.', 'error');
        }

    } catch (Exception $e) {
        Security::logSecurityEvent('USER_DELETE_EXCEPTION', [
            'message' => $e->getMessage(),
            'target_user_id' => $userId ?? 'unknown'
        ]);

        redirectWithMessage('userList.php', 'Silme işlemi sırasında hata oluştu.', 'error');
    }
}

/**
 * Helper function for redirecting with messages
 */
function redirectWithMessage($url, $message, $type = 'info') {
    $_SESSION['flash_message'] = [
        'message' => $message,
        'type' => $type,
        'timestamp' => time()
    ];
    redirect($url);
}
