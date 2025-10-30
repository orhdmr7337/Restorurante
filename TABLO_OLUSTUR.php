<?php
/**
 * ⚡ EKSİK TABLOLARI OLUŞTUR
 * materials ve suppliers tablolarını ekler
 */

$host = "localhost";
$dbname = "restaurant";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Tablo Oluşturma</title>";
    echo "<style>body{font-family:Arial;max-width:800px;margin:50px auto;padding:20px;background:#f5f5f5;}";
    echo ".box{background:white;padding:25px;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,0.1);margin:15px 0;}";
    echo ".success{background:#d4edda;padding:15px;border-radius:5px;margin:10px 0;border-left:4px solid #28a745;color:#155724;}";
    echo ".error{background:#f8d7da;padding:15px;border-radius:5px;margin:10px 0;border-left:4px solid #dc3545;color:#721c24;}";
    echo "h1{color:#2c3e50;text-align:center;} h2{color:#34495e;border-bottom:2px solid #3498db;padding-bottom:10px;}";
    echo ".btn{display:inline-block;padding:15px 30px;background:#007bff;color:white;text-decoration:none;border-radius:8px;font-weight:bold;margin:10px 5px;}";
    echo "</style></head><body>";
    
    echo "<div class='box'><h1>⚡ Eksik Tabloları Oluştur</h1>";
    
    echo "<h2>1️⃣ Materials Tablosu</h2>";
    
    $materialSQL = "CREATE TABLE IF NOT EXISTS `materials` (
      `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
      `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
      `unit` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
      `current_stock` decimal(10,2) DEFAULT 0.00,
      `min_stock` decimal(10,2) DEFAULT 0.00,
      `cost_price` decimal(10,2) DEFAULT 0.00,
      `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    
    $pdo->exec($materialSQL);
    echo "<div class='success'>✅ Materials tablosu oluşturuldu</div>";
    
    // Örnek veriler
    $pdo->exec("INSERT IGNORE INTO materials (id, name, unit, current_stock, min_stock, cost_price) VALUES
    (1, 'Un', 'kg', 50.00, 10.00, 5.50),
    (2, 'Şeker', 'kg', 30.00, 10.00, 8.00),
    (3, 'Yağ', 'lt', 20.00, 5.00, 45.00),
    (4, 'Domates', 'kg', 15.00, 10.00, 12.00),
    (5, 'Soğan', 'kg', 25.00, 10.00, 8.50)");
    
    echo "<div class='success'>✅ Örnek malzemeler eklendi (5 adet)</div>";
    
    echo "<h2>2️⃣ Suppliers Tablosu</h2>";
    
    $supplierSQL = "CREATE TABLE IF NOT EXISTS `suppliers` (
      `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
      `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
      `contact_person` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
      `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
      `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
      `address` text COLLATE utf8mb4_unicode_ci,
      `balance` decimal(10,2) DEFAULT 0.00,
      `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    
    $pdo->exec($supplierSQL);
    echo "<div class='success'>✅ Suppliers tablosu oluşturuldu</div>";
    
    // Örnek veriler
    $pdo->exec("INSERT IGNORE INTO suppliers (id, name, contact_person, phone, email, balance) VALUES
    (1, 'ABC Gıda Ltd.', 'Ahmet Yılmaz', '0532 123 4567', 'info@abcgida.com', 0.00),
    (2, 'XYZ Tedarik A.Ş.', 'Mehmet Demir', '0533 987 6543', 'satis@xyztedarik.com', 0.00),
    (3, 'Toptan Market', 'Ayşe Kaya', '0534 555 1234', 'info@toptanmarket.com', 0.00)");
    
    echo "<div class='success'>✅ Örnek tedarikçiler eklendi (3 adet)</div>";
    
    echo "<h2>3️⃣ Diğer Tablolar</h2>";
    
    // Purchases tablosu
    $purchaseSQL = "CREATE TABLE IF NOT EXISTS `purchases` (
      `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
      `supplier_id` int(11) unsigned NOT NULL,
      `invoice_number` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
      `total_amount` decimal(10,2) NOT NULL,
      `tax_amount` decimal(10,2) DEFAULT 0.00,
      `purchase_date` date NOT NULL,
      `payment_status` enum('unpaid','partial','paid') DEFAULT 'unpaid',
      `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    
    $pdo->exec($purchaseSQL);
    echo "<div class='success'>✅ Purchases tablosu oluşturuldu</div>";
    
    // Material movements
    $movementSQL = "CREATE TABLE IF NOT EXISTS `material_movements` (
      `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
      `material_id` int(11) unsigned NOT NULL,
      `type` enum('in','out') NOT NULL,
      `quantity` decimal(10,2) NOT NULL,
      `notes` text COLLATE utf8mb4_unicode_ci,
      `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    
    $pdo->exec($movementSQL);
    echo "<div class='success'>✅ Material_movements tablosu oluşturuldu</div>";
    
    echo "<hr>";
    echo "<div class='success' style='text-align:center;padding:30px;'>";
    echo "<h1 style='color:#28a745;font-size:48px;margin:0;'>🎉</h1>";
    echo "<h2 style='color:#28a745;'>Tablolar Oluşturuldu!</h2>";
    echo "<p style='font-size:18px;'>Tüm eksik tablolar başarıyla eklendi.</p>";
    echo "</div>";
    
    echo "<div style='text-align:center;margin-top:20px;'>";
    echo "<a href='SISTEM_KONTROL.php' class='btn'>🔄 Sistemi Tekrar Kontrol Et</a>";
    echo "<a href='login.php' class='btn' style='background:#28a745;'>🔐 Giriş Yap</a>";
    echo "</div>";
    
    echo "<div class='error' style='margin-top:30px;text-align:center;'>";
    echo "<strong>⚠️ Bu dosyayı silin:</strong> TABLO_OLUSTUR.php";
    echo "</div>";
    
    echo "</div></body></html>";
    
} catch (Exception $e) {
    echo "<div class='error'><h2>❌ Hata!</h2><p>" . htmlspecialchars($e->getMessage()) . "</p></div>";
}
?>
