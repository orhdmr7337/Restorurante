# 🍽️ Restaurant ERP System v2.0

## 📌 Genel Bakış

Restaurant ERP, restoranlar için geliştirilmiş profesyonel, tam donanımlı bir yönetim sistemidir. Sipariş yönetiminden muhasebe takibine, stok yönetiminden personel maaş takibine kadar tüm işlemleri tek bir platformda yönetmenizi sağlar.

---

## ✨ Özellikler

### 🎯 Temel Modüller
- ✅ **Sipariş & Masa Yönetimi** - Masa bazlı sipariş takibi
- ✅ **Menü Yönetimi** - Kategori ve ürün yönetimi
- ✅ **Stok Yönetimi** - Malzeme takibi, reçete sistemi
- ✅ **Tedarikçi Yönetimi** - Alış faturaları, borç takibi
- ✅ **Cari Hesap** - Müşteri ve tedarikçi borç/alacak
- ✅ **Muhasebe** - Gelir, gider, kasa, banka yönetimi
- ✅ **Personel & Maaş** - Çalışan yönetimi, maaş ödemeleri
- ✅ **Raporlama** - Detaylı raporlar, PDF çıktı
- ✅ **Bildirim Sistemi** - Otomatik uyarılar
- ✅ **Lisans Yönetimi** - Güvenli lisans kontrolü

### 🔌 Entegrasyonlar
- 🔄 **QR Sipariş** (Açılıp kapatılabilir)
- 🔄 **Getir API** (Hazır)
- 🔄 **Trendyol Yemek API** (Hazır)
- 📧 **E-posta Bildirimleri**
- 📄 **PDF Raporlama**

---

## 🚀 Kurulum

### Gereksinimler
- PHP 8.0 veya üzeri
- MySQL 5.7 veya üzeri
- Apache/Nginx web sunucusu
- cURL extension (API entegrasyonları için)

### Adım 1: Dosyaları Yükleyin
```bash
# Projeyi klonlayın veya indirin
git clone https://github.com/yourusername/restaurant-erp.git
cd restaurant-erp
```

### Adım 2: Kurulum Sihirbazını Çalıştırın
1. Tarayıcınızda `http://localhost/restaurant/setup/` adresine gidin
2. Veritabanı bilgilerinizi girin
3. Lisans anahtarınızı girin
4. Admin hesabı oluşturun
5. Kurulum tamamlandı!

### Adım 3: Güvenlik
Kurulum tamamlandıktan sonra `setup/` klasörünü silin:
```bash
rm -rf setup/
```

---

## 🔑 Lisans Sistemi

### License Manager Kurulumu
1. `http://localhost/license-manager/install.php` adresine gidin
2. Veritabanı otomatik oluşturulacak
3. Admin paneline giriş yapın: `admin/login.php`
   - Kullanıcı: `admin`
   - Şifre: `admin123`

### Yeni Lisans Oluşturma
1. Admin paneline giriş yapın
2. "Yeni Lisans" menüsüne tıklayın
3. Firma bilgilerini ve süreyi seçin
4. Lisans anahtarı otomatik oluşturulacak
5. Anahtarı müşterinize verin

### Lisans Tipleri
- **1 Yıl** - ₺2,500 (5 kullanıcı)
- **2 Yıl** - ₺4,500 (15 kullanıcı)
- **Ömür Boyu** - ₺7,500 (Sınırsız kullanıcı)

---

## 📊 Veritabanı Yapısı

### Ana Tablolar
- `users` - Kullanıcılar ve personel
- `roles` - Rol tanımları
- `tables` - Masalar
- `products` - Ürünler
- `product_categories` - Kategoriler
- `orders` - Siparişler
- `order_products` - Sipariş detayları

### Stok Yönetimi
- `materials` - Malzemeler
- `stock_movements` - Stok hareketleri
- `recipes` - Reçeteler (ürün-malzeme ilişkisi)

### Tedarikçi & Alış
- `suppliers` - Tedarikçiler
- `purchases` - Alış faturaları
- `purchase_items` - Fatura kalemleri

### Muhasebe
- `incomes` - Gelirler
- `expenses` - Giderler
- `cash_transactions` - Kasa hareketleri
- `bank_transactions` - Banka hareketleri
- `accounts` - Cari hesaplar
- `account_transactions` - Cari hareketler
- `debt_payments` - Borç ödemeleri

### Personel
- `salary_payments` - Maaş ödemeleri
- `shifts` - Vardiyalar

### Sistem
- `license` - Lisans bilgisi
- `settings` - Sistem ayarları
- `notifications` - Bildirimler
- `system_logs` - Sistem logları
- `api_settings` - API ayarları

---

## 👥 Kullanıcı Rolleri

