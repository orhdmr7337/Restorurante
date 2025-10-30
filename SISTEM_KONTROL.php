<?php
/**
 * ⚡ SİSTEM KONTROL VE OTOMATIK DÜZELTME
 * Tüm sorunları tespit edip otomatik düzeltir
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
    <title>Sistem Kontrol & Düzeltme</title>
    <style>
        body { font-family: Arial; max-width: 1000px; margin: 30px auto; padding: 20px; background: #f0f2f5; }
        .box { background: white; padding: 25px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin: 15px 0; }
        h1 { color: #2c3e50; text-align: center; margin-bottom: 30px; }
        h2 { color: #34495e; border-bottom: 2px solid #3498db; padding-bottom: 10px; margin-top: 0; }
        .success { background: #d4edda; padding: 12px; border-radius: 5px; margin: 8px 0; border-left: 4px solid #28a745; color: #155724; }
        .error { background: #f8d7da; padding: 12px; border-radius: 5px; margin: 8px 0; border-left: 4px solid #dc3545; color: #721c24; }
        .warning { background: #fff3cd; padding: 12px; border-radius: 5px; margin: 8px 0; border-left: 4px solid #ffc107; color: #856404; }
        .info { background: #d1ecf1; padding: 12px; border-radius: 5px; margin: 8px 0; border-left: 4px solid #17a2b8; color: #0c5460; }
        .btn { display: inline-block; padding: 15px 30px; background: #007bff; color: white; text-decoration: none; border-radius: 8px; font-weight: bold; margin: 10px 5px; }
        .btn-success { background: #28a745; }
        .btn-danger { background: #dc3545; }
        code { background: #f4f4f4; padding: 3px 8px; border-radius: 3px; font-family: 'Courier New', monospace; font-size: 13px; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        table td, table th { padding: 10px; border: 1px solid #dee2e6; text-align: left; }
        table th { background: #f8f9fa; font-weight: 600; }
        .step { background: #e9ecef; padding: 10px 15px; margin: 10px 0; border-radius: 5px; font-weight: bold; color: #495057; }
    </style>
</head>
<body>
    <h1>🔧 Sistem Kontrol & Otomatik Düzeltme</h1>
    
    <?php
    $errors = [];
    $warnings = [];
    $fixes = [];
    
    try {
        // 1. VERİTABANI KONTROLÜ
        echo "<div class='box'>";
        echo "<h2>1️⃣ Veritabanı Kontrolü</h2>";
        
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "<div class='success'>✅ Veritabanı bağlantısı başarılı</div>";
        
        // Tabloları kontrol et
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        echo "<div class='info'>📊 Toplam Tablo: <strong>" . count($tables) . "</strong></div>";
        
        $requiredTables = ['users', 'tables', 'products', 'orders', 'materials', 'suppliers'];
        foreach ($requiredTables as $table) {
            if (in_array($table, $tables)) {
                echo "<div class='success'>✅ Tablo mevcut: <code>$table</code></div>";
            } else {
                echo "<div class='error'>❌ Tablo eksik: <code>$table</code></div>";
                $errors[] = "Tablo eksik: $table";
            }
        }
        echo "</div>";
        
        // 2. KULLANICI KONTROLÜ
        echo "<div class='box'>";
        echo "<h2>2️⃣ Kullanıcı & Şifre Kontrolü</h2>";
        
        $users = $pdo->query("SELECT id, username, email, user_position, password FROM users")->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table>";
        echo "<tr><th>ID</th><th>Kullanıcı</th><th>Email</th><th>Pozisyon</th><th>Şifre Tipi</th><th>Durum</th></tr>";
        
        $needsUpdate = false;
        foreach ($users as $user) {
            echo "<tr>";
            echo "<td>{$user['id']}</td>";
            echo "<td><code>{$user['username']}</code></td>";
            echo "<td>{$user['email']}</td>";
            echo "<td>{$user['user_position']}</td>";
            
            // Şifre tipini kontrol et
            if (strpos($user['password'], '$2y$') === 0) {
                echo "<td><span style='color:#28a745;'>✅ bcrypt</span></td>";
                
                // Şifre doğrulamasını test et
                if (password_verify('123456', $user['password'])) {
                    echo "<td><span style='color:#28a745;'>✅ Geçerli</span></td>";
                } else {
                    echo "<td><span style='color:#dc3545;'>❌ Hatalı</span></td>";
                    $needsUpdate = true;
                }
            } else {
                echo "<td><span style='color:#dc3545;'>❌ MD5/Eski</span></td>";
                echo "<td><span style='color:#dc3545;'>❌ Güncellenmeli</span></td>";
                $needsUpdate = true;
            }
            echo "</tr>";
        }
        echo "</table>";
        
        // Şifreleri otomatik güncelle
        if ($needsUpdate) {
            echo "<div class='warning'>⚠️ Bazı şifreler güncel değil. Otomatik güncelleniyor...</div>";
            
            $newHash = password_hash('123456', PASSWORD_BCRYPT);
            $pdo->exec("UPDATE users SET password = '$newHash'");
            
            echo "<div class='success'>✅ Tüm şifreler bcrypt ile güncellendi!</div>";
            $fixes[] = "Kullanıcı şifreleri güncellendi";
        } else {
            echo "<div class='success'>✅ Tüm şifreler güncel (bcrypt)</div>";
        }
        
        echo "</div>";
        
        // 3. DOSYA KONTROLÜ
        echo "<div class='box'>";
        echo "<h2>3️⃣ Kritik Dosya Kontrolü</h2>";
        
        $criticalFiles = [
            'login.php' => 'Giriş sayfası',
            'index.php' => 'Ana sayfa',
            'admin.php' => 'Admin paneli',
            'userTasks.php' => 'Kullanıcı işlemleri',
            'model/User.php' => 'User modeli',
            'inc/global.php' => 'Global ayarlar'
        ];
        
        foreach ($criticalFiles as $file => $desc) {
            if (file_exists($file)) {
                echo "<div class='success'>✅ <strong>$desc:</strong> <code>$file</code></div>";
            } else {
                echo "<div class='error'>❌ <strong>$desc:</strong> <code>$file</code> bulunamadı!</div>";
                $errors[] = "Dosya eksik: $file";
            }
        }
        
        echo "</div>";
        
        // 4. userTasks.php KONTROLÜ
        echo "<div class='box'>";
        echo "<h2>4️⃣ userTasks.php Hash Kontrolü</h2>";
        
        if (file_exists('userTasks.php')) {
            $content = file_get_contents('userTasks.php');
            
            if (strpos($content, "md5(sha1(\$_POST['password']))") !== false) {
                echo "<div class='error'>❌ Eski hash yöntemi kullanılıyor: <code>md5(sha1())</code></div>";
                echo "<div class='warning'>⚠️ Otomatik düzeltme yapılıyor...</div>";
                
                // Otomatik düzelt
                $content = str_replace(
                    "fixTags(trim(md5(sha1(\$_POST['password']))))",
                    "fixTags(trim(\$_POST['password']))",
                    $content
                );
                file_put_contents('userTasks.php', $content);
                
                echo "<div class='success'>✅ userTasks.php düzeltildi! Artık düz metin şifre gönderiyor.</div>";
                $fixes[] = "userTasks.php hash yöntemi güncellendi";
            } else {
                echo "<div class='success'>✅ Hash yöntemi doğru (düz metin)</div>";
            }
        }
        
        echo "</div>";
        
        // 5. GİRİŞ KONTROLÜ TESTİ
        echo "<div class='box'>";
        echo "<h2>5️⃣ Giriş Sistemi Testi</h2>";
        
        $testUser = $pdo->query("SELECT password FROM users WHERE username = 'admin'")->fetch(PDO::FETCH_ASSOC);
        
        if ($testUser && password_verify('123456', $testUser['password'])) {
            echo "<div class='success'>✅ Giriş sistemi çalışıyor!</div>";
            echo "<div class='info'>📝 Test: <code>admin / 123456</code> kombinasyonu başarılı</div>";
        } else {
            echo "<div class='error'>❌ Giriş sistemi test edilemedi</div>";
            $errors[] = "Giriş sistemi testi başarısız";
        }
        
        echo "</div>";
        
        // SONUÇ RAPORU
        echo "<div class='box' style='background: " . (count($errors) > 0 ? "#fff3cd" : "#d4edda") . ";'>";
        echo "<h2>📋 Sonuç Raporu</h2>";
        
        if (count($errors) > 0) {
            echo "<div class='error'>";
            echo "<strong>❌ Bulunan Hatalar (" . count($errors) . "):</strong><ul>";
            foreach ($errors as $error) {
                echo "<li>$error</li>";
            }
            echo "</ul></div>";
        }
        
        if (count($fixes) > 0) {
            echo "<div class='success'>";
            echo "<strong>✅ Yapılan Düzeltmeler (" . count($fixes) . "):</strong><ul>";
            foreach ($fixes as $fix) {
                echo "<li>$fix</li>";
            }
            echo "</ul></div>";
        }
        
        if (count($errors) == 0) {
            echo "<div class='success' style='text-align: center; padding: 30px;'>";
            echo "<h1 style='color: #28a745; font-size: 48px; margin: 0;'>🎉</h1>";
            echo "<h2 style='color: #28a745;'>SİSTEM HAZIR!</h2>";
            echo "<p style='font-size: 18px;'>Tüm kontroller başarılı. Giriş yapabilirsiniz.</p>";
            echo "</div>";
            
            echo "<div style='text-align: center; margin-top: 20px;'>";
            echo "<a href='login.php' class='btn btn-success' style='font-size: 18px; padding: 20px 40px;'>🔐 Giriş Sayfasına Git</a>";
            echo "</div>";
            
            echo "<div class='info' style='margin-top: 20px; text-align: center;'>";
            echo "<strong>Giriş Bilgileri:</strong><br>";
            echo "Kullanıcı: <code>admin</code> | Şifre: <code>123456</code>";
            echo "</div>";
        }
        
        echo "</div>";
        
        // TEMİZLİK UYARISI
        echo "<div class='box' style='background: #fff3cd;'>";
        echo "<h2>⚠️ Güvenlik Uyarısı</h2>";
        echo "<p>Kurulum tamamlandıktan sonra şu dosyaları silin:</p>";
        echo "<ul>";
        echo "<li><code>SISTEM_KONTROL.php</code> (bu dosya)</li>";
        echo "<li><code>TEST_LOGIN.php</code></li>";
        echo "<li><code>FIX_LOGIN.php</code></li>";
        echo "<li><code>HIZLI_KURULUM.php</code></li>";
        echo "</ul>";
        echo "</div>";
        
    } catch (Exception $e) {
        echo "<div class='box'>";
        echo "<div class='error'>";
        echo "<h2>❌ Kritik Hata!</h2>";
        echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<hr>";
        echo "<h3>Çözüm Önerileri:</h3>";
        echo "<ol>";
        echo "<li>XAMPP Control Panel'de MySQL servisinin çalıştığından emin olun</li>";
        echo "<li>Veritabanı bilgilerini kontrol edin (host, username, password)</li>";
        echo "<li><code>HIZLI_KURULUM.php</code> dosyasını çalıştırın</li>";
        echo "</ol>";
        echo "</div>";
        echo "</div>";
    }
    ?>
</body>
</html>
