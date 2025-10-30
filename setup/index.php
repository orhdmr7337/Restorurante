<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurant ERP - Kurulum Sihirbazı</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .container { background: white; border-radius: 20px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); max-width: 600px; width: 90%; padding: 40px; }
        h1 { color: #333; margin-bottom: 10px; font-size: 28px; }
        .subtitle { color: #666; margin-bottom: 30px; font-size: 14px; }
        .step { display: none; }
        .step.active { display: block; animation: fadeIn 0.5s; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; color: #333; font-weight: 500; }
        input, select { width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 14px; transition: border-color 0.3s; }
        input:focus, select:focus { outline: none; border-color: #667eea; }
        .btn { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 14px 30px; border: none; border-radius: 8px; cursor: pointer; font-size: 16px; font-weight: 600; width: 100%; margin-top: 10px; transition: transform 0.2s; }
        .btn:hover { transform: translateY(-2px); }
        .btn-secondary { background: #6c757d; }
        .progress { height: 6px; background: #e0e0e0; border-radius: 10px; margin-bottom: 30px; overflow: hidden; }
        .progress-bar { height: 100%; background: linear-gradient(90deg, #667eea 0%, #764ba2 100%); transition: width 0.3s; }
        .alert { padding: 12px; border-radius: 8px; margin-bottom: 20px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .feature-list { list-style: none; }
        .feature-list li { padding: 10px; margin: 5px 0; background: #f8f9fa; border-radius: 6px; }
        .feature-list li:before { content: "✓ "; color: #28a745; font-weight: bold; margin-right: 8px; }
        .logo { text-align: center; margin-bottom: 30px; }
        .logo-icon { font-size: 60px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <div class="logo-icon">🍽️</div>
            <h1>Restaurant ERP</h1>
            <p class="subtitle">Kurulum Sihirbazı</p>
        </div>

        <div class="progress">
            <div class="progress-bar" id="progressBar" style="width: 25%"></div>
        </div>

        <!-- ADIM 1: HOŞGELDİNİZ -->
        <div class="step active" id="step1">
            <h2>Hoş Geldiniz! 👋</h2>
            <p style="margin: 20px 0; color: #666;">Restaurant ERP sistemini kurmak üzeresiniz. Bu sihirbaz size adım adım rehberlik edecek.</p>
            
            <h3 style="margin-top: 30px; margin-bottom: 15px;">Sistem Özellikleri:</h3>
            <ul class="feature-list">
                <li>Sipariş & Masa Yönetimi</li>
                <li>Stok & Malzeme Takibi</li>
                <li>Tedarikçi & Alış Yönetimi</li>
                <li>Borç/Alacak Takibi</li>
                <li>Muhasebe & Finans</li>
                <li>Personel & Maaş Yönetimi</li>
                <li>Detaylı Raporlama</li>
                <li>API Entegrasyonları</li>
            </ul>

            <button class="btn" onclick="nextStep(2)">Kuruluma Başla →</button>
        </div>

        <!-- ADIM 2: VERİTABANI AYARLARI -->
        <div class="step" id="step2">
            <h2>Veritabanı Ayarları 🗄️</h2>
            <p style="margin: 20px 0; color: #666;">Lütfen MySQL veritabanı bilgilerinizi girin.</p>

            <div id="dbError" class="alert alert-error" style="display: none;"></div>

            <form id="dbForm">
                <div class="form-group">
                    <label>Sunucu Adresi</label>
                    <input type="text" name="db_host" value="localhost" required>
                </div>
                <div class="form-group">
                    <label>Veritabanı Adı</label>
                    <input type="text" name="db_name" value="restaurant" required>
                </div>
                <div class="form-group">
                    <label>Kullanıcı Adı</label>
                    <input type="text" name="db_user" value="root" required>
                </div>
                <div class="form-group">
                    <label>Şifre</label>
                    <input type="password" name="db_pass" value="">
                </div>
                <button type="button" class="btn" onclick="testDatabase()">Bağlantıyı Test Et & Devam →</button>
            </form>
        </div>

        <!-- ADIM 3: LİSANS ANAHTARI -->
        <div class="step" id="step3">
            <h2>Lisans Anahtarı 🔑</h2>
            <p style="margin: 20px 0; color: #666;">Satın aldığınız lisans anahtarını girin.</p>

            <div id="licenseError" class="alert alert-error" style="display: none;"></div>

            <form id="licenseForm">
                <div class="form-group">
                    <label>Lisans Anahtarı</label>
                    <input type="text" name="license_key" placeholder="XXXX-XXXX-XXXX-XXXX" required>
                </div>
                <div class="form-group">
                    <label>Firma Adı</label>
                    <input type="text" name="company_name" placeholder="Firma adınız" required>
                </div>
                <button type="button" class="btn" onclick="validateLicense()">Lisansı Doğrula & Devam →</button>
            </form>
        </div>

        <!-- ADIM 4: YÖNETİCİ HESABI -->
        <div class="step" id="step4">
            <h2>Yönetici Hesabı 👤</h2>
            <p style="margin: 20px 0; color: #666;">Sistem yöneticisi hesabını oluşturun.</p>

            <form id="adminForm">
                <div class="form-group">
                    <label>Kullanıcı Adı</label>
                    <input type="text" name="admin_username" value="admin" required>
                </div>
                <div class="form-group">
                    <label>E-posta</label>
                    <input type="email" name="admin_email" required>
                </div>
                <div class="form-group">
                    <label>Şifre</label>
                    <input type="password" name="admin_password" required minlength="6">
                </div>
                <div class="form-group">
                    <label>Şifre Tekrar</label>
                    <input type="password" name="admin_password_confirm" required minlength="6">
                </div>
                <div class="form-group">
                    <label>Ad Soyad</label>
                    <input type="text" name="admin_fullname" required>
                </div>
                <button type="button" class="btn" onclick="createAdmin()">Hesap Oluştur & Devam →</button>
            </form>
        </div>

        <!-- ADIM 5: TAMAMLANDI -->
        <div class="step" id="step5">
            <div style="text-align: center;">
                <div style="font-size: 80px; margin-bottom: 20px;">🎉</div>
                <h2>Kurulum Tamamlandı!</h2>
                <p style="margin: 20px 0; color: #666;">Restaurant ERP sistemi başarıyla kuruldu.</p>
                
                <div class="alert alert-success">
                    <strong>Önemli:</strong> Güvenlik nedeniyle setup klasörünü silin veya taşıyın.
                </div>

                <button class="btn" onclick="window.location.href='../login.php'">Giriş Sayfasına Git →</button>
            </div>
        </div>
    </div>

    <script>
        let currentStep = 1;
        let dbConfig = {};

        function nextStep(step) {
            document.getElementById('step' + currentStep).classList.remove('active');
            currentStep = step;
            document.getElementById('step' + currentStep).classList.add('active');
            updateProgress();
        }

        function updateProgress() {
            const progress = (currentStep / 5) * 100;
            document.getElementById('progressBar').style.width = progress + '%';
        }

        function testDatabase() {
            const form = document.getElementById('dbForm');
            const formData = new FormData(form);

            fetch('process.php?action=test_db', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    dbConfig = Object.fromEntries(formData);
                    nextStep(3);
                } else {
                    showError('dbError', data.message);
                }
            })
            .catch(err => {
                showError('dbError', 'Bağlantı hatası: ' + err.message);
            });
        }

        function validateLicense() {
            const form = document.getElementById('licenseForm');
            const formData = new FormData(form);
            
            // DB config'i ekle
            Object.keys(dbConfig).forEach(key => {
                formData.append(key, dbConfig[key]);
            });

            fetch('process.php?action=validate_license', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    nextStep(4);
                } else {
                    showError('licenseError', data.message);
                }
            })
            .catch(err => {
                showError('licenseError', 'Doğrulama hatası: ' + err.message);
            });
        }

        function createAdmin() {
            const form = document.getElementById('adminForm');
            const formData = new FormData(form);

            if (formData.get('admin_password') !== formData.get('admin_password_confirm')) {
                alert('Şifreler eşleşmiyor!');
                return;
            }

            // DB config'i ekle
            Object.keys(dbConfig).forEach(key => {
                formData.append(key, dbConfig[key]);
            });

            fetch('process.php?action=create_admin', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    nextStep(5);
                } else {
                    alert('Hata: ' + data.message);
                }
            })
            .catch(err => {
                alert('İşlem hatası: ' + err.message);
            });
        }

        function showError(elementId, message) {
            const el = document.getElementById(elementId);
            el.textContent = message;
            el.style.display = 'block';
            setTimeout(() => {
                el.style.display = 'none';
            }, 5000);
        }
    </script>
</body>
</html>
