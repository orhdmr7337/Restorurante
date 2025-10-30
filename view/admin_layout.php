<?php 
$pageTitle = 'Dashboard';
$activePage = 'dashboard';
include 'layout/header.php'; 
?>

<!-- Stats Grid -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="icon" style="color: #667eea;"><i class="fa fa-money"></i></div>
        <h3>Bugünkü Gelir</h3>
        <div class="value">0.00 TL</div>
    </div>
    <div class="stat-card">
        <div class="icon" style="color: #f093fb;"><i class="fa fa-shopping-cart"></i></div>
        <h3>Bugünkü Sipariş</h3>
        <div class="value">1</div>
    </div>
    <div class="stat-card">
        <div class="icon" style="color: #4facfe;"><i class="fa fa-users"></i></div>
        <h3>Aktif Masa</h3>
        <div class="value">0</div>
    </div>
</div>

<!-- Quick Actions -->
<div class="card">
    <div class="card-header">
        <h3><i class="fa fa-bolt"></i> Hızlı İşlemler</h3>
    </div>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px;">
        <a href="pos.php" style="text-decoration: none; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 25px; border-radius: 12px; text-align: center; transition: all 0.3s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
            <i class="fa fa-calculator" style="font-size: 40px; display: block; margin-bottom: 12px;"></i>
            <strong style="font-size: 16px;">Kasiyer POS</strong>
        </a>
        <a href="index.php" style="text-decoration: none; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 25px; border-radius: 12px; text-align: center; transition: all 0.3s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
            <i class="fa fa-th" style="font-size: 40px; display: block; margin-bottom: 12px;"></i>
            <strong style="font-size: 16px;">Masalar</strong>
        </a>
        <a href="products.php" style="text-decoration: none; background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; padding: 25px; border-radius: 12px; text-align: center; transition: all 0.3s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
            <i class="fa fa-cutlery" style="font-size: 40px; display: block; margin-bottom: 12px;"></i>
            <strong style="font-size: 16px;">Ürünler</strong>
        </a>
        <a href="materials.php" style="text-decoration: none; background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); color: white; padding: 25px; border-radius: 12px; text-align: center; transition: all 0.3s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
            <i class="fa fa-cubes" style="font-size: 40px; display: block; margin-bottom: 12px;"></i>
            <strong style="font-size: 16px;">Stok Yönetimi</strong>
        </a>
        <a href="suppliers.php" style="text-decoration: none; background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); color: #2c3e50; padding: 25px; border-radius: 12px; text-align: center; transition: all 0.3s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
            <i class="fa fa-truck" style="font-size: 40px; display: block; margin-bottom: 12px;"></i>
            <strong style="font-size: 16px;">Tedarikçiler</strong>
        </a>
        <a href="finance.php" style="text-decoration: none; background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%); color: #2c3e50; padding: 25px; border-radius: 12px; text-align: center; transition: all 0.3s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
            <i class="fa fa-money" style="font-size: 40px; display: block; margin-bottom: 12px;"></i>
            <strong style="font-size: 16px;">Muhasebe</strong>
        </a>
        <a href="reports.php" style="text-decoration: none; background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%); color: #2c3e50; padding: 25px; border-radius: 12px; text-align: center; transition: all 0.3s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
            <i class="fa fa-bar-chart" style="font-size: 40px; display: block; margin-bottom: 12px;"></i>
            <strong style="font-size: 16px;">Raporlar</strong>
        </a>
        <a href="userList.php" style="text-decoration: none; background: linear-gradient(135deg, #a1c4fd 0%, #c2e9fb 100%); color: #2c3e50; padding: 25px; border-radius: 12px; text-align: center; transition: all 0.3s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
            <i class="fa fa-users" style="font-size: 40px; display: block; margin-bottom: 12px;"></i>
            <strong style="font-size: 16px;">Personel</strong>
        </a>
    </div>
</div>


<?php include 'layout/footer.php'; ?>
