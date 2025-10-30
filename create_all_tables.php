<?php
$pdo = new PDO('mysql:host=localhost;dbname=restaurant', 'root', '');

echo "<h2>Tablolar oluşturuluyor...</h2>";

// 1. Recipes (Reçeteler) - Foreign key olmadan
$pdo->exec("CREATE TABLE IF NOT EXISTS recipes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    material_id INT NOT NULL,
    quantity DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");
echo "✅ recipes tablosu oluşturuldu<br>";

// 2. Stock Movements (Stok Hareketleri) - Foreign key olmadan
$pdo->exec("CREATE TABLE IF NOT EXISTS stock_movements (
    id INT PRIMARY KEY AUTO_INCREMENT,
    material_id INT NOT NULL,
    type ENUM('in','out') NOT NULL,
    quantity DECIMAL(10,2) NOT NULL,
    reference_type VARCHAR(50),
    reference_id INT,
    user_id INT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");
echo "✅ stock_movements tablosu oluşturuldu<br>";

// 3. Purchase Items (Satın Alma Detayları) - Foreign key olmadan
$pdo->exec("CREATE TABLE IF NOT EXISTS purchase_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    purchase_id INT NOT NULL,
    material_id INT NOT NULL,
    quantity DECIMAL(10,2),
    unit_price DECIMAL(10,2),
    total_price DECIMAL(10,2)
)");
echo "✅ purchase_items tablosu oluşturuldu<br>";

// 4. Account Transactions (Cari Hareketler) - Foreign key olmadan
$pdo->exec("CREATE TABLE IF NOT EXISTS account_transactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    account_id INT NOT NULL,
    type ENUM('debit','credit') NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    description TEXT,
    reference_type VARCHAR(50),
    reference_id INT,
    transaction_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");
echo "✅ account_transactions tablosu oluşturuldu<br>";

// 5. Debt Payments (Borç Ödemeleri) - Foreign key olmadan
$pdo->exec("CREATE TABLE IF NOT EXISTS debt_payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    account_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('cash','card','bank','other'),
    payment_date DATE,
    notes TEXT,
    user_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");
echo "✅ debt_payments tablosu oluşturuldu<br>";

// 6. Cash Transactions (Kasa Hareketleri)
$pdo->exec("CREATE TABLE IF NOT EXISTS cash_transactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    type ENUM('in','out') NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    description TEXT,
    reference_type VARCHAR(50),
    reference_id INT,
    transaction_date DATE,
    user_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");
echo "✅ cash_transactions tablosu oluşturuldu<br>";

// 7. Bank Transactions (Banka Hareketleri)
$pdo->exec("CREATE TABLE IF NOT EXISTS bank_transactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    account_name VARCHAR(255),
    type ENUM('in','out') NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    description TEXT,
    reference_number VARCHAR(100),
    transaction_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");
echo "✅ bank_transactions tablosu oluşturuldu<br>";

// 8. Salary Payments (Maaş Ödemeleri) - Foreign key olmadan
$pdo->exec("CREATE TABLE IF NOT EXISTS salary_payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_date DATE,
    period_month INT,
    period_year INT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");
echo "✅ salary_payments tablosu oluşturuldu<br>";

// 9. Shifts (Vardiyalar) - Foreign key olmadan
$pdo->exec("CREATE TABLE IF NOT EXISTS shifts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    shift_date DATE,
    start_time TIME,
    end_time TIME,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");
echo "✅ shifts tablosu oluşturuldu<br>";

// 10. Notifications (Bildirimler)
$pdo->exec("CREATE TABLE IF NOT EXISTS notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    type VARCHAR(50),
    title VARCHAR(255),
    message TEXT,
    user_id INT,
    is_read TINYINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");
echo "✅ notifications tablosu oluşturuldu<br>";

// 11. System Logs (Sistem Logları)
$pdo->exec("CREATE TABLE IF NOT EXISTS system_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    action VARCHAR(255),
    table_name VARCHAR(100),
    record_id INT,
    old_value TEXT,
    new_value TEXT,
    ip_address VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");
echo "✅ system_logs tablosu oluşturuldu<br>";

// 12. Settings (Ayarlar)
$pdo->exec("CREATE TABLE IF NOT EXISTS settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) UNIQUE,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)");
echo "✅ settings tablosu oluşturuldu<br>";

// Varsayılan ayarları ekle
$pdo->exec("INSERT IGNORE INTO settings (setting_key, setting_value) VALUES
    ('app_name', 'Restaurant ERP'),
    ('currency', 'TRY'),
    ('tax_rate', '18'),
    ('low_stock_alert', '1'),
    ('email_notifications', '1'),
    ('qr_order_enabled', '0'),
    ('debt_alert_days', '7')
");
echo "✅ Varsayılan ayarlar eklendi<br>";

echo "<br><h2 style='color: green;'>✅ TÜM TABLOLAR BAŞARIYLA OLUŞTURULDU!</h2>";
echo "<p><a href='admin.php'>Admin Paneline Git</a></p>";
echo "<p style='color: red;'><strong>ÖNEMLİ:</strong> Bu dosyayı şimdi silin!</p>";
?>