### Admin
- Tüm yetkilere sahip
- Sistem ayarları
- Kullanıcı yönetimi

### Garson
- Sipariş alma
- Masa yönetimi

### Kasiyer
- Ödeme alma
- Hesap kapatma
- Raporlar

### Şef
- Sipariş görüntüleme
- Mutfak ekranı

### Muhasebe
- Finans işlemleri
- Stok yönetimi
- Tedarikçi işlemleri
- Raporlar

---

## 🔧 Yapılandırma

### Veritabanı Bağlantısı
`model/Connection.php` dosyasını düzenleyin:
```php
private $host = "localhost";
private $dbname = "restaurant";
private $username = "root";
private $password = "";
```

### Lisans API URL
`model/License.php` dosyasında:
```php
private $apiUrl = "http://yourdomain.com/license-manager/api/validate.php";
```

---

## 📖 Kullanım Kılavuzu

### Sipariş Alma
1. Masalar ekranından boş masa seçin
2. Ürünleri sepete ekleyin
3. Siparişi kaydedin
4. Masa otomatik aktif olur

### Stok Yönetimi
1. Malzemeler menüsünden yeni malzeme ekleyin
2. Reçete menüsünden ürün-malzeme ilişkisi kurun
3. Sipariş verildiğinde stok otomatik düşer
4. Minimum stok altına düşünce bildirim gelir

### Alış Faturası Girişi
1. Tedarikçiler menüsünden tedarikçi ekleyin
2. Alış Faturaları > Yeni Fatura
3. Malzemeleri ve miktarları girin
4. Stok otomatik artar, borç kaydedilir

### Muhasebe
1. Gelir/Gider menüsünden işlem ekleyin
2. Kasa/Banka hareketleri otomatik kaydedilir
3. Raporlar menüsünden analiz yapın

---

## 🔔 Bildirimler

### Otomatik Bildirimler
- ✅ Yeni sipariş geldiğinde
- ✅ Stok minimum seviyenin altına düştüğünde
- ✅ Borç vadesi dolduğunda
- ✅ Gün sonu raporu

### E-posta Bildirimleri
`settings` tablosunda `email_notifications = 1` yapın

---

## 🛡️ Güvenlik

### Şifreleme
- Kullanıcı şifreleri `password_hash()` ile şifrelenir
- Eski MD5 şifreler güncellenmeli

### CSRF Koruması
Formlarınıza CSRF token ekleyin:
```php
<input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
```

### SQL Injection
Tüm sorgularda prepared statements kullanılır

---

## 📱 API Kullanımı

### Lisans Doğrulama
```php
POST /license-manager/api/validate.php
{
    "license_key": "XXXX-XXXX-XXXX-XXXX"
}
```

**Yanıt:**
```json
{
    "valid": true,
    "company_name": "Firma Adı",
    "expiry_date": "2026-10-25",
    "max_users": 10,
    "features": {...}
}
```

---

## 🐛 Sorun Giderme

### Veritabanı Bağlantı Hatası
- MySQL servisinin çalıştığından emin olun
- `Connection.php` dosyasındaki bilgileri kontrol edin

### Lisans Hatası
- Lisans anahtarının doğru girildiğinden emin olun
- License Manager API'sinin erişilebilir olduğunu kontrol edin

### Stok Düşmüyor
- Reçete tanımlarını kontrol edin
- `recipes` tablosunda ürün-malzeme ilişkisi olmalı

---

## 📞 Destek

- **E-posta:** support@restauranterp.com
- **Telefon:** +90 555 123 4567
- **Dokümantasyon:** https://docs.restauranterp.com

---

## 📝 Lisans

Bu yazılım ticari bir üründür. Kullanım için geçerli bir lisans anahtarı gereklidir.

---

## 🔄 Güncelleme Geçmişi

### v2.0.0 (2025-10-25)
- ✅ Tam stok yönetimi sistemi
- ✅ Tedarikçi ve alış yönetimi
- ✅ Borç/alacak takibi
- ✅ Muhasebe modülü
- ✅ Personel ve maaş yönetimi
- ✅ Lisans sistemi
- ✅ Kurulum sihirbazı
- ✅ License Manager (ayrı sistem)
- ✅ API entegrasyonları hazır
- ✅ Bildirim sistemi

### v1.0.0
- Temel sipariş ve masa yönetimi
- Menü yönetimi
- Kullanıcı sistemi

---

## 🎯 Gelecek Özellikler

- [ ] Mobil uygulama
- [ ] Getir & Trendyol entegrasyonu (aktif)
- [ ] Online ödeme entegrasyonu
- [ ] QR menü sistemi
- [ ] Müşteri sadakat programı
- [ ] Rezervasyon sistemi

---

**© 2025 Restaurant ERP - Tüm hakları saklıdır**
