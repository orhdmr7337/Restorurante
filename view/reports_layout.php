<?php 
$pageTitle = 'Raporlar';
$activePage = 'reports';
include 'layout/header.php'; 
?>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
    <div class="card" style="text-align: center;">
        <div style="font-size: 64px; color: #667eea; margin-bottom: 15px;">
            <i class="fa fa-shopping-cart"></i>
        </div>
        <h3>Satış Raporu</h3>
        <p style="color: #7f8c8d;">Günlük, haftalık ve aylık satış raporları</p>
        <button class="btn-primary" onclick="viewReport('sales')">
            <i class="fa fa-file-text"></i> Raporu Görüntüle
        </button>
    </div>

    <div class="card" style="text-align: center;">
        <div style="font-size: 64px; color: #27ae60; margin-bottom: 15px;">
            <i class="fa fa-cubes"></i>
        </div>
        <h3>Stok Raporu</h3>
        <p style="color: #7f8c8d;">Mevcut stok durumu ve hareketler</p>
        <button class="btn-success" onclick="viewReport('stock')">
            <i class="fa fa-file-text"></i> Raporu Görüntüle
        </button>
    </div>

    <div class="card" style="text-align: center;">
        <div style="font-size: 64px; color: #f093fb; margin-bottom: 15px;">
            <i class="fa fa-line-chart"></i>
        </div>
        <h3>Kâr/Zarar Raporu</h3>
        <p style="color: #7f8c8d;">Gelir-gider analizi ve kârlılık</p>
        <button class="btn-warning" onclick="viewReport('profit')">
            <i class="fa fa-file-text"></i> Raporu Görüntüle
        </button>
    </div>

    <div class="card" style="text-align: center;">
        <div style="font-size: 64px; color: #e74c3c; margin-bottom: 15px;">
            <i class="fa fa-money"></i>
        </div>
        <h3>Borç/Alacak Raporu</h3>
        <p style="color: #7f8c8d;">Tedarikçi borçları ve müşteri alacakları</p>
        <button class="btn-danger" onclick="viewReport('debt')">
            <i class="fa fa-file-text"></i> Raporu Görüntüle
        </button>
    </div>

    <div class="card" style="text-align: center;">
        <div style="font-size: 64px; color: #3498db; margin-bottom: 15px;">
            <i class="fa fa-users"></i>
        </div>
        <h3>Personel Raporu</h3>
        <p style="color: #7f8c8d;">Maaş ödemeleri ve vardiya kayıtları</p>
        <button class="btn-primary" onclick="viewReport('staff')">
            <i class="fa fa-file-text"></i> Raporu Görüntüle
        </button>
    </div>

    <div class="card" style="text-align: center;">
        <div style="font-size: 64px; color: #f39c12; margin-bottom: 15px;">
            <i class="fa fa-star"></i>
        </div>
        <h3>Ürün Performans</h3>
        <p style="color: #7f8c8d;">En çok satan ve az satan ürünler</p>
        <button class="btn-warning" onclick="viewReport('products')">
            <i class="fa fa-file-text"></i> Raporu Görüntüle
        </button>
    </div>
</div>

<script>
    function viewReport(type) {
        alert('Rapor türü: ' + type + '\n\nRapor görüntüleme sayfası açılacak');
    }
</script>

<?php include 'layout/footer.php'; ?>
