# 🍽️ RESTORAN ERP SİSTEMİ - GELİŞTİRME PLANI

## 📊 PROJE ÖZETİ

**Hedef:** Satılabilir, profesyonel restoran yönetim sistemi  
**Kapsam:** Sipariş + Stok + Muhasebe + Borç + Personel + API Entegrasyonları  
**Teknoloji:** PHP 8.0+, MySQL, PDO, MVC Mimarisi

---

## 🎯 SİSTEM ÖZELLİKLERİ

### ✅ TEMEL MODÜLLER
- [x] Sipariş Yönetimi (mevcut)
- [x] Masa Yönetimi (mevcut)
- [x] Menü Yönetimi (mevcut)
- [x] Kullanıcı Yönetimi (mevcut)
- [ ] **Stok Yönetimi** (YENİ)
- [ ] **Muhasebe Sistemi** (YENİ)
- [ ] **Borç/Alacak Takibi** (YENİ)
- [ ] **Tedarikçi Yönetimi** (YENİ)
- [ ] **Personel & Maaş** (YENİ)
- [ ] **Raporlama** (YENİ)
- [ ] **API Entegrasyonları** (YENİ)

### 🔐 GÜVENLİK & LİSANS
- [ ] Lisans key sistemi
- [ ] Hızlı kurulum sihirbazı
- [ ] Rol bazlı yetkilendirme
- [ ] CSRF koruması
- [ ] Password hashing (bcrypt)

### 📱 ENTEGRASYONLAR
- [ ] QR Sipariş (açılıp kapatılabilir)
- [ ] Getir API
- [ ] Trendyol Yemek API
- [ ] E-posta bildirimleri
- [ ] PDF raporlama

---

## 📦 YENİ VERİTABANI TABLOLARI

### 1. STOK YÖNETİMİ
```sql
-- Malzemeler
CREATE TABLE materials (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    unit VARCHAR(50) NOT NULL,
    current_stock DECIMAL(10,2) DEFAULT 0,
    min_stock DECIMAL(10,2) DEFAULT 0,
    cost_price DECIMAL(10,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Stok Hareketleri
CREATE TABLE stock_movements (
    id INT PRIMARY KEY AUTO_INCREMENT,
    material_id INT NOT NULL,
    type ENUM('in','out') NOT NULL,
    quantity DECIMAL(10,2) NOT NULL,
    reference_type VARCHAR(50),
    reference_id INT,
    user_id INT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (material_id) REFERENCES materials(id)
);

-- Reçeteler
CREATE TABLE recipes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    material_id INT NOT NULL,
    quantity DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (material_id) REFERENCES materials(id)
);
```

### 2. TEDARİKÇİ YÖNETİMİ
```sql
-- Tedarikçiler
CREATE TABLE suppliers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    contact_person VARCHAR(255),
    phone VARCHAR(20),
    email VARCHAR(255),
    address TEXT,
    tax_number VARCHAR(50),
    iban VARCHAR(50),
    balance DECIMAL(10,2) DEFAULT 0,
    status TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Satın Alma İşlemleri
CREATE TABLE purchases (
    id INT PRIMARY KEY AUTO_INCREMENT,
    supplier_id INT NOT NULL,
    invoice_number VARCHAR(100),
    total_amount DECIMAL(10,2),
    tax_amount DECIMAL(10,2),
    payment_status ENUM('unpaid','partial','paid') DEFAULT 'unpaid',
    purchase_date DATE,
    due_date DATE,
    user_id INT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id)
);

-- Satın Alma Detayları
CREATE TABLE purchase_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    purchase_id INT NOT NULL,
    material_id INT NOT NULL,
    quantity DECIMAL(10,2),
    unit_price DECIMAL(10,2),
    total_price DECIMAL(10,2),
    FOREIGN KEY (purchase_id) REFERENCES purchases(id),
    FOREIGN KEY (material_id) REFERENCES materials(id)
);
```

