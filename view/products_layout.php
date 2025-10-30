<?php 
$pageTitle = 'Ürün Yönetimi';
$activePage = 'products';
include 'layout/header.php'; 
?>

<div class="card">
    <div class="card-header">
        <h3><i class="fa fa-cutlery"></i> Tüm Ürünler</h3>
        <button class="btn-success" onclick="showAddModal()">
            <i class="fa fa-plus"></i> Yeni Ürün Ekle
        </button>
    </div>
    
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Ürün Adı</th>
                <th>Kategori</th>
                <th>Fiyat</th>
                <th>Reçete</th>
                <th>İşlemler</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($products as $product): ?>
            <tr>
                <td><?= $product['id'] ?></td>
                <td><strong><?= htmlspecialchars($product['name']) ?></strong></td>
                <td><?= htmlspecialchars($product['category_name'] ?? 'Kategori Yok') ?></td>
                <td><strong style="color: #27ae60;"><?= number_format($product['price'], 2) ?> TL</strong></td>
                <td>
                    <button class="btn-primary" style="padding: 5px 10px; font-size: 12px;" onclick="showRecipe(<?= $product['id'] ?>)">
                        <i class="fa fa-list"></i> Reçete
                    </button>
                </td>
                <td>
                    <button class="btn-warning" style="padding: 5px 10px; font-size: 12px;" onclick="editProduct(<?= $product['id'] ?>)">
                        <i class="fa fa-edit"></i>
                    </button>
                    <button class="btn-danger" style="padding: 5px 10px; font-size: 12px;" onclick="deleteProduct(<?= $product['id'] ?>)">
                        <i class="fa fa-trash"></i>
                    </button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
    function showAddModal() {
        const content = `
            <form id="addProductForm">
                <input type="hidden" name="task" value="productAdd">
                <div class="form-group">
                    <label>Ürün Adı *</label>
                    <input type="text" name="productName" required>
                </div>
                <div class="form-group">
                    <label>Kategori *</label>
                    <select name="catId" required>
                        <option value="">Kategori Seçin</option>
                        <?php foreach($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Fiyat (TL) *</label>
                    <input type="number" name="productPrice" step="0.01" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-danger" onclick="closeModal()">İptal</button>
                    <button type="submit" class="btn-success">
                        <i class="fa fa-save"></i> Kaydet
                    </button>
                </div>
            </form>
        `;
        showModal('<i class="fa fa-plus"></i> Yeni Ürün Ekle', content, 'medium');
        
        document.getElementById('addProductForm').onsubmit = function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('menuTasks.php', {
                method: 'POST',
                body: formData
            }).then(() => {
                closeModal();
                location.reload();
            });
        };
    }
    
    function showRecipe(id) {
        window.location.href = 'recipes.php?product_id=' + id;
    }
    
    function editProduct(id) {
        window.location.href = 'productsTasks.php?task=edit&id=' + id;
    }
    
    function deleteProduct(id) {
        if(confirm('Bu ürünü silmek istediğinize emin misiniz?')) {
            window.location.href = 'productsTasks.php?task=delete&id=' + id;
        }
    }
</script>

<?php include 'layout/footer.php'; ?>
