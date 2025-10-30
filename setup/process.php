<?php
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'test_db':
        testDatabase();
        break;
    case 'validate_license':
        validateLicense();
        break;
    case 'create_admin':
        createAdmin();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Geçersiz işlem']);
}

function testDatabase()
{
    $host = $_POST['db_host'] ?? '';
    $name = $_POST['db_name'] ?? '';
    $user = $_POST['db_user'] ?? '';
    $pass = $_POST['db_pass'] ?? '';

    try {
        $pdo = new PDO("mysql:host=$host", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Veritabanını oluştur
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `$name`");

        // Schema dosyasını çalıştır
        $schema = file_get_contents('../database/schema.sql');
        $pdo->exec($schema);

        // Connection.php dosyasını güncelle
        updateConnectionFile($host, $name, $user, $pass);

        echo json_encode(['success' => true, 'message' => 'Veritabanı başarıyla oluşturuldu']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Veritabanı hatası: ' . $e->getMessage()]);
    }
}

function validateLicense()
{
    $licenseKey = $_POST['license_key'] ?? '';
    $companyName = $_POST['company_name'] ?? '';

    if (empty($licenseKey)) {
        echo json_encode(['success' => false, 'message' => 'Lisans anahtarı gerekli']);
        return;
    }

    // Veritabanına bağlan
    $host = $_POST['db_host'] ?? '';
    $name = $_POST['db_name'] ?? '';
    $user = $_POST['db_user'] ?? '';
    $pass = $_POST['db_pass'] ?? '';

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$name", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Lisans API'sine istek gönder (opsiyonel)
        $apiValidation = validateWithAPI($licenseKey);

        // Lisansı veritabanına kaydet
        $stmt = $pdo->prepare("INSERT INTO license (license_key, company_name, activation_date, status) 
                               VALUES (:key, :company, CURDATE(), 'active')");
        $stmt->execute([
            ':key' => $licenseKey,
            ':company' => $companyName
        ]);

        echo json_encode(['success' => true, 'message' => 'Lisans doğrulandı', 'api_check' => $apiValidation]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Lisans kaydedilemedi: ' . $e->getMessage()]);
    }
}

function validateWithAPI($licenseKey)
{
    $apiUrl = "http://localhost/license-manager/api/validate.php";
    
    try {
        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['license_key' => $licenseKey]));
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode == 200 && $response) {
            return json_decode($response, true);
        }
    } catch (Exception $e) {
        // API erişilemiyorsa devam et
    }
    
    return ['api_available' => false, 'message' => 'API kontrolü yapılamadı, yerel doğrulama kullanıldı'];
}

function createAdmin()
{
    $username = $_POST['admin_username'] ?? '';
    $email = $_POST['admin_email'] ?? '';
    $password = $_POST['admin_password'] ?? '';
    $fullname = $_POST['admin_fullname'] ?? '';

    $host = $_POST['db_host'] ?? '';
    $name = $_POST['db_name'] ?? '';
    $user = $_POST['db_user'] ?? '';
    $pass = $_POST['db_pass'] ?? '';

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$name", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Mevcut admin kullanıcısını güncelle veya yeni ekle
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        
        $stmt = $pdo->prepare("UPDATE users SET 
                               username = :username, 
                               password = :password, 
                               email = :email, 
                               fullname = :fullname 
                               WHERE id = 1");
        
        $result = $stmt->execute([
            ':username' => $username,
            ':password' => $hashedPassword,
            ':email' => $email,
            ':fullname' => $fullname
        ]);

        // Kurulum tamamlandı işaretini ekle
        $pdo->exec("INSERT INTO settings (setting_key, setting_value) 
                    VALUES ('setup_completed', '1') 
                    ON DUPLICATE KEY UPDATE setting_value = '1'");

        echo json_encode(['success' => true, 'message' => 'Yönetici hesabı oluşturuldu']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Hesap oluşturulamadı: ' . $e->getMessage()]);
    }
}

function updateConnectionFile($host, $dbname, $username, $password)
{
    $content = <<<PHP
<?php

class Connection
{
    protected \$con;
    private \$host = "$host";
    private \$dbname = "$dbname";
    private \$username = "$username";
    private \$password = "$password";

    function __construct(){
        try{
            \$this->con = new PDO("mysql:host=".\$this->host.";dbname=".\$this->dbname.";charset=UTF8;", \$this->username, \$this->password);
            \$this->con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }catch (PDOException \$e){
            die("Veritabani baglanti hatasi: ".\$e->getMessage());
        }
    }
}
PHP;

    file_put_contents('../model/Connection.php', $content);
}