### 3. BORÇ/ALACAK SİSTEMİ
```sql
-- Cari Hesaplar
CREATE TABLE accounts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    type ENUM('customer','supplier') NOT NULL,
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    email VARCHAR(255),
    address TEXT,
    balance DECIMAL(10,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Cari Hareketler
CREATE TABLE account_transactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    account_id INT NOT NULL,
    type ENUM('debit','credit') NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    description TEXT,
    reference_type VARCHAR(50),
    reference_id INT,
    transaction_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (account_id) REFERENCES accounts(id)
);

-- Borç Ödemeleri
CREATE TABLE debt_payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    account_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('cash','card','bank','other'),
    payment_date DATE,
    notes TEXT,
    user_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (account_id) REFERENCES accounts(id)
);
```

### 4. MUHASEBE
```sql
-- Gelirler
CREATE TABLE incomes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    category VARCHAR(100),
    amount DECIMAL(10,2) NOT NULL,
    description TEXT,
    income_date DATE,
    payment_method VARCHAR(50),
    reference_type VARCHAR(50),
    reference_id INT,
    user_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Giderler
CREATE TABLE expenses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    category VARCHAR(100),
    amount DECIMAL(10,2) NOT NULL,
    description TEXT,
    expense_date DATE,
    payment_method VARCHAR(50),
    user_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Kasa Hareketleri
CREATE TABLE cash_transactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    type ENUM('in','out') NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    description TEXT,
    reference_type VARCHAR(50),
    reference_id INT,
    transaction_date DATE,
    user_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Banka Hareketleri
CREATE TABLE bank_transactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    account_name VARCHAR(255),
    type ENUM('in','out') NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    description TEXT,
    reference_number VARCHAR(100),
    transaction_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### 5. PERSONEL & MAAŞ
```sql
-- Roller
CREATE TABLE roles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    permissions TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Kullanıcı Rolleri (users tablosu güncellenecek)
ALTER TABLE users ADD COLUMN role_id INT;
ALTER TABLE users ADD COLUMN salary DECIMAL(10,2);
ALTER TABLE users ADD COLUMN hire_date DATE;
ALTER TABLE users ADD COLUMN status TINYINT DEFAULT 1;

-- Maaş Ödemeleri
CREATE TABLE salary_payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_date DATE,
    period_month INT,
    period_year INT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Vardiyalar
CREATE TABLE shifts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    shift_date DATE,
    start_time TIME,
    end_time TIME,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

### 6. RAPORLAMA & BİLDİRİMLER
```sql
-- Bildirimler
CREATE TABLE notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    type VARCHAR(50),
    title VARCHAR(255),
    message TEXT,
    user_id INT,
    is_read TINYINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Sistem Logları
CREATE TABLE system_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    action VARCHAR(255),
    table_name VARCHAR(100),
    record_id INT,
    old_value TEXT,
    new_value TEXT,
    ip_address VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### 7. API ENTEGRASYONLARI
```sql
-- API Ayarları
CREATE TABLE api_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    provider VARCHAR(50) NOT NULL,
    api_key VARCHAR(255),
    api_secret VARCHAR(255),
    is_active TINYINT DEFAULT 0,
    settings TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Dış Siparişler
