<?php
/**
 * Security Class - CSRF Protection and Input Validation
 * Restaurant ERP System v2.0
 */

class Security
{
    /**
     * Generate CSRF Token
     */
    public static function generateCSRFToken()
    {
        if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time']) ||
            (time() - $_SESSION['csrf_token_time']) > 3600) { // 1 saat geçerlilik
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $_SESSION['csrf_token_time'] = time();
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Validate CSRF Token
     */
    public static function validateCSRFToken($token)
    {
        if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time'])) {
            return false;
        }

        // Token süresi kontrolü (1 saat)
        if ((time() - $_SESSION['csrf_token_time']) > 3600) {
            unset($_SESSION['csrf_token']);
            unset($_SESSION['csrf_token_time']);
            return false;
        }

        // Güvenli token karşılaştırması
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Generate CSRF Hidden Input HTML
     */
    public static function getCSRFInput()
    {
        $token = self::generateCSRFToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
    }

    /**
     * Validate Request with CSRF
     */
    public static function validateRequest()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? '';
            if (!self::validateCSRFToken($token)) {
                http_response_code(403);
                die(json_encode([
                    'error' => 'CSRF token validation failed',
                    'message' => 'Güvenlik doğrulaması başarısız. Sayfayı yenileyip tekrar deneyin.'
                ]));
            }
        }
    }

    /**
     * Sanitize Input Data
     */
    public static function sanitizeInput($data)
    {
        if (is_array($data)) {
            return array_map([self::class, 'sanitizeInput'], $data);
        }

        // HTML karakterlerini temizle
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');

        return $data;
    }

    /**
     * Validate Email
     */
    public static function validateEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate Phone Number (Turkish format)
     */
    public static function validatePhone($phone)
    {
        // Türkiye telefon numarası formatı
        $pattern = '/^(\+90|0)?\s*(\(\d{3}\)[\s-]*\d{3}[\s-]*\d{2}[\s-]*\d{2}|\d{3}[\s-]*\d{3}[\s-]*\d{2}[\s-]*\d{2}|\d{3}[\s-]*\d{3}[\s-]*\d{4})$/';
        return preg_match($pattern, $phone);
    }

    /**
     * Validate Turkish ID Number
     */
    public static function validateTCID($tc)
    {
        if (strlen($tc) != 11 || !ctype_digit($tc)) {
            return false;
        }

        $tc = str_split($tc);
        $checksum = ($tc[0] + $tc[2] + $tc[4] + $tc[6] + $tc[8]) * 7;
        $checksum -= ($tc[1] + $tc[3] + $tc[5] + $tc[7]);
        $checksum = $checksum % 10;

        if ($checksum != $tc[9]) {
            return false;
        }

        $checksum = array_sum(array_slice($tc, 0, 10)) % 10;
        return $checksum == $tc[10];
    }

    /**
     * Generate Secure Password
     */
    public static function generateSecurePassword($length = 12)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
        return substr(str_shuffle(str_repeat($chars, $length)), 0, $length);
    }

    /**
     * Check Password Strength
     */
    public static function checkPasswordStrength($password)
    {
        $score = 0;
        $feedback = [];

        // Uzunluk kontrolü
        if (strlen($password) >= 8) {
            $score += 1;
        } else {
            $feedback[] = 'En az 8 karakter olmalı';
        }

        // Büyük harf
        if (preg_match('/[A-Z]/', $password)) {
            $score += 1;
        } else {
            $feedback[] = 'En az bir büyük harf içermeli';
        }

        // Küçük harf
        if (preg_match('/[a-z]/', $password)) {
            $score += 1;
        } else {
            $feedback[] = 'En az bir küçük harf içermeli';
        }

        // Rakam
        if (preg_match('/[0-9]/', $password)) {
            $score += 1;
        } else {
            $feedback[] = 'En az bir rakam içermeli';
        }

        // Özel karakter
        if (preg_match('/[^A-Za-z0-9]/', $password)) {
            $score += 1;
        } else {
            $feedback[] = 'En az bir özel karakter içermeli (!@#$%^&*)';
        }

        $strength = ['Çok Zayıf', 'Zayıf', 'Orta', 'İyi', 'Güçlü'][$score] ?? 'Çok Zayıf';

        return [
            'score' => $score,
            'strength' => $strength,
            'feedback' => $feedback,
            'is_strong' => $score >= 4
        ];
    }

    /**
     * Rate Limiting - Simple Implementation
     */
    public static function checkRateLimit($action, $limit = 5, $window = 300) // 5 attempts in 5 minutes
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $key = "rate_limit_{$action}_{$ip}";

        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = [
                'attempts' => 1,
                'first_attempt' => time()
            ];
            return true;
        }

        $data = $_SESSION[$key];

        // Zaman penceresi geçtiyse sıfırla
        if ((time() - $data['first_attempt']) > $window) {
            $_SESSION[$key] = [
                'attempts' => 1,
                'first_attempt' => time()
            ];
            return true;
        }

        // Limit aşıldı mı?
        if ($data['attempts'] >= $limit) {
            $remaining_time = $window - (time() - $data['first_attempt']);
            http_response_code(429);
            die(json_encode([
                'error' => 'Rate limit exceeded',
                'message' => "Çok fazla deneme yaptınız. {$remaining_time} saniye sonra tekrar deneyin.",
                'retry_after' => $remaining_time
            ]));
        }

        // Deneme sayısını artır
        $_SESSION[$key]['attempts']++;
        return true;
    }

    /**
     * XSS Protection
     */
    public static function preventXSS($data)
    {
        if (is_array($data)) {
            return array_map([self::class, 'preventXSS'], $data);
        }

        // Tehlikeli HTML tag'leri ve script'leri temizle
        $data = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi', '', $data);
        $data = preg_replace('/<iframe\b[^<]*(?:(?!<\/iframe>)<[^<]*)*<\/iframe>/mi', '', $data);
        $data = preg_replace('/javascript:/i', '', $data);
        $data = preg_replace('/on\w+\s*=/i', '', $data);

        return htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Generate Secure Headers
     */
    public static function setSecurityHeaders()
    {
        // XSS Protection
        header('X-XSS-Protection: 1; mode=block');

        // Content Type Options
        header('X-Content-Type-Options: nosniff');

        // Frame Options
        header('X-Frame-Options: SAMEORIGIN');

        // Referrer Policy
        header('Referrer-Policy: strict-origin-when-cross-origin');

        // Content Security Policy (basic)
        header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self' https:;");
    }

    /**
     * Log Security Events
     */
    public static function logSecurityEvent($event, $details = [])
    {
        $log_entry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'event' => $event,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'user_id' => $_SESSION['user_session'] ?? null,
            'details' => $details
        ];

        $log_file = dirname(__DIR__) . '/logs/security_' . date('Y-m-d') . '.log';
        $log_dir = dirname($log_file);

        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0755, true);
        }

        error_log(json_encode($log_entry) . PHP_EOL, 3, $log_file);
    }

    /**
     * Session Security Setup
     */
    public static function secureSession()
    {
        // Session güvenlik ayarları
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
        ini_set('session.cookie_samesite', 'Strict');
        ini_set('session.use_strict_mode', 1);
        ini_set('session.gc_maxlifetime', 3600); // 1 saat

        // Session regeneration for security
        if (!isset($_SESSION['created'])) {
            $_SESSION['created'] = time();
        } else if (time() - $_SESSION['created'] > 1800) { // 30 dakikada bir regenerate
            session_regenerate_id(true);
            $_SESSION['created'] = time();
        }
    }
}
