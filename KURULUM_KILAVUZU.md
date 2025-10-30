# 🚀 Restaurant ERP - Kurulum Kılavuzu

## 📋 İçindekiler
1. [Sistem Gereksinimleri](#sistem-gereksinimleri)
2. [Kurulum Adımları](#kurulum-adımları)
3. [License Manager Kurulumu](#license-manager-kurulumu)
4. [İlk Yapılandırma](#ilk-yapılandırma)
5. [Sorun Giderme](#sorun-giderme)

---

## 🖥️ Sistem Gereksinimleri

### Minimum Gereksinimler
- **PHP:** 8.0 veya üzeri
- **MySQL:** 5.7 veya üzeri
- **Web Sunucu:** Apache 2.4+ veya Nginx
- **RAM:** 2 GB
- **Disk:** 500 MB boş alan

### PHP Eklentileri
```bash
php -m | grep -E 'pdo|mysqli|curl|mbstring|json'
```
Gerekli eklentiler:
- PDO
- pdo_mysql
- mysqli
- curl
- mbstring
- json
- openssl

---

## 📦 Kurulum Adımları

### 1️⃣ Dosyaları İndirin
```bash
# XAMPP kullanıyorsanız
cd C:\xampp\htdocs

# Projeyi buraya kopyalayın
# restaurant/ klasörü oluşmalı
```

### 2️⃣ XAMPP Servislerini Başlatın
1. XAMPP Control Panel'i açın
2. **Apache** ve **MySQL** servislerini başlatın
3. Yeşil ışık yanana kadar bekleyin

### 3️⃣ Kurulum Sihirbazını Çalıştırın

#### Adım 1: Hoş Geldiniz
1. Tarayıcınızda şu adresi açın:
   ```
   http://localhost/restaurant/setup/
   ```
2. "Kuruluma Başla" butonuna tıklayın

#### Adım 2: Veritabanı Ayarları
```
Sunucu Adresi: localhost
Veritabanı Adı: restaurant
Kullanıcı Adı: root
Şifre: (boş bırakın - XAMPP varsayılan)
```
3. "Bağlantıyı Test Et & Devam" butonuna tıklayın
4. Sistem otomatik olarak:
   - Veritabanını oluşturacak
   - Tüm tabloları kuracak
   - Örnek verileri ekleyecek

#### Adım 3: Lisans Anahtarı
1. Lisans anahtarınızı girin (örnek: `ABCD-1234-EFGH-5678`)
2. Firma adınızı girin
3. "Lisansı Doğrula & Devam" butonuna tıklayın

> **Not:** Lisans anahtarı almak için License Manager sistemini kurmanız gerekir (aşağıda anlatılmıştır)

#### Adım 4: Yönetici Hesabı
```
Kullanıcı Adı: admin
E-posta: admin@restaurant.com
Şifre: güçlü_şifre_123
Şifre Tekrar: güçlü_şifre_123
Ad Soyad: Admin Kullanıcı
```
4. "Hesap Oluştur & Devam" butonuna tıklayın

#### Adım 5: Tamamlandı! 🎉
1. "Giriş Sayfasına Git" butonuna tıklayın
2. Oluşturduğunuz kullanıcı adı ve şifre ile giriş yapın

### 4️⃣ Güvenlik (ÖNEMLİ!)
Kurulum tamamlandıktan sonra `setup/` klasörünü **mutlaka silin**:
```bash
# Windows
rmdir /s /q C:\xampp\htdocs\restaurant\setup

# Linux/Mac
rm -rf /path/to/restaurant/setup
```

---

## 🔑 License Manager Kurulumu

### Neden Gerekli?
License Manager, müşterilerinize lisans anahtarı üretmenizi ve yönetmenizi sağlar.

### Kurulum Adımları

#### 1️⃣ Veritabanını Oluşturun
1. Tarayıcınızda şu adresi açın:
   ```
   http://localhost/license-manager/install.php
   ```
2. "Veritabanı oluşturuldu" mesajını görmelisiniz
3. **Bu dosyayı silin:** `install.php`

#### 2️⃣ Admin Paneline Giriş
1. Şu adresi açın:
   ```
   http://localhost/license-manager/admin/login.php
   ```
2. Varsayılan giriş bilgileri:
   ```
   Kullanıcı Adı: admin
   Şifre: admin123
   ```
3. **ÖNEMLİ:** İlk girişten sonra şifreyi değiştirin!

#### 3️⃣ Yeni Lisans Oluşturun
1. Sol menüden "➕ Yeni Lisans" seçin
2. Bilgileri doldurun:
   - **Firma Adı:** Müşteri firma adı (opsiyonel)
   - **Lisans Süresi:** 1 Yıl / 2 Yıl / Ömür Boyu
   - **Maks. Kullanıcı:** Kaç kişi kullanabilir
   - **Özellikler:** Hangi modüller aktif olacak
3. "Lisans Oluştur" butonuna tıklayın
4. Oluşan lisans anahtarını kopyalayın
5. Bu anahtarı müşterinize verin

#### 4️⃣ Lisansları Görüntüleme
- "🔑 Lisanslar" menüsünden tüm lisansları görebilirsiniz
- Durum: Aktif / Süresi Dolmuş / Askıda
- Düzenle / Sil işlemleri yapabilirsiniz

---

## ⚙️ İlk Yapılandırma

### 1. Sistem Ayarları
Giriş yaptıktan sonra:
1. **Ayarlar** menüsüne gidin
2. Temel bilgileri doldurun:
   - Restoran adı
   - Para birimi (TRY)
   - Vergi oranı (%18)
   - E-posta bildirimleri (Açık/Kapalı)

### 2. Roller ve Yetkiler
Varsayılan roller:
- **Admin:** Tüm yetkiler
- **Garson:** Sipariş alma
- **Kasiyer:** Ödeme alma, raporlar
- **Şef:** Mutfak ekranı
- **Muhasebe:** Finans işlemleri

### 3. Masaları Kontrol Edin
- **Masalar** menüsünden 20 adet masa otomatik oluşturulmuştur
- Gerekirse ekleyin veya silin

### 4. Kategorileri ve Ürünleri Ekleyin
1. **Kategoriler** menüsünden kategori ekleyin (örn: Yiyecekler, İçecekler)
2. **Ürünler** menüsünden ürün ekleyin
3. Her ürün için fiyat belirleyin

### 5. Malzemeleri Tanımlayın (Stok Yönetimi)
1. **Malzemeler** menüsünden malzeme ekleyin
   - Ad: Un
   - Birim: kg
   - Minimum Stok: 10
   - Maliyet: 15 TL
2. **Reçeteler** menüsünden ürün-malzeme ilişkisi kurun
   - Ürün: Pizza
   - Malzeme: Un
   - Miktar: 0.2 kg

### 6. Tedarikçileri Ekleyin
1. **Tedarikçiler** menüsünden tedarikçi ekleyin
2. İletişim bilgilerini doldurun
3. Alış faturası girişi yapabilirsiniz

---

## 🔧 Gelişmiş Yapılandırma

### E-posta Ayarları
`inc/mail-config.php` dosyasını oluşturun:
```php
<?php
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your-email@gmail.com');
define('SMTP_PASS', 'your-password');
define('SMTP_FROM', 'noreply@restaurant.com');
```

### API Entegrasyonları
1. **Ayarlar > API Ayarları** menüsüne gidin
2. Getir veya Trendyol API anahtarlarını girin
3. Aktif/Pasif yapın

### QR Sipariş Sistemi
1. **Ayarlar** menüsünden "QR Sipariş" özelliğini aktif edin
2. Her masa için QR kod otomatik oluşturulur
3. QR kodları yazdırıp masalara koyun

---

## 🐛 Sorun Giderme

### Veritabanı Bağlantı Hatası
**Hata:** "Veritabani baglanti hatasi"

**Çözüm:**
1. MySQL servisinin çalıştığından emin olun
2. `model/Connection.php` dosyasını kontrol edin:
   ```php
   private $host = "localhost";
   private $dbname = "restaurant";
   private $username = "root";
   private $password = "";
   ```
3. phpMyAdmin'de veritabanının oluştuğunu kontrol edin

### Lisans Doğrulama Hatası
**Hata:** "Lisans bulunamadı"

**Çözüm:**
1. License Manager'ın kurulu olduğundan emin olun
2. `model/License.php` dosyasında API URL'ini kontrol edin:
   ```php
   private $apiUrl = "http://localhost/license-manager/api/validate.php";
   ```
3. Lisans anahtarını doğru girdiğinizden emin olun

### Stok Düşmüyor
**Sorun:** Sipariş verildiğinde stok azalmıyor

**Çözüm:**
1. **Reçeteler** menüsünden ürün-malzeme ilişkisi tanımlı mı kontrol edin
2. `recipes` tablosunda kayıt olmalı
3. Örnek:
   ```sql
   INSERT INTO recipes (product_id, material_id, quantity) 
   VALUES (1, 5, 0.2);
   ```

### Bildirimler Gelmiyor
**Çözüm:**
1. `settings` tablosunda kontrol edin:
   ```sql
   SELECT * FROM settings WHERE setting_key = 'email_notifications';
   ```
2. Değer `1` olmalı
3. E-posta ayarlarını kontrol edin

### Sayfa Bulunamadı (404)
**Çözüm:**
1. `.htaccess` dosyasının olduğundan emin olun
2. Apache'de `mod_rewrite` aktif mi kontrol edin
3. XAMPP'te varsayılan olarak aktiftir

---

## 📞 Destek

### Teknik Destek
- **E-posta:** support@restauranterp.com
- **Telefon:** +90 555 123 4567
- **Çalışma Saatleri:** Hafta içi 09:00 - 18:00

### Dokümantasyon
- **Kullanım Kılavuzu:** `README_YENI.md`
- **Geliştirme Planı:** `GELISTIRME_PLANI.md`
- **Online Dokümantasyon:** https://docs.restauranterp.com

### Topluluk
- **Forum:** https://forum.restauranterp.com
- **Discord:** https://discord.gg/restauranterp

---

## ✅ Kurulum Kontrol Listesi

Kurulumun başarılı olduğunu kontrol edin:

- [ ] XAMPP servisleri çalışıyor
- [ ] Veritabanı oluşturuldu (`restaurant`)
- [ ] Tüm tablolar mevcut (25+ tablo)
- [ ] Admin hesabı ile giriş yapabiliyorum
- [ ] Masalar görünüyor
- [ ] Ürün ekleyebiliyorum
- [ ] Sipariş oluşturabiliyorum
- [ ] License Manager kuruldu
- [ ] Lisans oluşturabiliyorum
- [ ] `setup/` klasörü silindi
- [ ] Sistem ayarları yapıldı

---

## 🎓 Eğitim Videoları

1. **Kurulum:** https://youtube.com/watch?v=xxxxx
2. **İlk Ayarlar:** https://youtube.com/watch?v=xxxxx
3. **Sipariş Alma:** https://youtube.com/watch?v=xxxxx
4. **Stok Yönetimi:** https://youtube.com/watch?v=xxxxx
5. **Muhasebe:** https://youtube.com/watch?v=xxxxx

---

**Başarılı bir kurulum dileriz! 🎉**

© 2025 Restaurant ERP - Tüm hakları saklıdır