CREATE TABLE external_orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    provider VARCHAR(50),
    external_id VARCHAR(255),
    order_data TEXT,
    status VARCHAR(50),
    total_amount DECIMAL(10,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### 8. LİSANS SİSTEMİ
```sql
-- Lisans
CREATE TABLE license (
    id INT PRIMARY KEY AUTO_INCREMENT,
    license_key VARCHAR(255) NOT NULL UNIQUE,
    company_name VARCHAR(255),
    activation_date DATE,
    expiry_date DATE,
    status ENUM('active','expired','suspended') DEFAULT 'active',
    max_users INT DEFAULT 10,
    features TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Sistem Ayarları
CREATE TABLE settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) UNIQUE,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

---

## 🔧 YENİ MODEL DOSYALARI

### 1. Material.php (Stok Yönetimi)
- `getAllMaterials()` - Tüm malzemeleri listele
- `getMaterial($id)` - Tek malzeme getir
- `addMaterial($data)` - Yeni malzeme ekle
- `updateMaterial($id, $data)` - Malzeme güncelle
- `deleteMaterial($id)` - Malzeme sil
- `getLowStock()` - Düşük stoklu malzemeler
- `addStockMovement($materialId, $type, $quantity, $notes)` - Stok hareketi
- `getStockHistory($materialId)` - Stok geçmişi

### 2. Supplier.php (Tedarikçi)
- `getAllSuppliers()` - Tüm tedarikçiler
- `getSupplier($id)` - Tek tedarikçi
- `addSupplier($data)` - Yeni tedarikçi
- `updateSupplier($id, $data)` - Tedarikçi güncelle
- `deleteSupplier($id)` - Tedarikçi sil
- `getSupplierBalance($id)` - Tedarikçi bakiyesi

### 3. Purchase.php (Satın Alma)
- `createPurchase($data)` - Yeni alış faturası
- `addPurchaseItem($purchaseId, $materialId, $qty, $price)` - Fatura kalemi
- `getPurchase($id)` - Alış faturası detayı
- `updatePaymentStatus($id, $status)` - Ödeme durumu güncelle
- `makePayment($purchaseId, $amount)` - Ödeme yap

### 4. Account.php (Cari Hesap)
- `createAccount($type, $data)` - Yeni cari hesap
- `addTransaction($accountId, $type, $amount, $desc)` - Hareket ekle
- `getAccountBalance($id)` - Hesap bakiyesi
- `getAccountStatement($id, $startDate, $endDate)` - Ekstre
- `makePayment($accountId, $amount, $method)` - Borç ödemesi

### 5. Finance.php (Muhasebe)
- `addIncome($data)` - Gelir ekle
- `addExpense($data)` - Gider ekle
- `getCashBalance()` - Kasa bakiyesi
- `getBankBalance()` - Banka bakiyesi
- `getDailyReport($date)` - Günlük rapor
- `getMonthlyReport($month, $year)` - Aylık rapor
- `getProfitLoss($startDate, $endDate)` - Kâr-zarar

### 6. Staff.php (Personel)
- `addStaff($data)` - Personel ekle
- `updateStaff($id, $data)` - Personel güncelle
- `deleteStaff($id)` - Personel sil
- `paySalary($userId, $amount, $period)` - Maaş öde
- `getSalaryHistory($userId)` - Maaş geçmişi
- `addShift($userId, $date, $start, $end)` - Vardiya ekle

### 7. Report.php (Raporlama)
- `generateSalesReport($startDate, $endDate)` - Satış raporu
- `generateStockReport()` - Stok raporu
- `generateDebtReport()` - Borç raporu
- `generateProfitLossReport($period)` - Kâr-zarar raporu
- `exportToPDF($reportType, $data)` - PDF çıktı
- `sendEmailReport($email, $reportType)` - E-posta gönder

### 8. Notification.php (Bildirim)
- `create($type, $title, $message, $userId)` - Bildirim oluştur
- `getUnread($userId)` - Okunmamış bildirimler
- `markAsRead($id)` - Okundu işaretle
- `sendStockAlert($materialId)` - Stok uyarısı
- `sendDebtAlert($accountId)` - Borç uyarısı

### 9. License.php (Lisans)
- `validateLicense($key)` - Lisans doğrula
- `activateLicense($key, $companyName)` - Lisans aktive et
- `checkExpiry()` - Süre kontrolü
- `getFeatures()` - Özellik listesi

### 10. API.php (API Entegrasyon)
- `connectGetir()` - Getir API bağlantı
- `connectTrendyol()` - Trendyol API bağlantı
- `syncOrders($provider)` - Sipariş senkronizasyonu
- `updateOrderStatus($externalId, $status)` - Durum güncelle

---

## 🎨 YENİ CONTROLLER DOSYALARI

- `MaterialController.php` - Stok işlemleri
- `SupplierController.php` - Tedarikçi işlemleri
- `PurchaseController.php` - Alış fatura işlemleri
- `AccountController.php` - Cari hesap işlemleri
- `FinanceController.php` - Muhasebe işlemleri
- `StaffController.php` - Personel işlemleri
- `ReportController.php` - Rapor oluşturma
- `NotificationController.php` - Bildirim yönetimi
- `APIController.php` - API entegrasyonları
- `SetupController.php` - Kurulum sihirbazı

---

## 📱 YENİ VIEW DOSYALARI

### Admin Panel
- `dashboard.php` - Ana sayfa (istatistikler)
- `materials.php` - Stok yönetimi
- `suppliers.php` - Tedarikçi listesi
- `purchases.php` - Alış faturaları
- `accounts.php` - Cari hesaplar
- `finance.php` - Muhasebe ekranı
- `staff.php` - Personel yönetimi
- `reports.php` - Raporlar
- `settings.php` - Sistem ayarları
- `api-settings.php` - API ayarları

### Kurulum
- `setup/index.php` - Kurulum başlangıç
- `setup/database.php` - Veritabanı ayarları
- `setup/license.php` - Lisans girişi
- `setup/admin.php` - Admin kullanıcı oluşturma
- `setup/complete.php` - Kurulum tamamlandı

---

## 🔐 GÜVENLİK GÜNCELLEMELERİ

### 1. Password Hashing
```php
// User.php içinde
password_hash($password, PASSWORD_BCRYPT);
password_verify($password, $hash);
```

### 2. CSRF Token
```php
// functions.php içinde
function generateCSRFToken() {
    return bin2hex(random_bytes(32));
}
```

### 3. Input Validation
```php
// Validator.php (yeni)
class Validator {
    public static function sanitize($data);
    public static function validateEmail($email);
    public static function validatePhone($phone);
}
```

---

## 📋 GELİŞTİRME ADIMLARI

### AŞAMA 1: ALTYAPI (1-2 Hafta)
- [ ] Kurulum sihirbazı
- [ ] Lisans sistemi
- [ ] Veritabanı şeması güncelleme
- [ ] .env dosya desteği
- [ ] Güvenlik güncellemeleri

### AŞAMA 2: STOK & TEDARİKÇİ (1 Hafta)
- [ ] Material model & controller
- [ ] Supplier model & controller
- [ ] Purchase model & controller
- [ ] Stok yönetimi arayüzü
- [ ] Reçete sistemi

### AŞAMA 3: MUHASEBE (1 Hafta)
- [ ] Finance model & controller
- [ ] Account model & controller
- [ ] Gelir-gider takibi
- [ ] Kasa-banka yönetimi
- [ ] Muhasebe arayüzü

### AŞAMA 4: PERSONEL (3-4 Gün)
- [ ] Rol sistemi
- [ ] Staff model & controller
- [ ] Maaş takibi
- [ ] Vardiya yönetimi

### AŞAMA 5: RAPORLAMA (3-4 Gün)
- [ ] Report model & controller
- [ ] PDF kütüphanesi entegrasyonu
- [ ] E-posta gönderimi
- [ ] Rapor arayüzleri

### AŞAMA 6: BİLDİRİMLER (2-3 Gün)
- [ ] Notification model & controller
- [ ] Stok uyarıları
- [ ] Borç uyarıları
- [ ] Gün sonu raporları

### AŞAMA 7: API ENTEGRASYONLARI (1 Hafta)
- [ ] QR sipariş sistemi
- [ ] Getir API
- [ ] Trendyol Yemek API
- [ ] Sipariş senkronizasyonu

### AŞAMA 8: TEST & OPTİMİZASYON (3-4 Gün)
- [ ] Güvenlik testleri
- [ ] Performans optimizasyonu
- [ ] Hata düzeltmeleri
- [ ] Dokümantasyon

---

## 📦 EK KÜTÜPHANELER

```json
{
  "dependencies": {
    "phpmailer/phpmailer": "^6.8",
    "dompdf/dompdf": "^2.0",
    "vlucas/phpdotenv": "^5.5",
    "guzzlehttp/guzzle": "^7.5"
  }
}
```

---

## 🎯 ÖNCELİK SIRASI

1. **Kurulum Sihirbazı** (kritik)
2. **Lisans Sistemi** (kritik)
3. **Stok Yönetimi** (yüksek)
4. **Tedarikçi & Alış** (yüksek)
5. **Muhasebe** (yüksek)
6. **Borç Takibi** (orta)
7. **Personel & Maaş** (orta)
8. **Raporlama** (orta)
9. **API Entegrasyonları** (düşük)
10. **Mobil Uygulama** (gelecek)

---

## 📞 DESTEK & DOKÜMANTASYON

- Kullanıcı kılavuzu (PDF)
- Video eğitimler
- API dokümantasyonu
- Teknik destek sistemi

---

**Son Güncelleme:** 2025-10-25  
**Versiyon:** 2.0.0  
**Geliştirici:** Restaurant ERP Team
