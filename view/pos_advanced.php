<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS / Kasa - Restaurant ERP</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); overflow: hidden; }
        
        .pos-container { display: grid; grid-template-columns: 380px 1fr 350px; height: 100vh; }
        
        /* Sol Panel - Sipariş */
        .left-panel { background: white; display: flex; flex-direction: column; }
        .pos-header { background: linear-gradient(135deg, #e91e63 0%, #c2185b 100%); color: white; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; }
        .pos-header h2 { font-size: 20px; margin: 0; }
        .pos-header .user { font-size: 12px; opacity: 0.9; }
        
        .table-selector { padding: 15px; background: #f8f9fa; display: flex; gap: 10px; }
        .table-btn { flex: 1; padding: 12px; background: white; border: 2px solid #e0e0e0; border-radius: 8px; cursor: pointer; text-align: center; font-weight: 600; transition: all 0.3s; }
        .table-btn.active { background: #e91e63; color: white; border-color: #e91e63; }
        
        .order-list { flex: 1; overflow-y: auto; padding: 15px; }
        .order-item { background: #f8f9fa; padding: 12px; border-radius: 8px; margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center; }
        .order-item-info { flex: 1; }
        .order-item-name { font-weight: 600; margin-bottom: 5px; }
        .order-item-price { color: #666; font-size: 14px; }
        .order-item-qty { display: flex; align-items: center; gap: 10px; }
        .qty-btn { width: 30px; height: 30px; border: none; background: #e91e63; color: white; border-radius: 5px; cursor: pointer; font-weight: 600; }
        .qty-display { min-width: 30px; text-align: center; font-weight: 600; }
        .btn-remove { background: #f44336; color: white; border: none; padding: 5px 10px; border-radius: 5px; cursor: pointer; }
        
        .order-summary { background: #f8f9fa; padding: 20px; border-top: 2px solid #e0e0e0; }
        .summary-row { display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 16px; }
        .summary-row.total { font-size: 24px; font-weight: 700; padding-top: 15px; border-top: 2px solid #e0e0e0; color: #e91e63; }
        
        .payment-section { padding: 15px; background: white; border-top: 2px solid #e0e0e0; }
        .btn-pay { width: 100%; padding: 18px; background: linear-gradient(135deg, #4caf50 0%, #45a049 100%); color: white; border: none; border-radius: 10px; font-size: 18px; font-weight: 700; cursor: pointer; transition: all 0.3s; }
        .btn-pay:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(76, 175, 80, 0.4); }
        
        /* Orta Panel - Ürünler */
        .middle-panel { background: #f5f5f5; display: flex; flex-direction: column; overflow: hidden; }
        .category-tabs { display: flex; gap: 10px; padding: 15px; background: white; overflow-x: auto; }
        .category-tab { padding: 12px 24px; background: #f5f5f5; border-radius: 8px; cursor: pointer; white-space: nowrap; font-weight: 600; transition: all 0.3s; }
        .category-tab.active { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        
        .products-grid { flex: 1; overflow-y: auto; padding: 15px; display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 15px; }
        .product-card { background: white; border-radius: 12px; padding: 15px; text-align: center; cursor: pointer; transition: all 0.3s; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .product-card:hover { transform: translateY(-5px); box-shadow: 0 5px 20px rgba(0,0,0,0.15); }
        .product-image { width: 100%; height: 110px; background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); border-radius: 8px; margin-bottom: 10px; display: flex; align-items: center; justify-content: center; font-size: 42px; }
        .product-name { font-size: 14px; font-weight: 600; margin-bottom: 8px; min-height: 40px; line-height: 1.3; }
        .product-price { font-size: 18px; font-weight: 700; color: #e91e63; }
        
        /* Sağ Panel - İşlemler */
        .right-panel { background: #2c3e50; color: white; display: flex; flex-direction: column; }
        .right-header { padding: 20px; background: rgba(0,0,0,0.2); }
        .right-header h3 { margin: 0 0 10px 0; }
        
        .action-buttons { padding: 15px; display: grid; gap: 10px; }
        .action-btn { padding: 15px; background: rgba(255,255,255,0.1); border: 2px solid rgba(255,255,255,0.2); color: white; border-radius: 8px; cursor: pointer; font-weight: 600; transition: all 0.3s; text-align: left; }
        .action-btn:hover { background: rgba(255,255,255,0.2); }
        .action-btn i { margin-right: 10px; }
        
        .numpad { padding: 15px; }
        .numpad-display { background: rgba(255,255,255,0.1); padding: 15px; border-radius: 8px; text-align: center; font-size: 24px; font-weight: 700; margin-bottom: 15px; }
        .numpad-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; }
        .numpad-btn { padding: 20px; background: rgba(255,255,255,0.1); border: 2px solid rgba(255,255,255,0.2); color: white; border-radius: 8px; cursor: pointer; font-size: 20px; font-weight: 600; transition: all 0.3s; }
        .numpad-btn:hover { background: rgba(255,255,255,0.2); }
        .numpad-btn.wide { grid-column: span 2; }
        
        .empty-order { text-align: center; padding: 40px 20px; color: #999; }
        .empty-order i { font-size: 64px; margin-bottom: 15px; opacity: 0.3; }
    </style>
</head>
<body>
    <div class="pos-container">
        <!-- Sol Panel - Sipariş -->
        <div class="left-panel">
            <div class="pos-header">
                <div>
                    <h2>🍽️ MENULUX POS</h2>
                    <div class="user">Kasiyer: <?= htmlspecialchars($userInfo['fullname']) ?></div>
                </div>
                <a href="admin.php" style="color: white; text-decoration: none;">
                    <i class="fa fa-times"></i>
                </a>
            </div>
            
            <div class="table-selector">
                <div class="table-btn active">
                    <i class="fa fa-shopping-cart"></i><br>
                    Masa 1
                </div>
                <div class="table-btn">
                    <i class="fa fa-user"></i><br>
                    Müşteri Seç
                </div>
                <div class="table-btn" onclick="showTableSelector()">
                    <i class="fa fa-th"></i><br>
                    Masa Değiştir
                </div>
            </div>
            
            <div class="order-list" id="orderList">
                <div class="empty-order">
                    <i class="fa fa-shopping-cart"></i>
                    <p>Sipariş boş</p>
                    <p style="font-size: 14px;">Ürün eklemek için sağdan seçin</p>
                </div>
            </div>
            
            <div class="order-summary">
                <div class="summary-row">
                    <span>Ara Toplam:</span>
                    <span id="subtotal">0.00 TL</span>
                </div>
                <div class="summary-row">
                    <span>KDV (%18):</span>
                    <span id="tax">0.00 TL</span>
                </div>
                <div class="summary-row">
                    <span>İndirim:</span>
                    <span id="discount">0.00 TL</span>
                </div>
                <div class="summary-row total">
                    <span>TOPLAM:</span>
                    <span id="total">0.00 TL</span>
                </div>
            </div>
            
            <div class="payment-section">
                <button class="btn-pay" onclick="showPaymentModal()">
                    <i class="fa fa-credit-card"></i> ÖDEME AL
                </button>
            </div>
        </div>

        <!-- Orta Panel - Ürünler -->
        <div class="middle-panel">
            <div class="category-tabs">
                <?php 
                $firstCat = true;
                foreach($menu as $catName => $products): 
                ?>
                <div class="category-tab <?= $firstCat ? 'active' : '' ?>" onclick="showCategory('<?= htmlspecialchars($catName) ?>')">
                    <?= htmlspecialchars($catName) ?>
                </div>
                <?php 
                    $firstCat = false;
                endforeach; 
                ?>
            </div>
            
            <div class="products-grid">
                <?php 
                $firstCat = true;
                foreach($menu as $catName => $products): 
                    foreach($products as $product): 
                ?>
                <div class="product-card" onclick="addToCart(<?= $product['id'] ?>, '<?= htmlspecialchars($product['name']) ?>', <?= $product['price'] ?>)">
                    <div class="product-image">
                        <i class="fa fa-cutlery"></i>
                    </div>
                    <div class="product-name"><?= htmlspecialchars($product['name']) ?></div>
                    <div class="product-price"><?= number_format($product['price'], 2) ?> TL</div>
                </div>
                <?php 
                    endforeach;
                endforeach; 
                ?>
            </div>
        </div>

        <!-- Sağ Panel - İşlemler -->
        <div class="right-panel">
            <div class="right-header">
                <h3>Hızlı İşlemler</h3>
            </div>
            
            <div class="action-buttons">
                <button class="action-btn" onclick="showDiscountModal()">
                    <i class="fa fa-percent"></i> İndirim / Promosyon
                </button>
                <button class="action-btn" onclick="showCustomerModal()">
                    <i class="fa fa-user"></i> Müşteri Seç (Veresiye)
                </button>
                <button class="action-btn" onclick="showNoteModal()">
                    <i class="fa fa-comment"></i> Not Ekle
                </button>
                <button class="action-btn" onclick="clearOrder()">
                    <i class="fa fa-trash"></i> Siparişi Temizle
                </button>
            </div>
            
            <div class="numpad">
                <div class="numpad-display" id="numpadDisplay">0</div>
                <div class="numpad-grid">
                    <button class="numpad-btn" onclick="numpadInput('7')">7</button>
                    <button class="numpad-btn" onclick="numpadInput('8')">8</button>
                    <button class="numpad-btn" onclick="numpadInput('9')">9</button>
                    <button class="numpad-btn" onclick="numpadInput('4')">4</button>
                    <button class="numpad-btn" onclick="numpadInput('5')">5</button>
                    <button class="numpad-btn" onclick="numpadInput('6')">6</button>
                    <button class="numpad-btn" onclick="numpadInput('1')">1</button>
                    <button class="numpad-btn" onclick="numpadInput('2')">2</button>
                    <button class="numpad-btn" onclick="numpadInput('3')">3</button>
                    <button class="numpad-btn wide" onclick="numpadInput('0')">0</button>
                    <button class="numpad-btn" onclick="numpadClear()">C</button>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/modal.js"></script>
    <script>
        let cart = [];
        let discount = 0;
        let selectedCustomer = null;
        let numpadValue = '0';

        function addToCart(id, name, price) {
            const existingItem = cart.find(item => item.id === id);
            if (existingItem) {
                existingItem.quantity++;
            } else {
                cart.push({ id, name, price, quantity: 1 });
            }
            updateCart();
        }

        function updateQuantity(id, change) {
            const item = cart.find(item => item.id === id);
            if (item) {
                item.quantity += change;
                if (item.quantity <= 0) {
                    cart = cart.filter(i => i.id !== id);
                }
            }
            updateCart();
        }

        function removeFromCart(id) {
            cart = cart.filter(item => item.id !== id);
            updateCart();
        }

        function updateCart() {
            const orderListDiv = document.getElementById('orderList');
            
            if (cart.length === 0) {
                orderListDiv.innerHTML = `
                    <div class="empty-order">
                        <i class="fa fa-shopping-cart"></i>
                        <p>Sipariş boş</p>
                        <p style="font-size: 14px;">Ürün eklemek için sağdan seçin</p>
                    </div>
                `;
            } else {
                orderListDiv.innerHTML = cart.map(item => `
                    <div class="order-item">
                        <div class="order-item-info">
                            <div class="order-item-name">${item.name}</div>
                            <div class="order-item-price">${item.price.toFixed(2)} TL</div>
                        </div>
                        <div class="order-item-qty">
                            <button class="qty-btn" onclick="updateQuantity(${item.id}, -1)">-</button>
                            <span class="qty-display">${item.quantity}</span>
                            <button class="qty-btn" onclick="updateQuantity(${item.id}, 1)">+</button>
                            <button class="btn-remove" onclick="removeFromCart(${item.id})">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    </div>
                `).join('');
            }

            // Toplamları hesapla
            const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            const tax = subtotal * 0.18;
            const total = subtotal + tax - discount;

            document.getElementById('subtotal').textContent = subtotal.toFixed(2) + ' TL';
            document.getElementById('tax').textContent = tax.toFixed(2) + ' TL';
            document.getElementById('discount').textContent = discount.toFixed(2) + ' TL';
            document.getElementById('total').textContent = total.toFixed(2) + ' TL';
        }

        function showPaymentModal() {
            if (cart.length === 0) {
                alert('Sepet boş!');
                return;
            }
            
            const total = document.getElementById('total').textContent;
            const content = `
                <div style="text-align: center; margin-bottom: 20px;">
                    <h2 style="color: #e91e63; font-size: 36px;">${total}</h2>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <button class="btn-success" style="padding: 30px; font-size: 18px;" onclick="processPayment('cash')">
                        <i class="fa fa-money" style="font-size: 32px; display: block; margin-bottom: 10px;"></i>
                        NAKİT
                    </button>
                    <button class="btn-primary" style="padding: 30px; font-size: 18px;" onclick="processPayment('card')">
                        <i class="fa fa-credit-card" style="font-size: 32px; display: block; margin-bottom: 10px;"></i>
                        KART
                    </button>
                    <button class="btn-warning" style="padding: 30px; font-size: 18px;" onclick="processPayment('bank')">
                        <i class="fa fa-bank" style="font-size: 32px; display: block; margin-bottom: 10px;"></i>
                        BANKA
                    </button>
                    <button class="btn-danger" style="padding: 30px; font-size: 18px;" onclick="processPayment('debt')">
                        <i class="fa fa-user" style="font-size: 32px; display: block; margin-bottom: 10px;"></i>
                        VERESİYE
                    </button>
                </div>
            `;
            showModal('<i class="fa fa-credit-card"></i> Ödeme Tipi Seçin', content, 'medium');
        }

        function processPayment(type) {
            const typeNames = {
                'cash': 'Nakit',
                'card': 'Kredi Kartı',
                'bank': 'Banka Transferi',
                'debt': 'Veresiye'
            };
            
            if (type === 'debt' && !selectedCustomer) {
                alert('Lütfen önce müşteri seçin!');
                closeModal();
                showCustomerModal();
                return;
            }
            
            if (confirm(`${typeNames[type]} ile ödeme alınacak. Onaylıyor musunuz?`)) {
                // Ödeme kaydet
                const orderData = {
                    tableId: 1,
                    items: cart,
                    paymentType: type,
                    discount: discount,
                    customerId: selectedCustomer,
                    note: ''
                };
                
                fetch('api/save_order.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(orderData)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(`✅ Ödeme alındı!\nSipariş No: ${data.orderId}\nTip: ${typeNames[type]}\nTutar: ${data.total.toFixed(2)} TL`);
                        cart = [];
                        discount = 0;
                        selectedCustomer = null;
                        updateCart();
                        closeModal();
                    } else {
                        alert('Hata: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Bir hata oluştu!');
                });
            }
        }

        function showDiscountModal() {
            const content = `
                <div class="form-group">
                    <label>İndirim Tutarı (TL)</label>
                    <input type="number" id="discountAmount" step="0.01" value="${discount}">
                </div>
                <div class="modal-footer">
                    <button class="btn-danger" onclick="closeModal()">İptal</button>
                    <button class="btn-success" onclick="applyDiscount()">Uygula</button>
                </div>
            `;
            showModal('<i class="fa fa-percent"></i> İndirim Uygula', content, 'small');
        }

        function applyDiscount() {
            discount = parseFloat(document.getElementById('discountAmount').value) || 0;
            updateCart();
            closeModal();
        }

        function showCustomerModal() {
            const content = `
                <div class="form-group">
                    <label>Müşteri Seç</label>
                    <select id="customerSelect">
                        <option value="">Müşteri Seçin</option>
                        <option value="1">Ahmet Yılmaz</option>
                        <option value="2">Mehmet Demir</option>
                        <option value="3">Ayşe Kaya</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button class="btn-danger" onclick="closeModal()">İptal</button>
                    <button class="btn-success" onclick="selectCustomer()">Seç</button>
                </div>
            `;
            showModal('<i class="fa fa-user"></i> Müşteri Seç (Veresiye)', content, 'small');
        }

        function selectCustomer() {
            const select = document.getElementById('customerSelect');
            selectedCustomer = select.value;
            if (selectedCustomer) {
                alert('Müşteri seçildi: ' + select.options[select.selectedIndex].text);
            }
            closeModal();
        }

        function showNoteModal() {
            const content = `
                <div class="form-group">
                    <label>Sipariş Notu</label>
                    <textarea id="orderNote" rows="4" placeholder="Not ekleyin..."></textarea>
                </div>
                <div class="modal-footer">
                    <button class="btn-danger" onclick="closeModal()">İptal</button>
                    <button class="btn-success" onclick="saveNote()">Kaydet</button>
                </div>
            `;
            showModal('<i class="fa fa-comment"></i> Sipariş Notu', content, 'medium');
        }

        function saveNote() {
            const note = document.getElementById('orderNote').value;
            if (note) {
                alert('Not kaydedildi: ' + note);
            }
            closeModal();
        }

        function clearOrder() {
            if (cart.length > 0 && confirm('Siparişi temizlemek istediğinize emin misiniz?')) {
                cart = [];
                discount = 0;
                updateCart();
            }
        }

        function showTableSelector() {
            alert('Masa seçme ekranı açılacak');
        }

        function numpadInput(num) {
            if (numpadValue === '0') {
                numpadValue = num;
            } else {
                numpadValue += num;
            }
            document.getElementById('numpadDisplay').textContent = numpadValue;
        }

        function numpadClear() {
            numpadValue = '0';
            document.getElementById('numpadDisplay').textContent = numpadValue;
        }

        function showCategory(catName) {
            document.querySelectorAll('.category-tab').forEach(tab => tab.classList.remove('active'));
            event.target.classList.add('active');
        }
    </script>
</body>
</html>
