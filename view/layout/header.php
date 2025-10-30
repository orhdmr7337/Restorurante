<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Restaurant ERP' ?></title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f7fa; }
        
        /* Sidebar */
        .sidebar { position: fixed; left: 0; top: 0; bottom: 0; width: 260px; background: linear-gradient(180deg, #2c3e50 0%, #34495e 100%); color: white; overflow-y: auto; z-index: 1000; }
        .sidebar-header { padding: 25px 20px; background: rgba(0,0,0,0.2); border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar-header h3 { margin: 0; font-size: 20px; font-weight: 700; }
        .sidebar-header small { opacity: 0.8; font-size: 12px; }
        .sidebar-menu { list-style: none; padding: 15px 0; }
        .sidebar-menu li { margin: 5px 0; }
        .sidebar-menu a { display: flex; align-items: center; padding: 12px 20px; color: rgba(255,255,255,0.8); text-decoration: none; transition: all 0.3s; }
        .sidebar-menu a:hover { background: rgba(255,255,255,0.1); color: white; }
        .sidebar-menu a.active { background: linear-gradient(90deg, #3498db 0%, #2980b9 100%); color: white; }
        .sidebar-menu a i { width: 25px; font-size: 16px; }
        .sidebar-menu .badge { margin-left: auto; background: #e74c3c; padding: 3px 8px; border-radius: 10px; font-size: 11px; }
        
        /* Main Content */
        .main-content { margin-left: 260px; min-height: 100vh; }
        
        /* Top Bar */
        .top-bar { background: white; padding: 20px 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); display: flex; justify-content: space-between; align-items: center; }
        .top-bar h1 { margin: 0; font-size: 24px; color: #2c3e50; }
        .top-bar .user-info { display: flex; align-items: center; gap: 15px; }
        .top-bar .user-avatar { width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; }
        
        /* Content Area */
        .content-area { padding: 30px; }
        
        /* Cards */
        .card { background: white; border-radius: 12px; padding: 25px; box-shadow: 0 2px 15px rgba(0,0,0,0.08); margin-bottom: 25px; }
        .card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #ecf0f1; }
        .card-header h3 { margin: 0; font-size: 18px; color: #2c3e50; }
        
        /* Buttons */
        .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 10px 20px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.3s; text-decoration: none; display: inline-block; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4); color: white; text-decoration: none; }
        .btn-success { background: linear-gradient(135deg, #56ab2f 0%, #a8e063 100%); color: white; padding: 10px 20px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.3s; text-decoration: none; display: inline-block; }
        .btn-success:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(86, 171, 47, 0.4); color: white; text-decoration: none; }
        .btn-danger { background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%); color: white; padding: 10px 20px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.3s; text-decoration: none; display: inline-block; }
        .btn-warning { background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%); color: white; padding: 10px 20px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.3s; text-decoration: none; display: inline-block; }
        
        /* Stats Grid */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 2px 15px rgba(0,0,0,0.08); text-align: center; transition: all 0.3s; }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 5px 25px rgba(0,0,0,0.12); }
        .stat-card .icon { font-size: 48px; margin-bottom: 15px; }
        .stat-card h3 { font-size: 14px; color: #7f8c8d; margin: 10px 0; }
        .stat-card .value { font-size: 32px; font-weight: 700; color: #2c3e50; }
        
        /* Table */
        .table { width: 100%; border-collapse: collapse; }
        .table th { background: #f8f9fa; padding: 12px; text-align: left; font-weight: 600; color: #2c3e50; border-bottom: 2px solid #ecf0f1; }
        .table td { padding: 12px; border-bottom: 1px solid #ecf0f1; }
        .table tr:hover { background: #f8f9fa; }
        
        /* Modal */
        .modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 9999; display: flex; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.3s; }
        .modal-overlay.active { opacity: 1; }
        .modal-container { background: white; border-radius: 12px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); max-height: 90vh; overflow-y: auto; transform: scale(0.9); transition: transform 0.3s; }
        .modal-overlay.active .modal-container { transform: scale(1); }
        .modal-header { padding: 20px 25px; border-bottom: 2px solid #ecf0f1; display: flex; justify-content: space-between; align-items: center; }
        .modal-header h3 { margin: 0; font-size: 20px; color: #2c3e50; }
        .modal-close { background: none; border: none; font-size: 24px; color: #7f8c8d; cursor: pointer; padding: 0; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; border-radius: 50%; transition: all 0.3s; }
        .modal-close:hover { background: #ecf0f1; color: #2c3e50; }
        .modal-body { padding: 25px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #2c3e50; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 12px; border: 2px solid #ecf0f1; border-radius: 8px; font-size: 14px; transition: all 0.3s; }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus { outline: none; border-color: #3498db; }
        .modal-footer { padding: 20px 25px; border-top: 2px solid #ecf0f1; display: flex; gap: 10px; justify-content: flex-end; }
        
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar { width: 0; transform: translateX(-100%); }
            .main-content { margin-left: 0; }
            .stats-grid { grid-template-columns: 1fr; }
            .modal-container { max-width: 95% !important; }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h3>🍽️ Restaurant ERP</h3>
            <small>v2.0.0</small>
        </div>
        <ul class="sidebar-menu">
            <li><a href="admin.php" class="<?= $activePage == 'dashboard' ? 'active' : '' ?>"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="index.php" class="<?= $activePage == 'tables' ? 'active' : '' ?>"><i class="fa fa-th"></i> Masalar</a></li>
            <li><a href="pos.php" class="<?= $activePage == 'pos' ? 'active' : '' ?>"><i class="fa fa-calculator"></i> POS / Kasa</a></li>
            <li><a href="products.php" class="<?= $activePage == 'products' ? 'active' : '' ?>"><i class="fa fa-cutlery"></i> Ürünler</a></li>
            <li><a href="categories.php" class="<?= $activePage == 'categories' ? 'active' : '' ?>"><i class="fa fa-list"></i> Kategoriler</a></li>
            <li><a href="materials.php" class="<?= $activePage == 'materials' ? 'active' : '' ?>"><i class="fa fa-cubes"></i> Stok <span class="badge"><?= $lowStockCount ?? 0 ?></span></a></li>
            <li><a href="suppliers.php" class="<?= $activePage == 'suppliers' ? 'active' : '' ?>"><i class="fa fa-truck"></i> Tedarikçiler</a></li>
            <li><a href="purchases.php" class="<?= $activePage == 'purchases' ? 'active' : '' ?>"><i class="fa fa-file-text"></i> Alış Faturaları</a></li>
            <li><a href="accounts.php" class="<?= $activePage == 'accounts' ? 'active' : '' ?>"><i class="fa fa-book"></i> Cari Hesaplar</a></li>
            <li><a href="finance.php" class="<?= $activePage == 'finance' ? 'active' : '' ?>"><i class="fa fa-money"></i> Muhasebe</a></li>
            <li><a href="reports.php" class="<?= $activePage == 'reports' ? 'active' : '' ?>"><i class="fa fa-bar-chart"></i> Raporlar</a></li>
            <li><a href="userList.php" class="<?= $activePage == 'users' ? 'active' : '' ?>"><i class="fa fa-users"></i> Personel</a></li>
            <li><a href="settings.php" class="<?= $activePage == 'settings' ? 'active' : '' ?>"><i class="fa fa-cog"></i> Ayarlar</a></li>
            <li><a href="logout.php"><i class="fa fa-sign-out"></i> Çıkış Yap</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Bar -->
        <div class="top-bar">
            <h1><?= $pageTitle ?? 'Dashboard' ?></h1>
            <div class="user-info">
                <div>
                    <strong><?= htmlspecialchars($userInfo['fullname'] ?? 'Kullanıcı') ?></strong><br>
                    <small style="color: #7f8c8d;"><?= ['1' => 'Admin', '2' => 'Yetkili', '3' => 'Garson'][$userInfo['user_position'] ?? 3] ?></small>
                </div>
                <div class="user-avatar"><?= strtoupper(substr($userInfo['fullname'] ?? 'U', 0, 1)) ?></div>
            </div>
        </div>

        <!-- Content Area -->
        <div class="content-area">
