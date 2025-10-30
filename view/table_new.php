<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masa <?= $table['name'] ?> - Sipariş</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f7fa; overflow: hidden; height: 100vh; }
        
        /* Top Bar */
        .top-bar { background: white; padding: 15px 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center; }
        .top-bar h2 { margin: 0; color: #2c3e50; font-size: 24px; }
        .btn-back { padding: 10px 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; text-decoration: none; border-radius: 8px; font-weight: 600; transition: all 0.3s; }
        .btn-back:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.2); color: white; text-decoration: none; }
        
        /* Main Container */
        .main-container { display: grid; grid-template-columns: 1fr 400px; gap: 25px; padding: 0 25px; height: calc(100vh - 80px); overflow: hidden; }
        
        /* Menu Section */
        .menu-section { background: white; border-radius: 12px; padding: 25px; box-shadow: 0 2px 15px rgba(0,0,0,0.08); height: 100%; display: flex; flex-direction: column; overflow: hidden; }
        
        /* Category Tabs */
        .category-tabs { display: flex; gap: 10px; margin-bottom: 25px; flex-wrap: wrap; flex-shrink: 0; }
        .category-tab { padding: 15px 25px; background: #f8f9fa; border: 2px solid #ecf0f1; border-radius: 10px; cursor: pointer; transition: all 0.3s; font-weight: 600; color: #7f8c8d; display: flex; align-items: center; gap: 10px; }
        .category-tab:hover { background: #e9ecef; border-color: #dee2e6; }
        .category-tab.active { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-color: #667eea; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3); }
        .category-tab i { font-size: 20px; }
        .category-badge { background: rgba(0,0,0,0.1); padding: 3px 10px; border-radius: 12px; font-size: 12px; }
        .category-tab.active .category-badge { background: rgba(255,255,255,0.3); }
        
        .search-box { margin-bottom: 20px; flex-shrink: 0; }
        .search-box input { width: 100%; padding: 12px 20px; border: 2px solid #ecf0f1; border-radius: 8px; font-size: 14px; transition: all 0.3s; }
        .search-box input:focus { outline: none; border-color: #3498db; }
        
        /* Category Content */
        .category-content { display: none; flex: 1; overflow-y: auto; min-height: 0; }
        .category-content.active { display: block; animation: fadeIn 0.3s; }
        .category-content::-webkit-scrollbar { width: 8px; }
        .category-content::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 10px; }
        .category-content::-webkit-scrollbar-thumb { background: #888; border-radius: 10px; }
        .category-content::-webkit-scrollbar-thumb:hover { background: #555; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        
        /* Product Grid */
        .products-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px; padding-bottom: 20px; }
        
        /* Product Card */
        .product-card { background: white; border: 2px solid #ecf0f1; border-radius: 12px; padding: 15px; text-align: center; cursor: pointer; transition: all 0.3s; position: relative; overflow: hidden; }
        .product-card:hover { border-color: #3498db; box-shadow: 0 5px 20px rgba(52, 152, 219, 0.2); transform: translateY(-5px); }
        
        .product-image { width: 100%; height: 120px; background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); border-radius: 8px; margin-bottom: 12px; display: flex; align-items: center; justify-content: center; font-size: 48px; color: #7f8c8d; }
        .product-name { font-size: 16px; font-weight: 600; color: #2c3e50; margin-bottom: 8px; }
        .product-price { font-size: 18px; font-weight: 700; color: #27ae60; margin-bottom: 12px; }
        .btn-add { width: 100%; padding: 10px; background: linear-gradient(135deg, #56ab2f 0%, #a8e063 100%); color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.3s; }
        .btn-add:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(86, 171, 47, 0.3); }
        
        /* Order Section */
        .order-section { background: white; border-radius: 12px; box-shadow: 0 2px 15px rgba(0,0,0,0.08); height: 100%; display: flex; flex-direction: column; overflow: hidden; }
        .order-header { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 20px; border-radius: 12px 12px 0 0; text-align: center; flex-shrink: 0; }
        .order-header h2 { margin: 0; font-size: 24px; }
        .order-header .table-name { font-size: 32px; font-weight: 700; margin: 10px 0; }
        
        /* Order Items */
        .order-items { flex: 1; overflow-y: auto; padding: 20px; min-height: 0; }
        .order-items::-webkit-scrollbar { width: 8px; }
        .order-items::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 10px; }
        .order-items::-webkit-scrollbar-thumb { background: #888; border-radius: 10px; }
        .order-items::-webkit-scrollbar-thumb:hover { background: #555; }
        .order-item { background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center; }
        .order-item-info { flex: 1; }
        .order-item-name { font-weight: 600; color: #2c3e50; margin-bottom: 5px; }
        .order-item-price { color: #7f8c8d; font-size: 14px; }
        .order-item-actions { display: flex; align-items: center; gap: 10px; }
        .order-item-quantity { background: white; padding: 8px 15px; border-radius: 6px; font-weight: 700; color: #2c3e50; min-width: 40px; text-align: center; }
        .btn-remove { background: #e74c3c; color: white; border: none; width: 32px; height: 32px; border-radius: 6px; cursor: pointer; transition: all 0.3s; }
        .btn-remove:hover { background: #c0392b; transform: scale(1.1); }
        
        /* Order Summary */
        .order-footer { background: white; border-top: 2px solid #ecf0f1; padding: 20px; border-radius: 0 0 12px 12px; flex-shrink: 0; }
        .order-summary { background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 15px; }
        .summary-row { display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 16px; }
        .summary-row.total { font-size: 24px; font-weight: 700; color: #2c3e50; padding-top: 15px; border-top: 2px solid #dee2e6; }
        
        /* Action Buttons */
        .action-buttons { display: grid; gap: 10px; }
        .btn-action { padding: 15px; border: none; border-radius: 8px; font-weight: 600; font-size: 16px; cursor: pointer; transition: all 0.3s; text-decoration: none; display: block; text-align: center; }
        .btn-complete { background: linear-gradient(135deg, #56ab2f 0%, #a8e063 100%); color: white; }
        .btn-complete:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(86, 171, 47, 0.3); color: white; text-decoration: none; }
        .btn-cancel { background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%); color: white; }
        .btn-cancel:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(231, 76, 60, 0.3); color: white; text-decoration: none; }
        
        .empty-order { text-align: center; padding: 40px 20px; color: #7f8c8d; flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; }
        .empty-order i { font-size: 64px; margin-bottom: 15px; opacity: 0.3; }
        
        /* Toast Notification */
        .toast { position: fixed; top: 20px; right: 20px; background: linear-gradient(135deg, #56ab2f 0%, #a8e063 100%); color: white; padding: 15px 25px; border-radius: 10px; box-shadow: 0 5px 20px rgba(0,0,0,0.3); z-index: 10000; display: flex; align-items: center; gap: 15px; transform: translateX(400px); transition: transform 0.3s; }
        .toast.show { transform: translateX(0); }
        .toast i { font-size: 24px; }
        .toast-message { font-weight: 600; font-size: 16px; }
        
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
        
        /* Responsive */
        @media (max-width: 1024px) {
            .main-container { grid-template-columns: 1fr; }
            .order-section { position: relative; top: 0; max-height: none; }
        }
        
        @media (max-width: 768px) {
            .products-grid { grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); }
            .modal-container { max-width: 95% !important; }
        }
    </style>
</head>
<body>
    <!-- Top Bar -->
    <div class="top-bar">
        <h2><i class="fa fa-cutlery"></i> Sipariş Sistemi</h2>
        <a href="index.php" class="btn-back"><i class="fa fa-arrow-left"></i> Masalara Dön</a>
    </div>

    <!-- Main Container -->
    <div class="main-container">
        <!-- Menu Section -->
        <div class="menu-section">
            <!-- Category Tabs -->
            <div class="category-tabs">
                <?php 
                $firstCategory = true;
                foreach($menu as $categoryName => $products): 
                    // Kategori ikonları
                    $icons = [
                        'yiyecek' => 'fa-cutlery',
                        'içecek' => 'fa-glass',
                        'tatlı' => 'fa-birthday-cake'
                    ];
                    $icon = 'fa-circle';
                    foreach ($icons as $key => $value) {
                        if (stripos($categoryName, $key) !== false) {
                            $icon = $value;
                            break;
                        }
                    }
                ?>
                <div class="category-tab <?= $firstCategory ? 'active' : '' ?>" 
                     onclick="showCategory('<?= htmlspecialchars($categoryName) ?>')"
                     data-category="<?= htmlspecialchars($categoryName) ?>">
                    <i class="fa <?= $icon ?>"></i>
                    <span><?= htmlspecialchars($categoryName) ?></span>
                    <span class="category-badge"><?= count($products) ?></span>
                </div>
                <?php 
                    $firstCategory = false;
                endforeach; 
                ?>
            </div>
            
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="🔍 Ürün ara..." onkeyup="filterProducts()">
            </div>
            
            <?php 
            $firstCategory = true;
            foreach($menu as $categoryName => $products): 
            ?>
            <div class="category-content <?= $firstCategory ? 'active' : '' ?>" 
                 id="category-<?= htmlspecialchars($categoryName) ?>"
                 data-category="<?= strtolower($categoryName) ?>">
                
                <div class="products-grid">
                    <?php foreach($products as $product): ?>
                    <div class="product-card" data-product="<?= strtolower($product['name']) ?>">
                        <div class="product-image">
                            <?php
                            // Kategori bazlı ikonlar
                            $icons = [
                                'yiyecek' => 'fa-cutlery',
                                'içecek' => 'fa-glass',
                                'tatlı' => 'fa-birthday-cake',
                                'default' => 'fa-circle'
                            ];
                            $icon = $icons['default'];
                            foreach ($icons as $key => $value) {
                                if (stripos($categoryName, $key) !== false) {
                                    $icon = $value;
                                    break;
                                }
                            }
                            ?>
                            <i class="fa <?= $icon ?>"></i>
                        </div>
                        <div class="product-name"><?= htmlspecialchars($product['name']) ?></div>
                        <div class="product-price"><?= number_format($product['price'], 2) ?> TL</div>
                        <a href="orderTasks.php?task=add&productId=<?= $product['id'] ?>&tableId=<?= $table['id'] ?>" class="btn-add">
                            <i class="fa fa-plus"></i> Ekle
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php 
                $firstCategory = false;
            endforeach; 
            ?>
        </div>

        <!-- Order Section -->
        <div class="order-section">
            <div class="order-header">
                <h2><i class="fa fa-list-alt"></i> Sipariş</h2>
                <div class="table-name">Masa <?= $table['name'] ?></div>
            </div>

            <?php if(!empty($orders)): ?>
            <div class="order-items">
                <?php 
                $total = 0;
                foreach($orders as $order): 
                    $qty = isset($order['total']) ? $order['total'] : 1;
                    $total += $order['product_price'] * $qty;
                ?>
                <div class="order-item">
                    <div class="order-item-info">
                        <div class="order-item-name"><?= htmlspecialchars($order['product_name']) ?></div>
                        <div class="order-item-price">
                            <?php 
                            $qty = isset($order['total']) ? $order['total'] : 1;
                            $itemTotal = $order['product_price'] * $qty;
                            ?>
                            <?= number_format($order['product_price'], 2) ?> TL x <?= $qty ?> = <?= number_format($itemTotal, 2) ?> TL
                        </div>
                    </div>
                    <div class="order-item-actions">
                        <div class="order-item-quantity"><?= $qty ?>x</div>
                        <button class="btn-remove" 
                                onclick="showDeleteItemModal(<?= $order['id'] ?>, '<?= htmlspecialchars($order['product_name']) ?>')">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="order-footer">
            <div class="order-summary">
                <div class="summary-row">
                    <span>Ara Toplam:</span>
                    <span><?= number_format($total, 2) ?> TL</span>
                </div>
                <div class="summary-row">
                    <span>KDV (%18):</span>
                    <span><?= number_format($total * 0.18, 2) ?> TL</span>
                </div>
                <div class="summary-row total">
                    <span>Toplam:</span>
                    <span><?= number_format($total * 1.18, 2) ?> TL</span>
                </div>
            </div>

            <div class="action-buttons">
                <button class="btn-action btn-complete" onclick="showCompleteModal()">
                    <i class="fa fa-check-circle"></i> Siparişi Tamamla
                </button>
                <button class="btn-action btn-cancel" onclick="showCancelModal()">
                    <i class="fa fa-times-circle"></i> Siparişi İptal Et
                </button>
            </div>
            </div>
            <?php else: ?>
            <div class="empty-order">
                <i class="fa fa-shopping-cart"></i>
                <p>Henüz sipariş yok</p>
                <p style="font-size: 14px;">Menüden ürün seçerek sipariş oluşturun</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="assets/js/modal.js"></script>
    <script>
        const tableId = <?= $table['id'] ?>;
        
        // Toast notification göster
        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = 'toast';
            
            const icon = type === 'success' ? 'check-circle' : 'times-circle';
            const gradient = type === 'success' 
                ? 'linear-gradient(135deg, #56ab2f 0%, #a8e063 100%)' 
                : 'linear-gradient(135deg, #e74c3c 0%, #c0392b 100%)';
            
            toast.style.background = gradient;
            toast.innerHTML = `
                <i class="fa fa-${icon}"></i>
                <div class="toast-message">${message}</div>
            `;
            
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.classList.add('show');
            }, 100);
            
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => {
                    toast.remove();
                }, 300);
            }, 3000);
        }
        
        // Sayfa yüklendiğinde başarı mesajını göster
        window.addEventListener('load', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const successMessage = urlParams.get('success');
            
            if (successMessage) {
                showToast(successMessage, 'success');
                
                // URL'den success parametresini temizle
                const newUrl = window.location.pathname + '?id=' + tableId;
                window.history.replaceState({}, '', newUrl);
            }
        });
        
        // Sipariş tamamlama modal
        function showCompleteModal() {
            const tableName = '<?= $table['name'] ?>';
            const totalAmount = <?= number_format($total * 1.18, 2, '.', '') ?>;
            
            const content = `
                <div style="text-align: center; padding: 30px 20px;">
                    <div style="font-size: 80px; color: #27ae60; margin-bottom: 20px; animation: scaleIn 0.5s;">
                        <i class="fa fa-check-circle"></i>
                    </div>
                    <h2 style="font-size: 32px; color: #2c3e50; margin-bottom: 15px;">Siparişi Tamamla</h2>
                    <div style="background: linear-gradient(135deg, #27ae60, #229954); color: white; padding: 25px; border-radius: 12px; margin: 25px 0;">
                        <div style="font-size: 20px; opacity: 0.95; margin-bottom: 10px;">Masa ${tableName}</div>
                        <div style="font-size: 56px; font-weight: 700; margin-bottom: 10px;">${totalAmount.toFixed(2)} TL</div>
                        <div style="font-size: 18px; opacity: 0.95;">KDV Dahil</div>
                    </div>
                    <p style="font-size: 18px; color: #666; margin-bottom: 30px;">Bu siparişi tamamlamak istediğinize emin misiniz?</p>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <button class="btn-danger" onclick="closeModal()" style="padding: 20px; font-size: 18px; border-radius: 10px;">
                            <i class="fa fa-times"></i> İPTAL
                        </button>
                        <button class="btn-success" onclick="completeOrder()" style="padding: 20px; font-size: 18px; border-radius: 10px;">
                            <i class="fa fa-check"></i> ONAYLA
                        </button>
                    </div>
                </div>
                <style>
                    @keyframes scaleIn {
                        from { transform: scale(0); }
                        to { transform: scale(1); }
                    }
                </style>
            `;
            showModal('', content, 'medium');
        }
        
        function completeOrder() {
            console.log('Sipariş tamamlanıyor, tableId:', tableId);
            showToast('Sipariş tamamlanıyor...', 'success');
            window.location.href = 'orderTasks.php?task=complete&tableId=' + tableId;
        }
        
        // Sipariş iptal modal
        function showCancelModal() {
            const tableName = '<?= $table['name'] ?>';
            
            const content = `
                <div style="padding: 20px;">
                    <div style="text-align: center; margin-bottom: 25px;">
                        <div style="font-size: 80px; color: #e74c3c; margin-bottom: 15px;">
                            <i class="fa fa-times-circle"></i>
                        </div>
                        <h2 style="font-size: 28px; color: #2c3e50; margin-bottom: 10px;">Siparişi İptal Et</h2>
                        <p style="font-size: 16px; color: #666;">Masa ${tableName} siparişi iptal edilecek.</p>
                    </div>
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #2c3e50;">İptal Nedeni *</label>
                        <select id="cancelReason" style="width: 100%; padding: 15px; border: 2px solid #ecf0f1; border-radius: 8px; margin-bottom: 15px; font-size: 16px;">
                            <option value="">Neden Seçin</option>
                            <option value="Müşteri İsteği">Müşteri İsteği</option>
                            <option value="Yanlış Sipariş">Yanlış Sipariş</option>
                            <option value="Ürün Bitti">Ürün Bitti</option>
                            <option value="Mutfak Hatası">Mutfak Hatası</option>
                            <option value="Uzun Bekleme">Uzun Bekleme</option>
                            <option value="Diğer">Diğer</option>
                        </select>
                        <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #2c3e50;">Açıklama (İsteğe Bağlı)</label>
                        <textarea id="cancelNote" placeholder="Detaylı açıklama yazın..." 
                                  style="width: 100%; padding: 15px; border: 2px solid #ecf0f1; border-radius: 8px; min-height: 100px; font-size: 16px; resize: vertical;"></textarea>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 25px;">
                        <button class="btn-primary" onclick="closeModal()" style="padding: 18px; font-size: 16px; border-radius: 10px;">
                            <i class="fa fa-arrow-left"></i> GERİ
                        </button>
                        <button class="btn-danger" onclick="cancelOrder()" style="padding: 18px; font-size: 16px; border-radius: 10px;">
                            <i class="fa fa-times"></i> İPTAL ET
                        </button>
                    </div>
                </div>
            `;
            showModal('', content, 'medium');
        }
        
        function cancelOrder() {
            const reason = document.getElementById('cancelReason').value;
            const note = document.getElementById('cancelNote').value;
            
            if (!reason) {
                alert('⚠️ Lütfen iptal nedeni seçin!');
                return;
            }
            
            console.log('Sipariş iptal ediliyor:', {tableId, reason, note});
            showToast('Sipariş iptal ediliyor...', 'success');
            
            window.location.href = 'orderTasks.php?task=cancel&tableId=' + tableId + 
                                   '&reason=' + encodeURIComponent(reason) + 
                                   '&note=' + encodeURIComponent(note);
        }
        
        // Ürün silme modal
        function showDeleteItemModal(orderId, productName) {
            const content = `
                <div style="text-align: center; margin-bottom: 25px;">
                    <div style="font-size: 64px; color: #e74c3c; margin-bottom: 15px;">
                        <i class="fa fa-trash"></i>
                    </div>
                    <h3 style="margin-bottom: 10px;">Ürünü Sil</h3>
                    <p style="color: #666;"><strong>${productName}</strong> silinecek.</p>
                </div>
                <div class="form-group">
                    <label>Silme Nedeni</label>
                    <select id="deleteReason" style="width: 100%; padding: 12px; border: 2px solid #ecf0f1; border-radius: 8px;">
                        <option value="Müşteri İsteği">Müşteri İsteği</option>
                        <option value="Yanlış Eklendi">Yanlış Eklendi</option>
                        <option value="Ürün Bitti">Ürün Bitti</option>
                        <option value="Diğer">Diğer</option>
                    </select>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 20px;">
                    <button class="btn-primary" onclick="closeModal()" style="padding: 15px;">
                        <i class="fa fa-arrow-left"></i> İptal
                    </button>
                    <button class="btn-danger" onclick="deleteItem(${orderId})" style="padding: 15px;">
                        <i class="fa fa-trash"></i> Sil
                    </button>
                </div>
            `;
            showModal('<i class="fa fa-trash"></i> Ürün Sil', content, 'small');
        }
        
        function deleteItem(orderId) {
            const reason = document.getElementById('deleteReason').value;
            window.location.href = 'orderTasks.php?task=delete&orderId=' + orderId + 
                                   '&tableId=' + tableId + 
                                   '&reason=' + encodeURIComponent(reason);
        }
        
        // Kategori değiştirme
        function showCategory(categoryName) {
            // Tüm tab'ları pasif yap
            document.querySelectorAll('.category-tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Tıklanan tab'ı aktif yap
            document.querySelectorAll('.category-tab').forEach(tab => {
                if (tab.getAttribute('data-category') === categoryName) {
                    tab.classList.add('active');
                }
            });
            
            // Tüm içerikleri gizle
            document.querySelectorAll('.category-content').forEach(content => {
                content.classList.remove('active');
            });
            
            // Seçilen kategoriyi göster
            const selectedCategory = document.getElementById('category-' + categoryName);
            if (selectedCategory) {
                selectedCategory.classList.add('active');
            }
            
            // Arama kutusunu temizle
            document.getElementById('searchInput').value = '';
        }
        
        // Ürün arama
        function filterProducts() {
            const searchValue = document.getElementById('searchInput').value.toLowerCase();
            const activeCategory = document.querySelector('.category-content.active');
            
            if (!activeCategory) return;
            
            const products = activeCategory.querySelectorAll('.product-card');
            
            products.forEach(product => {
                const productName = product.getAttribute('data-product');
                if (productName.includes(searchValue)) {
                    product.style.display = 'block';
                } else {
                    product.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>
