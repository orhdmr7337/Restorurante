<?php
require "inc/global.php";

// Kullanıcı kontrolü
if (!isset($_SESSION['user_session'])) {
    redirect('login.php');
}

$userId = $_SESSION['user_session'];
$userInfo = $usrObj->getOneUser($userId);

require "view/_header.php";
?>

<div class="container">
    <div class="page-header">
        <h1>Restaurant ERP Dashboard <small>Hoşgeldin, <?= htmlspecialchars($userInfo['fullname']) ?></small></h1>
    </div>

    <!-- Hızlı İstatistikler -->
    <div class="row">
        <div class="col-md-3">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title">Bugünkü Satış</h3>
                </div>
                <div class="panel-body text-center">
                    <h2>0 TL</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="panel panel-success">
                <div class="panel-heading">
                    <h3 class="panel-title">Aktif Masalar</h3>
                </div>
                <div class="panel-body text-center">
                    <?php
                    $activeTables = 0;
                    foreach ($tblObj->getAllTables() as $table) {
                        if ($table['status'] == 1) $activeTables++;
                    }
                    ?>
                    <h2><?= $activeTables ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="panel panel-warning">
                <div class="panel-heading">
                    <h3 class="panel-title">Toplam Ürün</h3>
                </div>
                <div class="panel-body text-center">
                    <?php
                    $menuObj = new Menu();
                    $productCount = $menuObj->getAllProductCount();
                    ?>
                    <h2><?= $productCount['COUNT(id)'] ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="panel panel-info">
                <div class="panel-heading">
                    <h3 class="panel-title">Toplam Kullanıcı</h3>
                </div>
                <div class="panel-body text-center">
                    <?php
                    $userCount = $usrObj->getAllUserCount();
                    ?>
                    <h2><?= $userCount['COUNT(id)'] ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Menü Butonları -->
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Hızlı Erişim</h3>
                </div>
                <div class="panel-body text-center">
                    <a href="index.php" class="btn btn-primary btn-lg" style="margin: 5px;">
                        <i class="glyphicon glyphicon-th"></i><br>Masalar
                    </a>
                    
                    <?php if ($userInfo["user_position"] == 1 || $userInfo["user_position"] == 2): ?>
                    <a href="products.php" class="btn btn-warning btn-lg" style="margin: 5px;">
                        <i class="glyphicon glyphicon-cutlery"></i><br>Ürünler
                    </a>
                    <a href="materials.php" class="btn btn-success btn-lg" style="margin: 5px;">
                        <i class="glyphicon glyphicon-list-alt"></i><br>Stok
                    </a>
                    <a href="suppliers.php" class="btn btn-info btn-lg" style="margin: 5px;">
                        <i class="glyphicon glyphicon-briefcase"></i><br>Tedarikçiler
                    </a>
                    <a href="purchases.php" class="btn btn-primary btn-lg" style="margin: 5px;">
                        <i class="glyphicon glyphicon-file"></i><br>Alış Faturaları
                    </a>
                    <a href="finance.php" class="btn btn-danger btn-lg" style="margin: 5px;">
                        <i class="glyphicon glyphicon-usd"></i><br>Muhasebe
                    </a>
                    <?php endif; ?>
                    
                    <?php if ($userInfo["user_position"] == 1): ?>
                    <a href="admin.php" class="btn btn-default btn-lg" style="margin: 5px;">
                        <i class="glyphicon glyphicon-cog"></i><br>Admin
                    </a>
                    <a href="userList.php" class="btn btn-default btn-lg" style="margin: 5px;">
                        <i class="glyphicon glyphicon-user"></i><br>Kullanıcılar
                    </a>
                    <?php endif; ?>
                    
                    <a href="logout.php?logOut=true" class="btn btn-danger btn-lg" style="margin: 5px;">
                        <i class="glyphicon glyphicon-log-out"></i><br>Çıkış
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Son İşlemler -->
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Son İşlemler</h3>
                </div>
                <div class="panel-body">
                    <p class="text-muted">Son işlemler burada görüntülenecek...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require "view/_footer.php"; ?>
