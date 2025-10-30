<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Raporlar - Restaurant ERP</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="assets/css/modern.css">
</head>
<body>
    <div class="top-bar">
        <h2><i class="fa fa-bar-chart"></i> Raporlar</h2>
        <a href="admin.php" class="btn-modern btn-primary"><i class="fa fa-arrow-left"></i> Admin Panel</a>
    </div>

    <div style="padding: 25px;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
            <!-- Satış Raporu -->
            <div class="card-modern" style="text-align: center;">
                <div style="font-size: 64px; color: #667eea; margin-bottom: 15px;">
                    <i class="fa fa-shopping-cart"></i>
                </div>
                <h3>Satış Raporu</h3>
                <p style="color: #7f8c8d;">Günlük, haftalık ve aylık satış raporları</p>
                <button class="btn-modern btn-primary" onclick="viewReport('sales')">
                    <i class="fa fa-file-text"></i> Raporu Görüntüle
                </button>
            </div>

            <!-- Stok Raporu -->
            <div class="card-modern" style="text-align: center;">
                <div style="font-size: 64px; color: #56ab2f; margin-bottom: 15px;">
                    <i class="fa fa-cubes"></i>
                </div>
                <h3>Stok Raporu</h3>
                <p style="color: #7f8c8d;">Mevcut stok durumu ve hareketler</p>
                <button class="btn-modern btn-success" onclick="viewReport('stock')">
                    <i class="fa fa-file-text"></i> Raporu Görüntüle
                </button>
            </div>

            <!-- Kâr/Zarar Raporu -->
            <div class="card-modern" style="text-align: center;">
                <div style="font-size: 64px; color: #f093fb; margin-bottom: 15px;">
                    <i class="fa fa-line-chart"></i>
                </div>
                <h3>Kâr/Zarar Raporu</h3>
                <p style="color: #7f8c8d;">Gelir-gider analizi ve kârlılık</p>
                <button class="btn-modern" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white;" onclick="viewReport('profit')">
                    <i class="fa fa-file-text"></i> Raporu Görüntüle
                </button>
            </div>

            <!-- Borç/Alacak Raporu -->
            <div class="card-modern" style="text-align: center;">
                <div style="font-size: 64px; color: #e74c3c; margin-bottom: 15px;">
                    <i class="fa fa-money"></i>
                </div>
                <h3>Borç/Alacak Raporu</h3>
                <p style="color: #7f8c8d;">Tedarikçi borçları ve müşteri alacakları</p>
                <button class="btn-modern btn-danger" onclick="viewReport('debt')">
                    <i class="fa fa-file-text"></i> Raporu Görüntüle
                </button>
            </div>

            <!-- Personel Raporu -->
            <div class="card-modern" style="text-align: center;">
                <div style="font-size: 64px; color: #4facfe; margin-bottom: 15px;">
                    <i class="fa fa-users"></i>
                </div>
                <h3>Personel Raporu</h3>
                <p style="color: #7f8c8d;">Maaş ödemeleri ve vardiya kayıtları</p>
                <button class="btn-modern" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white;" onclick="viewReport('staff')">
                    <i class="fa fa-file-text"></i> Raporu Görüntüle
                </button>
            </div>

            <!-- Ürün Performans Raporu -->
            <div class="card-modern" style="text-align: center;">
                <div style="font-size: 64px; color: #ffa502; margin-bottom: 15px;">
                    <i class="fa fa-star"></i>
                </div>
                <h3>Ürün Performans</h3>
                <p style="color: #7f8c8d;">En çok satan ve az satan ürünler</p>
                <button class="btn-modern" style="background: #ffa502; color: white;" onclick="viewReport('products')">
                    <i class="fa fa-file-text"></i> Raporu Görüntüle
                </button>
            </div>
        </div>
    </div>

    <script>
        function viewReport(type) {
            alert('Rapor türü: ' + type + '\n\nRapor görüntüleme sayfası açılacak');
        }
    </script>
</body>
</html>
