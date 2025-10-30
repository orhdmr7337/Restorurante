<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ayarlar - Restaurant ERP</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="assets/css/modern.css">
</head>
<body>
    <div class="top-bar">
        <h2><i class="fa fa-cog"></i> Sistem Ayarları</h2>
        <a href="admin.php" class="btn-modern btn-primary"><i class="fa fa-arrow-left"></i> Admin Panel</a>
    </div>

    <div style="padding: 25px;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 25px;">
            <!-- Genel Ayarlar -->
            <div class="card-modern">
                <h3><i class="fa fa-info-circle"></i> Genel Ayarlar</h3>
                <hr>
                <form>
                    <div class="form-group">
                        <label>Restoran Adı</label>
                        <input type="text" class="form-control" value="<?= $settings['app_name'] ?? 'Restaurant ERP' ?>">
                    </div>
                    <div class="form-group">
                        <label>Para Birimi</label>
                        <select class="form-control">
                            <option value="TRY" <?= ($settings['currency'] ?? 'TRY') == 'TRY' ? 'selected' : '' ?>>TRY (₺)</option>
                            <option value="USD">USD ($)</option>
                            <option value="EUR">EUR (€)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>KDV Oranı (%)</label>
                        <input type="number" class="form-control" value="<?= $settings['tax_rate'] ?? '18' ?>">
                    </div>
                    <button type="submit" class="btn-modern btn-success">
                        <i class="fa fa-save"></i> Kaydet
                    </button>
                </form>
            </div>

            <!-- Stok Ayarları -->
            <div class="card-modern">
                <h3><i class="fa fa-cubes"></i> Stok Ayarları</h3>
                <hr>
                <form>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" <?= ($settings['low_stock_alert'] ?? '1') == '1' ? 'checked' : '' ?>>
                            Düşük stok uyarısı göster
                        </label>
                    </div>
                    <div class="form-group">
                        <label>Minimum stok uyarı seviyesi</label>
                        <input type="number" class="form-control" value="10">
                    </div>
                    <button type="submit" class="btn-modern btn-success">
                        <i class="fa fa-save"></i> Kaydet
                    </button>
                </form>
            </div>

            <!-- Bildirim Ayarları -->
            <div class="card-modern">
                <h3><i class="fa fa-bell"></i> Bildirim Ayarları</h3>
                <hr>
                <form>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" <?= ($settings['email_notifications'] ?? '1') == '1' ? 'checked' : '' ?>>
                            E-posta bildirimleri
                        </label>
                    </div>
                    <div class="form-group">
                        <label>
                            <input type="checkbox">
                            Stok uyarıları
                        </label>
                    </div>
                    <div class="form-group">
                        <label>
                            <input type="checkbox">
                            Borç uyarıları
                        </label>
                    </div>
                    <div class="form-group">
                        <label>Borç uyarı günü</label>
                        <input type="number" class="form-control" value="<?= $settings['debt_alert_days'] ?? '7' ?>">
                    </div>
                    <button type="submit" class="btn-modern btn-success">
                        <i class="fa fa-save"></i> Kaydet
                    </button>
                </form>
            </div>

            <!-- QR Sipariş -->
            <div class="card-modern">
                <h3><i class="fa fa-qrcode"></i> QR Sipariş</h3>
                <hr>
                <form>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" <?= ($settings['qr_order_enabled'] ?? '0') == '1' ? 'checked' : '' ?>>
                            QR sipariş sistemini aktif et
                        </label>
                    </div>
                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i> QR kod ile müşteriler masadan sipariş verebilir.
                    </div>
                    <button type="submit" class="btn-modern btn-success">
                        <i class="fa fa-save"></i> Kaydet
                    </button>
                </form>
            </div>

            <!-- Yedekleme -->
            <div class="card-modern">
                <h3><i class="fa fa-database"></i> Yedekleme</h3>
                <hr>
                <p>Veritabanı yedeği oluşturun ve geri yükleyin.</p>
                <button class="btn-modern btn-primary" onclick="createBackup()">
                    <i class="fa fa-download"></i> Yedek Al
                </button>
                <button class="btn-modern btn-warning" onclick="restoreBackup()">
                    <i class="fa fa-upload"></i> Geri Yükle
                </button>
            </div>

            <!-- Sistem Bilgisi -->
            <div class="card-modern">
                <h3><i class="fa fa-info"></i> Sistem Bilgisi</h3>
                <hr>
                <table class="table">
                    <tr>
                        <td><strong>Versiyon:</strong></td>
                        <td>2.0.0</td>
                    </tr>
                    <tr>
                        <td><strong>PHP Versiyonu:</strong></td>
                        <td><?= phpversion() ?></td>
                    </tr>
                    <tr>
                        <td><strong>Veritabanı:</strong></td>
                        <td>MySQL</td>
                    </tr>
                    <tr>
                        <td><strong>Lisans Durumu:</strong></td>
                        <td><span class="label label-success">Aktif</span></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <script>
        function createBackup() {
            if(confirm('Veritabanı yedeği oluşturulacak. Onaylıyor musunuz?')) {
                alert('Yedek oluşturma işlemi başlatıldı...');
            }
        }
        
        function restoreBackup() {
            if(confirm('Veritabanı geri yüklenecek. Bu işlem mevcut verileri etkileyebilir. Onaylıyor musunuz?')) {
                alert('Geri yükleme işlemi başlatıldı...');
            }
        }
    </script>
</body>
</html>
