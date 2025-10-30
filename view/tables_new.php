<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masalar - Restaurant ERP</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f7fa; }
        
        /* Top Bar */
        .top-bar { background: white; padding: 15px 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 25px; }
        .top-bar .user-info { display: flex; justify-content: space-between; align-items: center; }
        .top-bar h2 { margin: 0; color: #2c3e50; font-size: 24px; }
        .top-bar .actions { display: flex; gap: 10px; flex-wrap: wrap; }
        .btn-custom { padding: 8px 16px; border-radius: 6px; text-decoration: none; font-size: 14px; font-weight: 500; transition: all 0.3s; display: inline-block; }
        .btn-admin { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .btn-products { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; }
        .btn-stock { background: linear-gradient(135deg, #56ab2f 0%, #a8e063 100%); color: white; }
        .btn-suppliers { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; }
        .btn-finance { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); color: white; }
        .btn-logout { background: #e74c3c; color: white; }
        .btn-custom:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.2); color: white; text-decoration: none; }
        
        /* Stats Cards */
        .stats-container { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 0 25px 25px 25px; }
        .stat-card { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); text-align: center; }
        .stat-card .icon { font-size: 36px; margin-bottom: 10px; }
        .stat-card.empty .icon { color: #27ae60; }
        .stat-card.occupied .icon { color: #e74c3c; }
        .stat-card.total .icon { color: #3498db; }
        .stat-card h3 { font-size: 14px; color: #7f8c8d; margin: 10px 0 5px 0; }
        .stat-card .value { font-size: 32px; font-weight: 700; color: #2c3e50; }
        
        /* Floor Sections */
        .floor-section { margin: 25px; background: white; border-radius: 12px; padding: 25px; box-shadow: 0 2px 15px rgba(0,0,0,0.08); }
        .floor-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #ecf0f1; }
        .floor-header h2 { margin: 0; color: #2c3e50; font-size: 20px; }
        .floor-header .floor-stats { display: flex; gap: 20px; font-size: 14px; color: #7f8c8d; }
        
        /* Table Grid */
        .tables-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 15px; }
        
        /* Table Card */
        .table-card { background: white; border: 2px solid #ecf0f1; border-radius: 12px; padding: 20px; text-align: center; cursor: pointer; transition: all 0.3s; position: relative; overflow: hidden; text-decoration: none; display: block; }
        .table-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 4px; }
        .table-card.empty { border-color: #27ae60; }
        .table-card.empty::before { background: linear-gradient(90deg, #27ae60, #2ecc71); }
        .table-card.empty:hover { border-color: #27ae60; box-shadow: 0 5px 20px rgba(39, 174, 96, 0.3); transform: translateY(-5px); }
        .table-card.occupied { border-color: #e74c3c; background: linear-gradient(135deg, #fff5f5 0%, #ffffff 100%); }
        .table-card.occupied::before { background: linear-gradient(90deg, #e74c3c, #c0392b); }
        .table-card.occupied:hover { border-color: #e74c3c; box-shadow: 0 5px 20px rgba(231, 76, 60, 0.3); transform: translateY(-5px); }
        
        .table-icon { font-size: 48px; margin-bottom: 10px; }
        .table-card.empty .table-icon { color: #27ae60; }
        .table-card.occupied .table-icon { color: #e74c3c; }
        
        .table-name { font-size: 18px; font-weight: 700; color: #2c3e50; margin-bottom: 5px; }
        .table-status { font-size: 12px; padding: 4px 12px; border-radius: 12px; display: inline-block; font-weight: 600; }
        .table-card.empty .table-status { background: #d4edda; color: #155724; }
        .table-card.occupied .table-status { background: #f8d7da; color: #721c24; }
        
        /* Legend */
        .legend { display: flex; justify-content: center; gap: 30px; margin: 25px; padding: 15px; background: white; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); }
        .legend-item { display: flex; align-items: center; gap: 10px; }
        .legend-color { width: 20px; height: 20px; border-radius: 4px; }
        .legend-color.empty { background: #27ae60; }
        .legend-color.occupied { background: #e74c3c; }
        
        /* Responsive */
        @media (max-width: 768px) {
            .top-bar .actions { width: 100%; margin-top: 10px; }
            .stats-container { grid-template-columns: 1fr; }
            .tables-grid { grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); }
        }
    </style>
</head>
<body>
    <?php
    // Kullanıcı bilgilerini al
    $userId = $_SESSION['user_session'];
    $userInfo = $usrObj->getOneUser($userId);
    ?>
    
    <!-- Top Bar -->
    <div class="top-bar">
        <div class="user-info">
            <h2>🍽️ Masalar</h2>
            <div>
                <span style="color: #7f8c8d;">Hoşgeldin, <strong><?= htmlspecialchars($userInfo['fullname']) ?></strong></span>
            </div>
        </div>
        <div class="actions" style="margin-top: 15px;">
            <?php if($userInfo["user_position"] == 1): ?>
                <a href="admin.php" class="btn-custom btn-admin"><i class="fa fa-dashboard"></i> Admin Panel</a>
                <a href="products.php" class="btn-custom btn-products"><i class="fa fa-cutlery"></i> Ürünler</a>
                <a href="materials.php" class="btn-custom btn-stock"><i class="fa fa-cubes"></i> Stok</a>
                <a href="suppliers.php" class="btn-custom btn-suppliers"><i class="fa fa-truck"></i> Tedarikçiler</a>
                <a href="finance.php" class="btn-custom btn-finance"><i class="fa fa-money"></i> Muhasebe</a>
            <?php elseif($userInfo["user_position"] == 2): ?>
                <a href="products.php" class="btn-custom btn-products"><i class="fa fa-cutlery"></i> Ürünler</a>
                <a href="materials.php" class="btn-custom btn-stock"><i class="fa fa-cubes"></i> Stok</a>
                <a href="suppliers.php" class="btn-custom btn-suppliers"><i class="fa fa-truck"></i> Tedarikçiler</a>
                <a href="finance.php" class="btn-custom btn-finance"><i class="fa fa-money"></i> Muhasebe</a>
            <?php endif; ?>
            <a href="logout.php?logOut=true" class="btn-custom btn-logout"><i class="fa fa-sign-out"></i> Çıkış</a>
        </div>
    </div>

    <?php
    // Masa istatistikleri
    $allTablesResult = $tblObj->getAllTables();
    $allTables = [];
    foreach ($allTablesResult as $table) {
        $allTables[] = $table;
    }
    $totalTables = count($allTables);
    $occupiedTables = 0;
    $emptyTables = 0;
    
    foreach ($allTables as $table) {
        if ($table['status'] == 1) {
            $occupiedTables++;
        } else {
            $emptyTables++;
        }
    }
    ?>

    <!-- Stats Cards -->
    <div class="stats-container">
        <div class="stat-card total">
            <div class="icon"><i class="fa fa-th"></i></div>
            <h3>Toplam Masa</h3>
            <div class="value"><?= $totalTables ?></div>
        </div>
        <div class="stat-card empty">
            <div class="icon"><i class="fa fa-check-circle"></i></div>
            <h3>Boş Masa</h3>
            <div class="value"><?= $emptyTables ?></div>
        </div>
        <div class="stat-card occupied">
            <div class="icon"><i class="fa fa-users"></i></div>
            <h3>Dolu Masa</h3>
            <div class="value"><?= $occupiedTables ?></div>
        </div>
    </div>

    <!-- Legend -->
    <div class="legend">
        <div class="legend-item">
            <div class="legend-color empty"></div>
            <span>Boş Masa</span>
        </div>
        <div class="legend-item">
            <div class="legend-color occupied"></div>
            <span>Dolu Masa</span>
        </div>
    </div>

    <?php
    // Masaları kata göre grupla
    $floors = [
        'Zemin Kat' => range(1, 10),
        '1. Kat' => range(11, 20)
    ];
    
    foreach ($floors as $floorName => $floorTables):
        $floorOccupied = 0;
        $floorEmpty = 0;
        
        // Bu kattaki masa durumlarını say
        foreach ($allTables as $table) {
            if (in_array($table['id'], $floorTables)) {
                if ($table['status'] == 1) {
                    $floorOccupied++;
                } else {
                    $floorEmpty++;
                }
            }
        }
    ?>
    
    <!-- Floor Section -->
    <div class="floor-section">
        <div class="floor-header">
            <h2><i class="fa fa-building"></i> <?= $floorName ?></h2>
            <div class="floor-stats">
                <span><i class="fa fa-check-circle" style="color: #27ae60;"></i> <?= $floorEmpty ?> Boş</span>
                <span><i class="fa fa-users" style="color: #e74c3c;"></i> <?= $floorOccupied ?> Dolu</span>
            </div>
        </div>
        
        <div class="tables-grid">
            <?php
            foreach ($allTables as $table):
                if (in_array($table['id'], $floorTables)):
                    $statusClass = $table['status'] == 1 ? 'occupied' : 'empty';
                    $statusText = $table['status'] == 1 ? 'Dolu' : 'Boş';
                    $icon = $table['status'] == 1 ? 'fa-users' : 'fa-check-circle';
            ?>
                <div class="table-card <?= $statusClass ?>" data-table-id="<?= $table['id'] ?>" data-table-status="<?= $table['status'] ?>">
                    <a href="table.php?id=<?= $table['id'] ?>" style="text-decoration: none; color: inherit; display: block;">
                        <div class="table-icon"><i class="fa <?= $icon ?>"></i></div>
                        <div class="table-name">Masa <?= $table['name'] ?></div>
                        <div class="table-status"><?= $statusText ?></div>
                    </a>
                    <?php if($table['status'] == 1): ?>
                    <div style="margin-top: 10px; display: flex; gap: 5px; justify-content: center;">
                        <button class="btn btn-xs btn-warning" onclick="event.preventDefault(); startMove(<?= $table['id'] ?>)" title="Masa Taşı">
                            <i class="fa fa-arrows"></i>
                        </button>
                        <button class="btn btn-xs btn-info" onclick="event.preventDefault(); startMerge(<?= $table['id'] ?>)" title="Masa Birleştir">
                            <i class="fa fa-compress"></i>
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
            <?php
                endif;
            endforeach;
            ?>
        </div>
    </div>
    
    <?php endforeach; ?>

    <script src="assets/js/jquery-1.7.2.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <script>
        let moveMode = false;
        let mergeMode = false;
        let sourceTableId = null;
        
        function startMove(tableId) {
            moveMode = true;
            mergeMode = false;
            sourceTableId = tableId;
            
            // Tüm boş masaları vurgula
            document.querySelectorAll('.table-card.empty').forEach(card => {
                card.style.border = '3px solid #f39c12';
                card.style.cursor = 'pointer';
                card.onclick = function() {
                    const targetId = this.getAttribute('data-table-id');
                    moveTable(sourceTableId, targetId);
                };
            });
            
            alert('Taşımak istediğiniz boş masayı seçin');
        }
        
        function startMerge(tableId) {
            mergeMode = true;
            moveMode = false;
            sourceTableId = tableId;
            
            // Tüm dolu masaları vurgula (kendisi hariç)
            document.querySelectorAll('.table-card.occupied').forEach(card => {
                const cardId = card.getAttribute('data-table-id');
                if(cardId != tableId) {
                    card.style.border = '3px solid #3498db';
                    card.style.cursor = 'pointer';
                    card.onclick = function() {
                        const targetId = this.getAttribute('data-table-id');
                        mergeTables(sourceTableId, targetId);
                    };
                }
            });
            
            alert('Birleştirmek istediğiniz dolu masayı seçin');
        }
        
        function moveTable(sourceId, targetId) {
            if(confirm('Masa ' + sourceId + ' içeriğini Masa ' + targetId + ' taşımak istediğinize emin misiniz?')) {
                window.location.href = 'tableTasks.php?task=move&tableId=' + sourceId + '&targetTableId=' + targetId;
            }
        }
        
        function mergeTables(sourceId, targetId) {
            if(confirm('Masa ' + sourceId + ' ile Masa ' + targetId + ' birleştirmek istediğinize emin misiniz?')) {
                window.location.href = 'tableTasks.php?task=merge&tableId=' + sourceId + '&targetTableId=' + targetId;
            }
        }
    </script>
</body>
</html>
