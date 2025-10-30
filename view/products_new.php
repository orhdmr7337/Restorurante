<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ürünler - Restaurant ERP</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="assets/css/modern.css">
</head>
<body>
    <div class="top-bar">
        <h2><i class="fa fa-cutlery"></i> Ürün Yönetimi</h2>
        <a href="admin.php" class="btn-modern btn-primary"><i class="fa fa-arrow-left"></i> Admin Panel</a>
    </div>

    <div style="padding: 25px;">
        <div class="card-modern">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3>Tüm Ürünler</h3>
                <button class="btn-modern btn-success" onclick="showAddModal()">
                    <i class="fa fa-plus"></i> Yeni Ürün Ekle
                </button>
            </div>

            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Ürün Adı</th>
                        <th>Kategori</th>
                        <th>Fiyat</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($products as $product): ?>
                    <tr>
                        <td><?= $product['id'] ?></td>
                        <td><strong><?= htmlspecialchars($product['name']) ?></strong></td>
                        <td><?= htmlspecialchars($product['category_name'] ?? 'Kategori Yok') ?></td>
                        <td><strong><?= number_format($product['price'], 2) ?> TL</strong></td>
                        <td>
                            <button class="btn btn-sm btn-warning" onclick="editProduct(<?= $product['id'] ?>)">
                                <i class="fa fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="deleteProduct(<?= $product['id'] ?>)">
                                <i class="fa fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function showAddModal() {
            alert('Ürün ekleme modal açılacak');
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
</body>
</html>
