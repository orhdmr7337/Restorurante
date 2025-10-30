<?php
// Kullanıcı bilgilerini al
$userId = $_SESSION['user_session'] ?? null;
if ($userId) {
    $userInfo = $usrObj->getOneUser($userId);
    $userPosition = $userInfo['user_position'] ?? 3;
} else {
    redirect('login.php');
}
?>

<nav class="navbar navbar-default navbar-fixed-top">
    <div class="container-fluid">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-collapse">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="index.php">🍽️ Restaurant ERP</a>
        </div>

        <div class="collapse navbar-collapse" id="navbar-collapse">
            <ul class="nav navbar-nav">
                <li><a href="index.php"><i class="glyphicon glyphicon-th"></i> Masalar</a></li>
                
                <?php if ($userPosition == 1 || $userPosition == 2): ?>
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <i class="glyphicon glyphicon-cutlery"></i> Menü <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="products.php">Ürünler</a></li>
                        <li><a href="categories.php">Kategoriler</a></li>
                    </ul>
                </li>
                
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <i class="glyphicon glyphicon-list-alt"></i> Stok <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="materials.php">Malzemeler</a></li>
                        <li><a href="materials.php?action=stock_movement">Stok Hareketi</a></li>
                    </ul>
                </li>
                
                <li><a href="suppliers.php"><i class="glyphicon glyphicon-briefcase"></i> Tedarikçiler</a></li>
                <li><a href="purchases.php"><i class="glyphicon glyphicon-file"></i> Alış Faturaları</a></li>
                <li><a href="finance.php"><i class="glyphicon glyphicon-usd"></i> Muhasebe</a></li>
                <?php endif; ?>
                
                <?php if ($userPosition == 1): ?>
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <i class="glyphicon glyphicon-cog"></i> Yönetim <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="admin.php">Admin Paneli</a></li>
                        <li><a href="userList.php">Kullanıcılar</a></li>
                        <li role="separator" class="divider"></li>
                        <li><a href="settings.php">Ayarlar</a></li>
                    </ul>
                </li>
                <?php endif; ?>
            </ul>

            <ul class="nav navbar-nav navbar-right">
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <i class="glyphicon glyphicon-user"></i> <?= htmlspecialchars($userInfo['fullname']) ?> <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="dashboard.php">Dashboard</a></li>
                        <li role="separator" class="divider"></li>
                        <li><a href="logout.php?logOut=true"><i class="glyphicon glyphicon-log-out"></i> Çıkış Yap</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div style="margin-top: 70px;"></div> <!-- Navbar için boşluk -->
