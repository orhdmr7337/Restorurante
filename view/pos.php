<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS / Kasa - Restaurant ERP</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="assets/css/modern.css">
    <style>
        body { background: #1a1a2e; color: white; }
        .pos-container { display: grid; grid-template-columns: 1fr 400px; gap: 0; height: 100vh; }
        
        /* Sol Panel - Ürünler */
        .products-panel { background: #16213e; padding: 20px; overflow-y: auto; }
        .pos-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 15px 20px; margin: -20px -20px 20px -20px; display: flex; justify-content: space-between; align-items: center; }
        .pos-header h2 { margin: 0; font-size: 24px; }
        .btn-back-pos { background: rgba(255,255,255,0.2); color: white; padding: 8px 16px; border-radius: 6px; text-decoration: none; }
        
        .category-tabs-pos { display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; }
        .category-tab-pos { padding: 12px 20px; background: #0f3460; border: 2px solid #16213e; border-radius: 8px; cursor: pointer; transition: all 0.3s; color: white; }
        .category-tab-pos.active { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-color: #667eea; }
        
        .products-grid-pos { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 15px; }
        .product-card-pos { background: #0f3460; border-radius: 10px; padding: 15px; text-align: center; cursor: pointer; transition: all 0.3s; }
        .product-card-pos:hover { background: #1a4d7a; transform: translateY(-3px); }
        .product-icon-pos { font-size: 40px; margin-bottom: 10px; }
        .product-name-pos { font-size: 14px; font-weight: 600; margin-bottom: 8px; }
        .product-price-pos { font-size: 18px; font-weight: 700; color: #4ade80; }
        
        /* Sağ Panel - Sepet */
        .cart-panel { background: #0f3460; display: flex; flex-direction: column; }
        .cart-header { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); padding: 20px; text-align: center; }
        .cart-header h2 { margin: 0; font-size: 28px; }
        .cart-items { flex: 1; padding: 20px; overflow-y: auto; }
        .cart-item { background: #1a4d7a; padding: 15px; border-radius: 8px; margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center; }
        .cart-item-info { flex: 1; }
        .cart-item-name { font-weight: 600; margin-bottom: 5px; }
        .cart-item-price { color: #94a3b8; font-size: 14px; }
        .cart-item-qty { background: #16213e; padding: 5px 15px; border-radius: 6px; margin: 0 10px; }
        .btn-remove-cart { background: #ef4444; color: white; border: none; width: 30px; height: 30px; border-radius: 6px; cursor: pointer; }
        
        .cart-summary { background: #16213e; padding: 20px; }
        .summary-row { display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 16px; }
        .summary-row.total { font-size: 24px; font-weight: 700; padding-top: 15px; border-top: 2px solid #0f3460; color: #4ade80; }
        
        .cart-actions { padding: 20px; display: grid; gap: 10px; }
        .btn-action-pos { padding: 15px; border: none; border-radius: 8px; font-weight: 600; font-size: 16px; cursor: pointer; transition: all 0.3s; }
        .btn-complete-pos { background: linear-gradient(135deg, #56ab2f 0%, #a8e063 100%); color: white; }
        .btn-cancel-pos { background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%); color: white; }
        
        .empty-cart { text-align: center; padding: 40px 20px; color: #64748b; }
        .empty-cart i { font-size: 64px; margin-bottom: 15px; opacity: 0.3; }
    </style>
</head>
<body>
    <div class="pos-container">
        <!-- Sol Panel - Ürünler -->
        <div class="products-panel">
            <div class="pos-header">
                <h2><i class="fa fa-calculator"></i> POS / Kasa</h2>
                <div>
                    <span style="margin-right: 20px;">Kasiyer: <strong><?= htmlspecialchars($userInfo['fullname']) ?></strong></span>
                    <a href="admin.php" class="btn-back-pos"><i class="fa fa-arrow-left"></i> Geri</a>
                </div>
            </div>

            <!-- Kategori Tabları -->
            <div class="category-tabs-pos">
                <?php 
                $firstCat = true;
                foreach($menu as $catName => $products): 
                ?>
                <div class="category-tab-pos <?= $firstCat ? 'active' : '' ?>" onclick="showPOSCategory('<?= htmlspecialchars($catName) ?>')">
                    <?= htmlspecialchars($catName) ?> (<?= count($products) ?>)
                </div>
                <?php 
                    $firstCat = false;
                endforeach; 
                ?>
            </div>

            <!-- Ürün Gridleri -->
            <?php 
            $firstCat = true;
            foreach($menu as $catName => $products): 
            ?>
            <div class="products-grid-pos category-content-pos <?= $firstCat ? 'active' : '' ?>" id="pos-cat-<?= htmlspecialchars($catName) ?>">
                <?php foreach($products as $product): ?>
                <div class="product-card-pos" onclick="addToCart(<?= $product['id'] ?>, '<?= htmlspecialchars($product['name']) ?>', <?= $product['price'] ?>)">
                    <div class="product-icon-pos"><i class="fa fa-cutlery"></i></div>
                    <div class="product-name-pos"><?= htmlspecialchars($product['name']) ?></div>
                    <div class="product-price-pos"><?= number_format($product['price'], 2) ?> TL</div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php 
                $firstCat = false;
            endforeach; 
            ?>
        </div>

        <!-- Sağ Panel - Sepet -->
        <div class="cart-panel">
            <div class="cart-header">
                <h2><i class="fa fa-shopping-cart"></i> Sepet</h2>
            </div>

            <div class="cart-items" id="cartItems">
                <div class="empty-cart">
                    <i class="fa fa-shopping-cart"></i>
                    <p>Sepet boş</p>
                    <p style="font-size: 14px;">Ürün eklemek için soldan seçin</p>
                </div>
            </div>

            <div class="cart-summary">
                <div class="summary-row">
                    <span>Ara Toplam:</span>
                    <span id="subtotal">0.00 TL</span>
                </div>
                <div class="summary-row">
                    <span>KDV (%18):</span>
                    <span id="tax">0.00 TL</span>
                </div>
                <div class="summary-row total">
                    <span>TOPLAM:</span>
                    <span id="total">0.00 TL</span>
                </div>
            </div>

            <div class="cart-actions">
                <button class="btn-action-pos btn-complete-pos" onclick="completeOrder()">
                    <i class="fa fa-check-circle"></i> Ödeme Al
                </button>
                <button class="btn-action-pos btn-cancel-pos" onclick="clearCart()">
                    <i class="fa fa-times-circle"></i> Sepeti Temizle
                </button>
            </div>
        </div>
    </div>

    <script>
        let cart = [];

        function showPOSCategory(catName) {
            document.querySelectorAll('.category-tab-pos').forEach(tab => tab.classList.remove('active'));
            event.target.classList.add('active');
            
            document.querySelectorAll('.category-content-pos').forEach(content => content.classList.remove('active'));
            document.getElementById('pos-cat-' + catName).classList.add('active');
        }

        function addToCart(id, name, price) {
            const existingItem = cart.find(item => item.id === id);
            if (existingItem) {
                existingItem.quantity++;
            } else {
                cart.push({ id, name, price, quantity: 1 });
            }
            updateCart();
        }

        function removeFromCart(id) {
            cart = cart.filter(item => item.id !== id);
            updateCart();
        }

        function updateCart() {
            const cartItemsDiv = document.getElementById('cartItems');
            
            if (cart.length === 0) {
                cartItemsDiv.innerHTML = `
                    <div class="empty-cart">
                        <i class="fa fa-shopping-cart"></i>
                        <p>Sepet boş</p>
                        <p style="font-size: 14px;">Ürün eklemek için soldan seçin</p>
                    </div>
                `;
            } else {
                cartItemsDiv.innerHTML = cart.map(item => `
                    <div class="cart-item">
                        <div class="cart-item-info">
                            <div class="cart-item-name">${item.name}</div>
                            <div class="cart-item-price">${item.price.toFixed(2)} TL</div>
                        </div>
                        <div style="display: flex; align-items: center;">
                            <span class="cart-item-qty">${item.quantity}x</span>
                            <button class="btn-remove-cart" onclick="removeFromCart(${item.id})">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    </div>
                `).join('');
            }

            // Toplamları hesapla
            const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            const tax = subtotal * 0.18;
            const total = subtotal + tax;

            document.getElementById('subtotal').textContent = subtotal.toFixed(2) + ' TL';
            document.getElementById('tax').textContent = tax.toFixed(2) + ' TL';
            document.getElementById('total').textContent = total.toFixed(2) + ' TL';
        }

        function completeOrder() {
            if (cart.length === 0) {
                alert('Sepet boş!');
                return;
            }
            
            if (confirm('Ödeme alınacak. Onaylıyor musunuz?')) {
                // TODO: Sipariş kaydetme işlemi
                alert('Ödeme alındı! Toplam: ' + document.getElementById('total').textContent);
                clearCart();
            }
        }

        function clearCart() {
            if (cart.length > 0 && confirm('Sepeti temizlemek istediğinize emin misiniz?')) {
                cart = [];
                updateCart();
            }
        }
    </script>
</body>
</html>
