<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurant ERP - Admin Panel</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f7fa; }
        
        /* Sidebar */
        .sidebar { position: fixed; left: 0; top: 0; width: 260px; height: 100vh; background: linear-gradient(180deg, #2c3e50 0%, #34495e 100%); color: white; overflow-y: auto; z-index: 1000; box-shadow: 2px 0 10px rgba(0,0,0,0.1); }
        .sidebar-header { padding: 25px 20px; background: rgba(0,0,0,0.2); border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar-header h3 { margin: 0; font-size: 22px; font-weight: 600; }
        .sidebar-header small { color: #bdc3c7; font-size: 12px; }
        .sidebar-menu { list-style: none; padding: 0; margin: 20px 0; }
        .sidebar-menu li { margin: 5px 0; }
        .sidebar-menu a { display: flex; align-items: center; padding: 12px 20px; color: #ecf0f1; text-decoration: none; transition: all 0.3s; border-left: 3px solid transparent; }
        .sidebar-menu a:hover, .sidebar-menu a.active { background: rgba(255,255,255,0.1); border-left-color: #3498db; }
        .sidebar-menu i { width: 25px; margin-right: 12px; font-size: 16px; }
        .sidebar-menu .badge { margin-left: auto; background: #e74c3c; padding: 3px 8px; border-radius: 10px; font-size: 11px; }
        
        /* Main Content */
        .main-content { margin-left: 260px; padding: 20px; }
        .top-bar { background: white; padding: 15px 25px; border-radius: 10px; margin-bottom: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); display: flex; justify-content: space-between; align-items: center; }
        .top-bar h1 { margin: 0; font-size: 24px; color: #2c3e50; }
        .top-bar .user-info { display: flex; align-items: center; gap: 15px; }
        .top-bar .user-avatar { width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; }
        
        /* Stats Cards */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 25px; }
        .stat-card { background: white; border-radius: 12px; padding: 25px; box-shadow: 0 2px 15px rgba(0,0,0,0.08); transition: transform 0.3s, box-shadow 0.3s; position: relative; overflow: hidden; }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 5px 25px rgba(0,0,0,0.12); }
        .stat-card::before { content: ''; position: absolute; top: 0; right: 0; width: 100px; height: 100px; background: linear-gradient(135deg, rgba(255,255,255,0.1), rgba(255,255,255,0)); border-radius: 50%; }
        .stat-card .icon { width: 60px; height: 60px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 28px; margin-bottom: 15px; }
        .stat-card.primary .icon { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .stat-card.success .icon { background: linear-gradient(135deg, #56ab2f 0%, #a8e063 100%); color: white; }
        .stat-card.warning .icon { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; }
        .stat-card.danger .icon { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); color: white; }
        .stat-card.info .icon { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; }
        .stat-card h3 { font-size: 14px; color: #7f8c8d; margin-bottom: 8px; font-weight: 500; }
        .stat-card .value { font-size: 32px; font-weight: 700; color: #2c3e50; margin-bottom: 5px; }
        .stat-card .change { font-size: 12px; color: #27ae60; }
        .stat-card .change.negative { color: #e74c3c; }
        
        /* Charts & Tables */
        .panel { background: white; border-radius: 12px; padding: 25px; box-shadow: 0 2px 15px rgba(0,0,0,0.08); margin-bottom: 25px; }
        .panel-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #ecf0f1; }
        .panel-header h2 { margin: 0; font-size: 18px; color: #2c3e50; font-weight: 600; }
        .panel-header .actions { display: flex; gap: 10px; }
        .btn { padding: 8px 16px; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; transition: all 0.3s; text-decoration: none; display: inline-block; }
        .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .btn-success { background: linear-gradient(135deg, #56ab2f 0%, #a8e063 100%); color: white; }
        .btn-sm { padding: 6px 12px; font-size: 12px; }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.2); }
        
        /* Table */
        .table { width: 100%; border-collapse: collapse; }
        .table th { background: #f8f9fa; padding: 12px; text-align: left; font-weight: 600; color: #2c3e50; font-size: 13px; border-bottom: 2px solid #ecf0f1; }
        .table td { padding: 12px; border-bottom: 1px solid #ecf0f1; color: #7f8c8d; }
        .table tr:hover { background: #f8f9fa; }
        .badge { padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 600; }
        .badge-success { background: #d4edda; color: #155724; }
        .badge-warning { background: #fff3cd; color: #856404; }
        .badge-danger { background: #f8d7da; color: #721c24; }
        .badge-info { background: #d1ecf1; color: #0c5460; }
        
        /* Quick Actions */
        .quick-actions { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 25px; }
        .quick-action { background: white; border-radius: 12px; padding: 20px; text-align: center; box-shadow: 0 2px 15px rgba(0,0,0,0.08); transition: all 0.3s; cursor: pointer; text-decoration: none; color: inherit; display: block; }
        .quick-action:hover { transform: translateY(-5px); box-shadow: 0 5px 25px rgba(0,0,0,0.12); }
        .quick-action i { font-size: 36px; margin-bottom: 10px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .quick-action h4 { margin: 0; font-size: 14px; color: #2c3e50; }
        
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar { width: 0; transform: translateX(-100%); }
            .sidebar.active { width: 260px; transform: translateX(0); }
            .main-content { margin-left: 0; }
            .stats-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h3>🍽️ Restaurant ERP</h3>
            <small>Yönetim Paneli</small>
        </div>
        <ul class="sidebar-menu">
            <li><a href="admin.php" class="active"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="index.php"><i class="fa fa-th"></i> Masalar</a></li>
            <li><a href="pos.php"><i class="fa fa-calculator"></i> POS / Kasa</a></li>
            <li><a href="products.php"><i class="fa fa-cutlery"></i> Ürünler</a></li>
            <li><a href="categories.php"><i class="fa fa-list"></i> Kategoriler</a></li>
            <li><a href="materials.php"><i class="fa fa-cubes"></i> Stok Yönetimi <span class="badge"><?= $lowStockCount ?? 0 ?></span></a></li>
            <li><a href="suppliers.php"><i class="fa fa-truck"></i> Tedarikçiler</a></li>
            <li><a href="purchases.php"><i class="fa fa-file-text"></i> Alış Faturaları</a></li>
            <li><a href="accounts.php"><i class="fa fa-book"></i> Cari Hesaplar</a></li>
            <li><a href="finance.php"><i class="fa fa-money"></i> Muhasebe</a></li>
            <li><a href="reports.php"><i class="fa fa-bar-chart"></i> Raporlar</a></li>
            <li><a href="userList.php"><i class="fa fa-users"></i> Personel</a></li>
            <li><a href="settings.php"><i class="fa fa-cog"></i> Ayarlar</a></li>
            <li><a href="logout.php?logOut=true"><i class="fa fa-sign-out"></i> Çıkış Yap</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Bar -->
        <div class="top-bar">
            <h1>Dashboard</h1>
            <div class="user-info">
                <div>
                    <div style="font-size: 14px; font-weight: 600; color: #2c3e50;"><?= htmlspecialchars($userInfo['fullname']) ?></div>
                    <div style="font-size: 12px; color: #7f8c8d;">Admin</div>
                </div>
                <div class="user-avatar"><?= strtoupper(substr($userInfo['fullname'], 0, 1)) ?></div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card primary">
                <div class="icon"><i class="fa fa-shopping-cart"></i></div>
                <h3>Bugünkü Satış</h3>
                <div class="value"><?= number_format($dailyIncome ?? 0, 2) ?> TL</div>
                <div class="change"><i class="fa fa-arrow-up"></i> +12.5% geçen güne göre</div>
            </div>

            <div class="stat-card success">
                <div class="icon"><i class="fa fa-check-circle"></i></div>
                <h3>Toplam Sipariş</h3>
                <div class="value"><?= $orderCount['COUNT(id)'] ?? 0 ?></div>
                <div class="change"><i class="fa fa-arrow-up"></i> +8.2% bu ay</div>
            </div>

            <div class="stat-card warning">
                <div class="icon"><i class="fa fa-cubes"></i></div>
                <h3>Stok Uyarısı</h3>
                <div class="value"><?= $lowStockCount ?? 0 ?></div>
                <div class="change negative"><i class="fa fa-exclamation-triangle"></i> Düşük stok</div>
            </div>

            <div class="stat-card danger">
                <div class="icon"><i class="fa fa-credit-card"></i></div>
                <h3>Toplam Borç</h3>
                <div class="value"><?= number_format($totalDebt ?? 0, 2) ?> TL</div>
                <div class="change negative"><i class="fa fa-arrow-down"></i> Ödenmemiş</div>
            </div>

            <div class="stat-card info">
                <div class="icon"><i class="fa fa-users"></i></div>
                <h3>Aktif Kullanıcı</h3>
                <div class="value"><?= $userCount['COUNT(id)'] ?? 0 ?></div>
                <div class="change"><i class="fa fa-check"></i> Tümü aktif</div>
            </div>

            <div class="stat-card primary">
                <div class="icon"><i class="fa fa-cutlery"></i></div>
                <h3>Toplam Ürün</h3>
                <div class="value"><?= $productCount['COUNT(id)'] ?? 0 ?></div>
                <div class="change"><i class="fa fa-info-circle"></i> <?= $categoryCount['COUNT(id)'] ?? 0 ?> kategori</div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="panel">
            <div class="panel-header">
                <h2>Hızlı İşlemler</h2>
            </div>
            <div class="quick-actions">
                <a href="products.php?action=create" class="quick-action">
                    <i class="fa fa-plus-circle"></i>
                    <h4>Yeni Ürün Ekle</h4>
                </a>
                <a href="materials.php?action=create" class="quick-action">
                    <i class="fa fa-cube"></i>
                    <h4>Malzeme Ekle</h4>
                </a>
                <a href="suppliers.php?action=create" class="quick-action">
                    <i class="fa fa-truck"></i>
                    <h4>Tedarikçi Ekle</h4>
                </a>
                <a href="purchases.php?action=create" class="quick-action">
                    <i class="fa fa-file-text-o"></i>
                    <h4>Alış Faturası</h4>
                </a>
                <a href="finance.php?action=add_income" class="quick-action">
                    <i class="fa fa-arrow-circle-up"></i>
                    <h4>Gelir Ekle</h4>
                </a>
                <a href="finance.php?action=add_expense" class="quick-action">
                    <i class="fa fa-arrow-circle-down"></i>
                    <h4>Gider Ekle</h4>
                </a>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="row">
            <div class="col-md-8">
                <div class="panel">
                    <div class="panel-header">
                        <h2>Son Siparişler</h2>
                        <div class="actions">
                            <a href="#" class="btn btn-sm btn-primary">Tümünü Gör</a>
                        </div>
                    </div>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Sipariş No</th>
                                <th>Masa</th>
                                <th>Tutar</th>
                                <th>Durum</th>
                                <th>Tarih</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>#1001</td>
                                <td>Masa 5</td>
                                <td>125.50 TL</td>
                                <td><span class="badge badge-success">Tamamlandı</span></td>
                                <td>Bugün, 14:30</td>
                            </tr>
                            <tr>
                                <td>#1002</td>
                                <td>Masa 12</td>
                                <td>89.00 TL</td>
                                <td><span class="badge badge-warning">Hazırlanıyor</span></td>
                                <td>Bugün, 14:25</td>
                            </tr>
                            <tr>
                                <td>#1003</td>
                                <td>Masa 3</td>
                                <td>210.75 TL</td>
                                <td><span class="badge badge-info">Serviste</span></td>
                                <td>Bugün, 14:20</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-md-4">
                <div class="panel">
                    <div class="panel-header">
                        <h2>Düşük Stok Uyarıları</h2>
                    </div>
                    <div style="padding: 10px 0;">
                        <div style="padding: 12px; border-left: 3px solid #e74c3c; background: #fff5f5; margin-bottom: 10px; border-radius: 4px;">
                            <div style="font-weight: 600; color: #2c3e50; margin-bottom: 5px;">Un</div>
                            <div style="font-size: 12px; color: #7f8c8d;">Mevcut: 2.5 kg / Min: 10 kg</div>
                        </div>
                        <div style="padding: 12px; border-left: 3px solid #f39c12; background: #fffbf0; margin-bottom: 10px; border-radius: 4px;">
                            <div style="font-weight: 600; color: #2c3e50; margin-bottom: 5px;">Domates</div>
                            <div style="font-size: 12px; color: #7f8c8d;">Mevcut: 8 kg / Min: 15 kg</div>
                        </div>
                        <div style="padding: 12px; border-left: 3px solid #e74c3c; background: #fff5f5; border-radius: 4px;">
                            <div style="font-weight: 600; color: #2c3e50; margin-bottom: 5px;">Süt</div>
                            <div style="font-size: 12px; color: #7f8c8d;">Mevcut: 3 lt / Min: 20 lt</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/jquery-1.7.2.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
</body>
</html>
