<?php 
$pageTitle = 'Kasiyer POS';
$activePage = 'pos';
include 'layout/header.php'; 
?>

<style>
    .pos-grid { display: grid; grid-template-columns: 1fr 400px; gap: 20px; height: calc(100vh - 200px); }
    .tables-section { background: white; border-radius: 12px; padding: 20px; overflow-y: auto; }
    .detail-section { background: white; border-radius: 12px; padding: 20px; display: flex; flex-direction: column; }
    
    .filter-tabs { display: flex; gap: 10px; margin-bottom: 20px; }
    .filter-tab { flex: 1; padding: 10px; background: #f5f5f5; border-radius: 8px; cursor: pointer; text-align: center; font-weight: 600; transition: all 0.3s; }
    .filter-tab.active { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
    
    .tables-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 15px; }
    .table-card { background: #f8f9fa; border-radius: 10px; padding: 20px; text-align: center; cursor: pointer; transition: all 0.3s; border: 2px solid #e0e0e0; }
    .table-card:hover { transform: translateY(-3px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
    .table-card.occupied { background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%); border-color: #ff9800; }
    .table-icon { font-size: 40px; margin-bottom: 10px; }
    .table-name { font-size: 16px; font-weight: 700; margin-bottom: 5px; }
    .table-amount { font-size: 18px; font-weight: 700; color: #e91e63; margin-top: 8px; }
    
    .order-items { flex: 1; overflow-y: auto; margin-bottom: 20px; }
    .order-item { background: #f8f9fa; padding: 12px; border-radius: 8px; margin-bottom: 10px; }
    .order-summary { background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 15px; }
    .summary-row { display: flex; justify-content: space-between; margin-bottom: 10px; }
    .summary-row.total { font-size: 20px; font-weight: 700; padding-top: 10px; border-top: 2px solid #ddd; color: #e91e63; }
    
    .empty-state { text-align: center; padding: 40px 20px; color: #999; }
    .empty-state i { font-size: 60px; margin-bottom: 15px; opacity: 0.3; }
</style>

<div class="pos-grid">
    <!-- Sol - Masalar -->
    <div class="tables-section">
        <div class="filter-tabs">
            <div class="filter-tab active" onclick="filterTables('all')">
                <i class="fa fa-th"></i> Tümü
            </div>
            <div class="filter-tab" onclick="filterTables('occupied')">
                <i class="fa fa-users"></i> Dolu
            </div>
            <div class="filter-tab" onclick="filterTables('empty')">
                <i class="fa fa-check-circle"></i> Boş
            </div>
        </div>
        
        <div class="tables-grid">
            <?php foreach($allTables as $table): ?>
                <?php 
                $isOccupied = $table['status'] == 1;
                $totalAmount = 0;
                
                if ($isOccupied) {
                    $tableOrders = $orderObj->getOrdersByTable($table['id']);
                    foreach ($tableOrders as $item) {
                        $totalAmount += $item['product_price'] * 1;
                    }
                }
                ?>
                <div class="table-card <?= $isOccupied ? 'occupied' : '' ?>" 
                     data-status="<?= $isOccupied ? 'occupied' : 'empty' ?>"
                     onclick="selectTable(<?= $table['id'] ?>, '<?= htmlspecialchars($table['name']) ?>')">
                    <div class="table-icon">
                        <i class="fa fa-<?= $isOccupied ? 'users' : 'check-circle' ?>" style="color: <?= $isOccupied ? '#ff9800' : '#bbb' ?>;"></i>
                    </div>
                    <div class="table-name">Masa <?= htmlspecialchars($table['name']) ?></div>
                    <div style="font-size: 12px; color: #666;"><?= $isOccupied ? 'Dolu' : 'Boş' ?></div>
                    <?php if ($isOccupied): ?>
                        <div class="table-amount"><?= number_format($totalAmount, 2) ?> TL</div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Sağ - Hesap Detayı -->
    <div class="detail-section" id="detailPanel">
        <div class="empty-state">
            <i class="fa fa-hand-pointer-o"></i>
            <h3>Masa Seçin</h3>
            <p>Hesap detaylarını görmek için<br>soldan bir masa seçin</p>
        </div>
    </div>
</div>

<script src="assets/js/modal.js"></script>
<script>
    let selectedTableId = null;
    let selectedTableName = '';
    
    function filterTables(type) {
        document.querySelectorAll('.filter-tab').forEach(tab => tab.classList.remove('active'));
        event.target.closest('.filter-tab').classList.add('active');
        
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
        
        console.log('Masa seçildi:', tableId, tableName);
        
        // Tüm masaların seçimini kaldır
        document.querySelectorAll('.table-card').forEach(card => {
            card.style.borderColor = card.classList.contains('occupied') ? '#ff9800' : '#e0e0e0';
        });
        
        // Seçili masayı vurgula
        event.target.closest('.table-card').style.borderColor = '#667eea';
        
        fetch(`api/get_table_details.php?tableId=${tableId}`)
            .then(response => {
                console.log('API Response status:', response.status);
                return response.text().then(text => ({
                    status: response.status,
                    text: text
                }));
            })
            .then(({status, text}) => {
                console.log('API Response text:', text);
                
                if (status !== 200) {
                    alert('API Hatası (500):\n\n' + text);
                    return;
                }
                
                try {
                    const data = JSON.parse(text);
                    if (data.success) {
                        showTableDetails(data);
                    } else {
                        alert('Hata: ' + data.message + '\n\nDetay: ' + JSON.stringify(data));
                    }
                } catch (e) {
                    console.error('JSON parse error:', e);
                    alert('API hatası: Geçersiz JSON\n\nYanıt:\n' + text.substring(0, 500));
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                alert('Bağlantı hatası: ' + error.message);
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
                        <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                            <strong>${item.product_name}</strong>
                            <strong style="color: #e91e63;">${itemTotal.toFixed(2)} TL</strong>
                        </div>
                        <div style="font-size: 12px; color: #666;">1x ${item.product_price} TL</div>
                    </div>
                `;
            });
        } else {
            itemsHTML = '<div class="empty-state"><p>Bu masada sipariş yok</p></div>';
        }
        
        const tax = total * 0.18;
        const grandTotal = total + tax;
        
        panel.innerHTML = `
            <h3 style="margin-bottom: 15px;"><i class="fa fa-th"></i> Masa ${selectedTableName}</h3>
            
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
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                <button class="btn-warning" style="padding: 15px; font-size: 16px; font-weight: 700;" onclick="showSplitPaymentModal(${grandTotal})">
                    <i class="fa fa-users"></i> HESABI BÖL
                </button>
                <button class="btn-success" style="padding: 15px; font-size: 16px; font-weight: 700;" onclick="showPaymentModal(${grandTotal})">
                    <i class="fa fa-credit-card"></i> ÖDEME AL
                </button>
            </div>
        `;
    }
    
    // Hesap bölme modal'ı
    function showSplitPaymentModal(total) {
        const content = `
            <div style="padding: 20px;">
                <div style="text-align: center; margin-bottom: 25px;">
                    <div style="font-size: 60px; color: #ff9800; margin-bottom: 15px;">
                        <i class="fa fa-calculator"></i>
                    </div>
                    <h2 style="font-size: 24px; color: #2c3e50; margin-bottom: 10px;">Hesap Bölme</h2>
                    <div style="background: linear-gradient(135deg, #ff9800 0%, #f57c00 100%); color: white; padding: 15px; border-radius: 10px;">
                        <div style="font-size: 16px; opacity: 0.9;">Toplam Hesap</div>
                        <div style="font-size: 36px; font-weight: 700;" id="splitTotal">${total.toFixed(2)} TL</div>
                    </div>
                </div>
                
                <div style="background: #f8f9fa; padding: 20px; border-radius: 10px; margin-bottom: 20px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                        <label style="font-weight: 600; color: #2c3e50;">Kişi Sayısı:</label>
                        <div style="display: flex; gap: 10px; align-items: center;">
                            <button onclick="changePeopleCount(-1)" style="width: 40px; height: 40px; border-radius: 50%; background: #e74c3c; color: white; border: none; font-size: 20px; cursor: pointer;">-</button>
                            <input type="number" id="peopleCount" value="2" min="1" max="20" onchange="calculateSplit(${total})" style="width: 60px; text-align: center; font-size: 20px; font-weight: 700; border: 2px solid #ddd; border-radius: 8px; padding: 8px;">
                            <button onclick="changePeopleCount(1)" style="width: 40px; height: 40px; border-radius: 50%; background: #4caf50; color: white; border: none; font-size: 20px; cursor: pointer;">+</button>
                        </div>
                    </div>
                    <div style="text-align: center; padding: 15px; background: white; border-radius: 8px;">
                        <div style="font-size: 14px; color: #666; margin-bottom: 5px;">Kişi Başı</div>
                        <div style="font-size: 32px; font-weight: 700; color: #ff9800;" id="perPerson">${(total / 2).toFixed(2)} TL</div>
                    </div>
                </div>
                
                <div style="background: #e8f5e9; padding: 20px; border-radius: 10px; margin-bottom: 20px;">
                    <h3 style="margin: 0 0 15px 0; color: #2c3e50; font-size: 18px;">
                        <i class="fa fa-money"></i> Ödeme Takibi
                    </h3>
                    <div id="paymentsList" style="margin-bottom: 15px;">
                        <!-- Ödemeler buraya eklenecek -->
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr auto; gap: 10px;">
                        <input type="number" id="paymentAmount" placeholder="Ödeme tutarı girin..." style="padding: 12px; border: 2px solid #ddd; border-radius: 8px; font-size: 16px;">
                        <button onclick="addPayment(${total})" class="btn-success" style="padding: 12px 20px; border-radius: 8px;">
                            <i class="fa fa-plus"></i> Ekle
                        </button>
                    </div>
                </div>
                
                <div style="background: #fff3e0; padding: 20px; border-radius: 10px; margin-bottom: 20px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                        <span style="font-size: 16px; color: #666;">Toplanan:</span>
                        <span style="font-size: 20px; font-weight: 700; color: #4caf50;" id="totalCollected">0.00 TL</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding-top: 10px; border-top: 2px solid #ddd;">
                        <span style="font-size: 18px; font-weight: 600; color: #2c3e50;">Kalan:</span>
                        <span style="font-size: 24px; font-weight: 700; color: #e74c3c;" id="remaining">${total.toFixed(2)} TL</span>
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <button class="btn-danger" onclick="closeModal()" style="padding: 15px; font-size: 16px; border-radius: 8px;">
                        <i class="fa fa-times"></i> İPTAL
                    </button>
                    <button class="btn-success" onclick="completeSplitPayment(${total})" style="padding: 15px; font-size: 16px; border-radius: 8px;" id="completeBtn" disabled>
                        <i class="fa fa-check"></i> TAMAMLA
                    </button>
                </div>
            </div>
        `;
        showModal('', content, 'large');
        
        // Global değişkenler
        window.splitPayments = [];
        window.splitTotal = total;
    }
    
    function changePeopleCount(change) {
        const input = document.getElementById('peopleCount');
        let count = parseInt(input.value) + change;
        if (count < 1) count = 1;
        if (count > 20) count = 20;
        input.value = count;
        calculateSplit(window.splitTotal);
    }
    
    function calculateSplit(total) {
        const peopleCount = parseInt(document.getElementById('peopleCount').value);
        const perPerson = total / peopleCount;
        document.getElementById('perPerson').textContent = perPerson.toFixed(2) + ' TL';
    }
    
    function addPayment(total) {
        const amountInput = document.getElementById('paymentAmount');
        const amount = parseFloat(amountInput.value);
        
        if (!amount || amount <= 0) {
            alert('Geçerli bir tutar girin!');
            return;
        }
        
        const remaining = parseFloat(document.getElementById('remaining').textContent);
        
        if (amount > remaining) {
            alert('Ödeme tutarı kalan tutardan fazla olamaz!');
            return;
        }
        
        window.splitPayments.push(amount);
        
        // Listeyi güncelle
        updatePaymentsList();
        
        // Toplamları güncelle
        const totalCollected = window.splitPayments.reduce((sum, p) => sum + p, 0);
        document.getElementById('totalCollected').textContent = totalCollected.toFixed(2) + ' TL';
        document.getElementById('remaining').textContent = (total - totalCollected).toFixed(2) + ' TL';
        
        // Tamamla butonunu aktif et
        if (totalCollected >= total) {
            document.getElementById('completeBtn').disabled = false;
            document.getElementById('remaining').style.color = '#4caf50';
        }
        
        // Input'u temizle
        amountInput.value = '';
        amountInput.focus();
    }
    
    function updatePaymentsList() {
        const list = document.getElementById('paymentsList');
        list.innerHTML = window.splitPayments.map((payment, index) => `
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px; background: white; border-radius: 8px; margin-bottom: 8px;">
                <span style="font-weight: 600; color: #2c3e50;">
                    <i class="fa fa-user"></i> Kişi ${index + 1}
                </span>
                <span style="font-weight: 700; color: #4caf50; font-size: 18px;">${payment.toFixed(2)} TL</span>
                <button onclick="removePayment(${index})" style="background: #e74c3c; color: white; border: none; width: 30px; height: 30px; border-radius: 50%; cursor: pointer;">
                    <i class="fa fa-trash"></i>
                </button>
            </div>
        `).join('');
    }
    
    function removePayment(index) {
        window.splitPayments.splice(index, 1);
        updatePaymentsList();
        
        const total = window.splitTotal;
        const totalCollected = window.splitPayments.reduce((sum, p) => sum + p, 0);
        document.getElementById('totalCollected').textContent = totalCollected.toFixed(2) + ' TL';
        document.getElementById('remaining').textContent = (total - totalCollected).toFixed(2) + ' TL';
        
        if (totalCollected < total) {
            document.getElementById('completeBtn').disabled = true;
            document.getElementById('remaining').style.color = '#e74c3c';
        }
    }
    
    function completeSplitPayment(total) {
        const totalCollected = window.splitPayments.reduce((sum, p) => sum + p, 0);
        
        if (totalCollected < total) {
            alert('Toplanan tutar yeterli değil!');
            return;
        }
        
        // Ödeme modal'ına geç
        closeModal();
        showPaymentModal(total);
    }
    
    function showPaymentModal(total) {
        const content = `
            <div style="text-align: center; margin-bottom: 30px;">
                <div style="font-size: 72px; color: #e91e63; margin-bottom: 20px;">
                    <i class="fa fa-credit-card"></i>
                </div>
                <h2 style="color: #2c3e50; font-size: 28px; margin-bottom: 10px;">Ödeme Tipi Seçin</h2>
                <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 12px; margin: 20px 0;">
                    <div style="font-size: 18px; opacity: 0.9; margin-bottom: 5px;">Masa ${selectedTableName}</div>
                    <div style="font-size: 48px; font-weight: 700;">${total.toFixed(2)} TL</div>
                </div>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <button class="btn-success" style="padding: 40px 30px; font-size: 20px; border-radius: 12px; transition: all 0.3s;" onclick="processPayment('cash', ${total})" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                    <i class="fa fa-money" style="font-size: 48px; display: block; margin-bottom: 15px;"></i>
                    <strong>NAKİT</strong>
                </button>
                <button class="btn-primary" style="padding: 40px 30px; font-size: 20px; border-radius: 12px; transition: all 0.3s;" onclick="processPayment('card', ${total})" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                    <i class="fa fa-credit-card" style="font-size: 48px; display: block; margin-bottom: 15px;"></i>
                    <strong>KART</strong>
                </button>
                <button class="btn-warning" style="padding: 40px 30px; font-size: 20px; border-radius: 12px; transition: all 0.3s;" onclick="processPayment('bank', ${total})" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                    <i class="fa fa-bank" style="font-size: 48px; display: block; margin-bottom: 15px;"></i>
                    <strong>BANKA</strong>
                </button>
                <button class="btn-danger" style="padding: 40px 30px; font-size: 20px; border-radius: 12px; transition: all 0.3s;" onclick="processPayment('debt', ${total})" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                    <i class="fa fa-user" style="font-size: 48px; display: block; margin-bottom: 15px;"></i>
                    <strong>VERESİYE</strong>
                </button>
            </div>
        `;
        showModal('', content, 'large');
    }
    
    function processPayment(type, total) {
        const typeNames = {'cash': 'Nakit', 'card': 'Kredi Kartı', 'bank': 'Banka', 'debt': 'Veresiye'};
        const typeIcons = {'cash': 'money', 'card': 'credit-card', 'bank': 'bank', 'debt': 'user'};
        const typeColors = {'cash': '#4caf50', 'card': '#2196f3', 'bank': '#ff9800', 'debt': '#f44336'};
        
        // Onay modal'ı göster
        const confirmContent = `
            <div style="text-align: center; padding: 30px 20px;">
                <div style="font-size: 80px; color: ${typeColors[type]}; margin-bottom: 20px;">
                    <i class="fa fa-${typeIcons[type]}"></i>
                </div>
                <h2 style="font-size: 32px; color: #2c3e50; margin-bottom: 15px;">Ödemeyi Onayla</h2>
                <div style="background: linear-gradient(135deg, ${typeColors[type]}, ${typeColors[type]}dd); color: white; padding: 25px; border-radius: 12px; margin: 25px 0;">
                    <div style="font-size: 20px; opacity: 0.95; margin-bottom: 10px;">Masa ${selectedTableName}</div>
                    <div style="font-size: 56px; font-weight: 700; margin-bottom: 10px;">${total.toFixed(2)} TL</div>
                    <div style="font-size: 24px; opacity: 0.95;">${typeNames[type]}</div>
                </div>
                <p style="font-size: 18px; color: #666; margin-bottom: 30px;">Bu ödemeyi onaylıyor musunuz?</p>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <button class="btn-danger" onclick="closeModal()" style="padding: 20px; font-size: 18px; border-radius: 10px;">
                        <i class="fa fa-times"></i> İPTAL
                    </button>
                    <button class="btn-success" onclick="confirmPayment('${type}', ${total})" style="padding: 20px; font-size: 18px; border-radius: 10px;">
                        <i class="fa fa-check"></i> ONAYLA
                    </button>
                </div>
            </div>
        `;
        showModal('', confirmContent, 'medium');
    }
    
    function confirmPayment(type, total) {
        const typeNames = {'cash': 'Nakit', 'card': 'Kredi Kartı', 'bank': 'Banka', 'debt': 'Veresiye'};
        const paymentData = {
            tableId: selectedTableId,
            paymentType: type,
            amount: total
        };
        
        console.log('Ödeme gönderiliyor:', paymentData);
        
        fetch('api/close_table.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(paymentData)
        })
        .then(response => {
            console.log('Response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            if (data.success) {
                // Başarı modal'ı göster
                const successContent = `
                    <div style="text-align: center; padding: 40px 20px;">
                        <div style="font-size: 100px; color: #4caf50; margin-bottom: 20px; animation: scaleIn 0.5s;">
                            <i class="fa fa-check-circle"></i>
                        </div>
                        <h2 style="font-size: 36px; color: #2c3e50; margin-bottom: 20px;">Ödeme Başarılı!</h2>
                        <div style="background: #f8f9fa; padding: 25px; border-radius: 12px; margin: 25px 0;">
                            <div style="font-size: 18px; color: #666; margin-bottom: 10px;">Masa ${selectedTableName}</div>
                            <div style="font-size: 48px; font-weight: 700; color: #4caf50; margin-bottom: 10px;">${data.amount.toFixed(2)} TL</div>
                            <div style="font-size: 20px; color: #666; margin-bottom: 5px;">${typeNames[type]}</div>
                            <div style="font-size: 14px; color: #999;">Sipariş No: #${data.orderId}</div>
                        </div>
                        <button class="btn-primary" onclick="location.reload()" style="padding: 20px 40px; font-size: 20px; border-radius: 10px; margin-top: 20px;">
                            <i class="fa fa-refresh"></i> TAMAM
                        </button>
                    </div>
                    <style>
                        @keyframes scaleIn {
                            from { transform: scale(0); }
                            to { transform: scale(1); }
                        }
                    </style>
                `;
                showModal('', successContent, 'medium');
            } else {
                alert('❌ Hata: ' + data.message + '\n\nDebug: ' + JSON.stringify(data.debug || {}));
                console.error('Hata detayı:', data);
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            alert('❌ Bağlantı hatası: ' + error.message);
        });
    }
</script>

<?php include 'layout/footer.php'; ?>
