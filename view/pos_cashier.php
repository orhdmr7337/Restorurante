<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kasiyer POS - Restaurant ERP</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); overflow: hidden; }
        
        .pos-container { display: grid; grid-template-columns: 1fr 450px; height: 100vh; }
        
        /* Sol Panel - Masalar */
        .left-panel { background: #f5f5f5; display: flex; flex-direction: column; }
        .pos-header { background: linear-gradient(135deg, #e91e63 0%, #c2185b 100%); color: white; padding: 20px 25px; display: flex; justify-content: space-between; align-items: center; }
        .pos-header h2 { font-size: 24px; margin: 0; }
        .pos-header .info { font-size: 13px; opacity: 0.9; }
        
        .filter-tabs { display: flex; gap: 10px; padding: 15px; background: white; }
        .filter-tab { flex: 1; padding: 12px; background: #f5f5f5; border-radius: 8px; cursor: pointer; text-align: center; font-weight: 600; transition: all 0.3s; }
        .filter-tab.active { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        
        .tables-grid { flex: 1; overflow-y: auto; padding: 20px; display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 15px; }
        .table-card { background: white; border-radius: 12px; padding: 20px; text-align: center; cursor: pointer; transition: all 0.3s; box-shadow: 0 2px 10px rgba(0,0,0,0.1); position: relative; }
        .table-card:hover { transform: translateY(-5px); box-shadow: 0 5px 20px rgba(0,0,0,0.2); }
        .table-card.empty { border: 2px dashed #ddd; }
        .table-card.occupied { background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%); border: 2px solid #ff9800; }
        .table-icon { font-size: 48px; margin-bottom: 10px; }
        .table-card.empty .table-icon { color: #bbb; }
        .table-card.occupied .table-icon { color: #ff9800; }
        .table-name { font-size: 18px; font-weight: 700; margin-bottom: 5px; }
        .table-status { font-size: 13px; color: #666; margin-bottom: 10px; }
        .table-amount { font-size: 20px; font-weight: 700; color: #e91e63; margin-top: 10px; }
        .table-time { font-size: 12px; color: #999; }
        .table-actions { margin-top: 10px; display: flex; gap: 5px; justify-content: center; }
        .table-action-btn { padding: 5px 10px; background: #667eea; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 11px; }
        
        /* Sağ Panel - Hesap Detayı */
        .right-panel { background: white; display: flex; flex-direction: column; box-shadow: -5px 0 20px rgba(0,0,0,0.1); }
        .detail-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; }
        .detail-header h3 { margin: 0 0 5px 0; font-size: 22px; }
        .detail-header .table-info { font-size: 14px; opacity: 0.9; }
        
        .order-items { flex: 1; overflow-y: auto; padding: 20px; }
        .order-item { background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 10px; }
        .order-item-header { display: flex; justify-content: space-between; margin-bottom: 8px; }
        .order-item-name { font-weight: 600; }
        .order-item-price { color: #e91e63; font-weight: 600; }
        .order-item-footer { display: flex; justify-content: space-between; font-size: 13px; color: #666; }
        
        .order-summary { background: #f8f9fa; padding: 20px; }
        .summary-row { display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 16px; }
        .summary-row.total { font-size: 28px; font-weight: 700; padding-top: 15px; border-top: 2px solid #ddd; color: #e91e63; }
        
        .payment-actions { padding: 20px; background: white; border-top: 2px solid #f0f0f0; }
        .action-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 10px; }
        .action-btn { padding: 12px; background: #f5f5f5; border: 2px solid #e0e0e0; border-radius: 8px; cursor: pointer; font-weight: 600; transition: all 0.3s; text-align: center; }
        .action-btn:hover { background: #e0e0e0; }
        .action-btn i { display: block; font-size: 20px; margin-bottom: 5px; }
        .btn-pay { width: 100%; padding: 18px; background: linear-gradient(135deg, #4caf50 0%, #45a049 100%); color: white; border: none; border-radius: 10px; font-size: 18px; font-weight: 700; cursor: pointer; margin-top: 10px; }
        .btn-pay:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(76, 175, 80, 0.4); }
        
        .empty-selection { text-align: center; padding: 60px 20px; color: #999; }
        .empty-selection i { font-size: 80px; margin-bottom: 20px; opacity: 0.3; }
    </style>
</head>
<body>
    <div class="pos-container">
        <!-- Sol Panel - Masalar -->
        <div class="left-panel">
            <div class="pos-header">
                <div>
                    <h2><i class="fa fa-calculator"></i> KASİYER POS</h2>
                    <div class="info">Kasiyer: <?= htmlspecialchars($userInfo['fullname']) ?> | <?= date('d.m.Y H:i') ?></div>
                </div>
                <a href="admin.php" style="color: white; text-decoration: none; font-size: 24px;">
                    <i class="fa fa-times"></i>
                </a>
            </div>
            
            <div class="filter-tabs">
                <div class="filter-tab active" onclick="filterTables('all')">
                    <i class="fa fa-th"></i> Tümü
                </div>
                <div class="filter-tab" onclick="filterTables('occupied')">
                    <i class="fa fa-users"></i> Dolu Masalar
                </div>
                <div class="filter-tab" onclick="filterTables('empty')">
                    <i class="fa fa-check-circle"></i> Boş Masalar
                </div>
            </div>
            
            <div class="tables-grid" id="tablesGrid">
                <?php foreach($allTables as $table): ?>
                    <?php 
                    $isOccupied = $table['status'] == 1;
                    $tableOrders = [];
                    $totalAmount = 0;
                    
                    if ($isOccupied) {
                        // Masanın siparişlerini al
                        $tableOrders = $orderObj->getOrdersByTable($table['id']);
                        foreach ($tableOrders as $item) {
                            $totalAmount += $item['product_price'] * 1;
                        }
                    }
                    ?>
                    <div class="table-card <?= $isOccupied ? 'occupied' : 'empty' ?>" 
                         data-status="<?= $isOccupied ? 'occupied' : 'empty' ?>"
                         onclick="selectTable(<?= $table['id'] ?>, '<?= htmlspecialchars($table['name']) ?>')">
                        <div class="table-icon">
                            <i class="fa fa-<?= $isOccupied ? 'users' : 'check-circle' ?>"></i>
                        </div>
                        <div class="table-name">Masa <?= htmlspecialchars($table['name']) ?></div>
                        <div class="table-status"><?= $isOccupied ? 'Dolu' : 'Boş' ?></div>
                        
                        <?php if ($isOccupied): ?>
                            <div class="table-amount"><?= number_format($totalAmount, 2) ?> TL</div>
                            <div class="table-time">
                                <i class="fa fa-clock-o"></i> 45 dk
                            </div>
                            <div class="table-actions" onclick="event.stopPropagation()">
                                <button class="table-action-btn" onclick="moveTable(<?= $table['id'] ?>)">
                                    <i class="fa fa-arrows"></i> Taşı
                                </button>
                                <button class="table-action-btn" onclick="mergeTable(<?= $table['id'] ?>)">
                                    <i class="fa fa-compress"></i> Birleştir
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Sağ Panel - Hesap Detayı -->
        <div class="right-panel">
            <div id="detailPanel">
                <div class="empty-selection">
                    <i class="fa fa-hand-pointer-o"></i>
                    <h3>Masa Seçin</h3>
                    <p>Hesap detaylarını görmek için<br>soldan bir masa seçin</p>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/modal.js"></script>
    <script>
        let selectedTableId = null;
        let selectedTableName = '';
        
        function filterTables(type) {
            document.querySelectorAll('.filter-tab').forEach(tab => tab.classList.remove('active'));
            event.target.classList.add('active');
            
            const cards = document.querySelectorAll('.table-card');
            cards.forEach(card => {
                const status = card.getAttribute('data-status');
                if (type === 'all' || status === type) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }
        
        function selectTable(tableId, tableName) {
            selectedTableId = tableId;
            selectedTableName = tableName;
            
            // Masa detaylarını yükle
            fetch(`api/get_table_details.php?tableId=${tableId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showTableDetails(data);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }
        
        function showTableDetails(data) {
            const panel = document.getElementById('detailPanel');
            
            let itemsHTML = '';
            let total = 0;
            
            if (data.items && data.items.length > 0) {
                data.items.forEach(item => {
                    const itemTotal = item.product_price * 1;
                    total += itemTotal;
                    itemsHTML += `
                        <div class="order-item">
                            <div class="order-item-header">
                                <span class="order-item-name">${item.product_name}</span>
                                <span class="order-item-price">${itemTotal.toFixed(2)} TL</span>
                            </div>
                            <div class="order-item-footer">
                                <span>1x ${item.product_price} TL</span>
                                <span>${new Date(item.created_at).toLocaleTimeString('tr-TR', {hour: '2-digit', minute: '2-digit'})}</span>
                            </div>
                        </div>
                    `;
                });
            } else {
                itemsHTML = '<div class="empty-selection"><p>Bu masada sipariş yok</p></div>';
            }
            
            const tax = total * 0.18;
            const grandTotal = total + tax;
            
            panel.innerHTML = `
                <div class="detail-header">
                    <h3><i class="fa fa-th"></i> Masa ${selectedTableName}</h3>
                    <div class="table-info">Açılış: ${new Date().toLocaleTimeString('tr-TR')} | Süre: 45 dk</div>
                </div>
                
                <div class="order-items">
                    ${itemsHTML}
                </div>
                
                <div class="order-summary">
                    <div class="summary-row">
                        <span>Ara Toplam:</span>
                        <span>${total.toFixed(2)} TL</span>
                    </div>
                    <div class="summary-row">
                        <span>KDV (%18):</span>
                        <span>${tax.toFixed(2)} TL</span>
                    </div>
                    <div class="summary-row total">
                        <span>TOPLAM:</span>
                        <span>${grandTotal.toFixed(2)} TL</span>
                    </div>
                </div>
                
                <div class="payment-actions">
                    <div class="action-grid">
                        <button class="action-btn" onclick="addDiscount()">
                            <i class="fa fa-percent"></i>
                            İndirim
                        </button>
                        <button class="action-btn" onclick="addNote()">
                            <i class="fa fa-comment"></i>
                            Not Ekle
                        </button>
                        <button class="action-btn" onclick="viewDetails()">
                            <i class="fa fa-eye"></i>
                            Detay
                        </button>
                        <button class="action-btn" onclick="printBill()">
                            <i class="fa fa-print"></i>
                            Yazdır
                        </button>
                    </div>
                    <button class="btn-pay" onclick="showPaymentModal(${grandTotal})">
                        <i class="fa fa-credit-card"></i> ÖDEME AL (${grandTotal.toFixed(2)} TL)
                    </button>
                </div>
            `;
        }
        
        function showPaymentModal(total) {
            const content = `
                <div style="text-align: center; margin-bottom: 20px;">
                    <h2 style="color: #e91e63; font-size: 36px;">${total.toFixed(2)} TL</h2>
                    <p style="color: #666;">Masa ${selectedTableName}</p>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <button class="btn-success" style="padding: 30px; font-size: 18px;" onclick="processPayment('cash', ${total})">
                        <i class="fa fa-money" style="font-size: 32px; display: block; margin-bottom: 10px;"></i>
                        NAKİT
                    </button>
                    <button class="btn-primary" style="padding: 30px; font-size: 18px;" onclick="processPayment('card', ${total})">
                        <i class="fa fa-credit-card" style="font-size: 32px; display: block; margin-bottom: 10px;"></i>
                        KART
                    </button>
                    <button class="btn-warning" style="padding: 30px; font-size: 18px;" onclick="processPayment('bank', ${total})">
                        <i class="fa fa-bank" style="font-size: 32px; display: block; margin-bottom: 10px;"></i>
                        BANKA
                    </button>
                    <button class="btn-danger" style="padding: 30px; font-size: 18px;" onclick="processPayment('debt', ${total})">
                        <i class="fa fa-user" style="font-size: 32px; display: block; margin-bottom: 10px;"></i>
                        VERESİYE
                    </button>
                </div>
            `;
            showModal('<i class="fa fa-credit-card"></i> Ödeme Tipi Seçin', content, 'medium');
        }
        
        function processPayment(type, total) {
            const typeNames = {
                'cash': 'Nakit',
                'card': 'Kredi Kartı',
                'bank': 'Banka Transferi',
                'debt': 'Veresiye'
            };
            
            if (confirm(`${typeNames[type]} ile ${total.toFixed(2)} TL ödeme alınacak. Onaylıyor musunuz?`)) {
                fetch('api/close_table.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({
                        tableId: selectedTableId,
                        paymentType: type,
                        amount: total
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(`✅ Ödeme alındı!\nMasa ${selectedTableName} kapatıldı.`);
                        closeModal();
                        location.reload();
                    } else {
                        alert('Hata: ' + data.message);
                    }
                });
            }
        }
        
        function moveTable(tableId) {
            alert('Masa taşıma işlemi - Masa ID: ' + tableId);
        }
        
        function mergeTable(tableId) {
            alert('Masa birleştirme işlemi - Masa ID: ' + tableId);
        }
        
        function addDiscount() {
            alert('İndirim ekleme');
        }
        
        function addNote() {
            alert('Not ekleme');
        }
        
        function viewDetails() {
            window.location.href = 'table.php?id=' + selectedTableId;
        }
        
        function printBill() {
            alert('Adisyon yazdırma');
        }
    </script>
</body>
</html>
