<?php
/**
 * ⚡ HIZLI KURULUM - TEK TIKLA SİSTEMİ HAZIRLA
 * Bu dosyayı bir kez çalıştırın!
 */

$host = "localhost";
$dbname = "restaurant";
$username = "root";
$password = "";

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Restaurant ERP - Hızlı Kurulum</title>
    <style>
        body { font-family: Arial; max-width: 900px; margin: 50px auto; padding: 20px; background: #f0f2f5; }
        .box { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin: 20px 0; }
        h1 { color: #2c3e50; text-align: center; }
        .success { background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #28a745; }
        .error { background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #dc3545; }
        .info { background: #d1ecf1; padding: 15px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #17a2b8; }
        .btn { display: inline-block; padding: 15px 30px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin: 10px 5px; font-weight: bold; }
        .btn-success { background: #28a745; }
        .btn-danger { background: #dc3545; }
        code { background: #f4f4f4; padding: 3px 8px; border-radius: 3px; font-family: monospace; }
        .step { background: #e9ecef; padding: 10px 15px; margin: 10px 0; border-radius: 5px; font-weight: bold; }
    </style>
</head>
<body>
    <h1>🍽️ Restaurant ERP - Hızlı Kurulum</h1>
    
    <div class="box">
        <?php
        try {
            echo "<div class='step'>ADIM 1: MySQL Bağlantısı</div>";
            $pdo = new PDO("mysql:host=$host", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            echo "<div class='success'>✅ MySQL bağlantısı başarılı</div>";
            
            echo "<div class='step'>ADIM 2: Veritabanı Oluştur</div>";
            $pdo->exec("DROP DATABASE IF EXISTS `$dbname`");
            $pdo->exec("CREATE DATABASE `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE `$dbname`");
            echo "<div class='success'>✅ Veritabanı hazır: <strong>$dbname</strong></div>";
            
            echo "<div class='step'>ADIM 3: Temel Tabloları Oluştur</div>";
            
            // Temel tabloları doğrudan oluştur
            $basicSQL = "
            CREATE TABLE IF NOT EXISTS users (
                id int(10) unsigned NOT NULL AUTO_INCREMENT,
                username varchar(70) NOT NULL,
                password varchar(255) NOT NULL,
                email varchar(255) NOT NULL,
                fullname varchar(120) DEFAULT NULL,
                user_position tinyint(4) DEFAULT NULL,
                PRIMARY KEY (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            
            INSERT IGNORE INTO users (id, username, password, email, fullname, user_position) VALUES
            (1, 'admin', 'd93a5def7511da3d0f2d171d9c344e91', 'admin@restaurant.com', 'Admin User', 1),
            (2, 'yetkili', 'd93a5def7511da3d0f2d171d9c344e91', 'yetkili@restaurant.com', 'Yetkili', 2),
            (3, 'garson', 'd93a5def7511da3d0f2d171d9c344e91', 'garson@restaurant.com', 'Garson', 3);
            
            CREATE TABLE IF NOT EXISTS tables (
                id int(11) unsigned NOT NULL AUTO_INCREMENT,
                name varchar(255) NOT NULL,
                status tinyint(4) NOT NULL DEFAULT 0,
                PRIMARY KEY (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            
            INSERT IGNORE INTO tables (id, name, status) VALUES
            (1,'1',0),(2,'2',0),(3,'3',0),(4,'4',0),(5,'5',0),
            (6,'6',0),(7,'7',0),(8,'8',0),(9,'9',0),(10,'10',0),
            (11,'11',0),(12,'12',0),(13,'13',0),(14,'14',0),(15,'15',0),
            (16,'16',0),(17,'17',0),(18,'18',0),(19,'19',0),(20,'20',0);
            
            CREATE TABLE IF NOT EXISTS product_categories (
                id int(11) unsigned NOT NULL AUTO_INCREMENT,
                name varchar(255) NOT NULL,
                PRIMARY KEY (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            
            INSERT IGNORE INTO product_categories (id, name) VALUES
            (1, 'Yiyecekler'), (2, 'İçecekler'), (3, 'Tatlılar');
            
            CREATE TABLE IF NOT EXISTS products (
                id int(11) NOT NULL AUTO_INCREMENT,
                category_id int(11) NOT NULL,
                name varchar(255) NOT NULL,
                price float(4,2) NOT NULL,
                PRIMARY KEY (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            
            INSERT IGNORE INTO products (id, category_id, name, price) VALUES
            (1, 1, 'Kuru Fasulye', 5.50),
            (2, 1, 'Pilav', 3.00),
            (3, 2, 'Su', 0.75),
            (4, 2, 'Kola', 2.50),
            (5, 3, 'Künefe', 12.50),
            (6, 2, 'Meyveli Soda', 2.00);
            
            CREATE TABLE IF NOT EXISTS orders (
                id int(11) unsigned NOT NULL AUTO_INCREMENT,
                table_id int(11) unsigned NOT NULL,
                total_amount float(6,2) DEFAULT NULL,
                created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                user_id tinyint(4) NOT NULL,
                status tinyint(4) NOT NULL DEFAULT 1,
                PRIMARY KEY (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            
            CREATE TABLE IF NOT EXISTS order_products (
                id int(11) unsigned NOT NULL AUTO_INCREMENT,
                order_id int(11) unsigned NOT NULL,
                product_id int(11) DEFAULT NULL,
                product_name varchar(255) NOT NULL,
                product_price float(4,2) NOT NULL,
                created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ";
            
            $statements = explode(';', $basicSQL);
            $count = 0;
            foreach ($statements as $statement) {
                $statement = trim($statement);
                if (!empty($statement)) {
                    try {
                        $pdo->exec($statement);
                        $count++;
                    } catch (PDOException $e) {
                        // Ignore
                    }
                }
            }
            echo "<div class='success'>✅ Temel tablolar oluşturuldu (6 tablo + örnek veriler)</div>";
            
            echo "<div class='step'>ADIM 4: Yeni Tabloları Ekle</div>";
            
            // schema.sql dosyasını çalıştır
            $schemaFile = __DIR__ . '/database/schema.sql';
            if (file_exists($schemaFile)) {
                $sql = file_get_contents($schemaFile);
                $statements = explode(';', $sql);
                $count = 0;
                foreach ($statements as $statement) {
                    $statement = trim($statement);
                    if (!empty($statement) && strpos($statement, '--') !== 0) {
                        try {
                            $pdo->exec($statement);
                            $count++;
                        } catch (PDOException $e) {
                            // Ignore duplicate/exists errors
                        }
                    }
                }
                echo "<div class='success'>✅ Yeni tablolar eklendi ($count işlem)</div>";
            }
            
            echo "<div class='step'>ADIM 5: Admin Şifresini Ayarla</div>";
            $hashedPassword = password_hash('123456', PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = 1");
            $stmt->execute([$hashedPassword]);
            echo "<div class='success'>✅ Admin şifresi güncellendi</div>";
            
            // Tabloları say
            $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
            echo "<div class='info'><strong>📊 Toplam Tablo:</strong> " . count($tables) . " adet</div>";
            
            echo "<hr>";
            echo "<div class='success' style='text-align: center;'>";
            echo "<h2>🎉 KURULUM TAMAMLANDI!</h2>";
            echo "<p><strong>Giriş Bilgileri:</strong></p>";
            echo "<p>Kullanıcı Adı: <code>admin</code></p>";
            echo "<p>Şifre: <code>123456</code></p>";
            echo "</div>";
            
            echo "<div style='text-align: center; margin-top: 30px;'>";
            echo "<a href='login.php' class='btn btn-success'>🔐 Giriş Yap</a>";
            echo "<a href='admin.php' class='btn'>📊 Admin Panel</a>";
            echo "</div>";
            
            echo "<hr>";
            echo "<div class='error' style='text-align: center;'>";
            echo "<strong>⚠️ GÜVENLİK:</strong> Bu dosyayı (<code>HIZLI_KURULUM.php</code>) şimdi silin!";
            echo "</div>";
            
        } catch (Exception $e) {
            echo "<div class='error'>";
            echo "<h2>❌ Hata!</h2>";
            echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
            echo "<hr>";
            echo "<h3>Çözüm:</h3>";
            echo "<ol>";
            echo "<li>XAMPP Control Panel'de <strong>MySQL</strong> servisinin çalıştığından emin olun</li>";
            echo "<li>MySQL şifresi varsa yukarıdaki <code>\$password</code> değişkenine ekleyin</li>";
            echo "<li>Sayfayı yenileyin</li>";
            echo "</ol>";
            echo "</div>";
        }
        ?>
    </div>
</body>
</html>
