<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stok Yönetimi - Restaurant ERP</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="assets/css/modern.css">
</head>
<body>
    <div class="top-bar">
        <h2><i class="fa fa-cubes"></i> Stok Yönetimi</h2>
        <a href="admin.php" class="btn-modern btn-primary"><i class="fa fa-arrow-left"></i> Admin Panel</a>
    </div>

    <div style="padding: 25px;">
        <?php if(!empty($lowStock)): ?>
        <div class="alert alert-warning">
            <strong><i class="fa fa-exclamation-triangle"></i> Düşük Stok Uyarısı!</strong>
            <?= count($lowStock) ?> malzeme minimum stok seviyesinin altında.
        </div>
        <?php endif; ?>

        <div class="card-modern">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3>Tüm Malzemeler</h3>
                <div>
                    <button class="btn-modern btn-success" onclick="showAddModal()">
                        <i class="fa fa-plus"></i> Yeni Malzeme
                    </button>
                    <button class="btn-modern btn-primary" onclick="showStockMovement()">
                        <i class="fa fa-exchange"></i> Stok Hareketi
                    </button>
                </div>
            </div>

            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Malzeme Adı</th>
                        <th>Birim</th>
                        <th>Mevcut Stok</th>
                        <th>Min. Stok</th>
                        <th>Maliyet</th>
                        <th>Durum</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($materials as $material): ?>
                    <tr class="<?= $material['current_stock'] <= $material['min_stock'] ? 'danger' : '' ?>">
                        <td><?= $material['id'] ?></td>
                        <td><strong><?= htmlspecialchars($material['name']) ?></strong></td>
                        <td><?= htmlspecialchars($material['unit']) ?></td>
                        <td>
                            <span class="badge <?= $material['current_stock'] <= $material['min_stock'] ? 'badge-danger' : 'badge-success' ?>">
                                <?= number_format($material['current_stock'], 2) ?>
                            </span>
                        </td>
                        <td><?= number_format($material['min_stock'], 2) ?></td>
                        <td><?= number_format($material['cost_price'], 2) ?> TL</td>
                        <td>
                            <?php if($material['current_stock'] <= $material['min_stock']): ?>
                                <span class="label label-danger">Düşük Stok</span>
                            <?php else: ?>
                                <span class="label label-success">Normal</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-info" onclick="showHistory(<?= $material['id'] ?>)">
                                <i class="fa fa-history"></i>
                            </button>
                            <button class="btn btn-sm btn-warning" onclick="editMaterial(<?= $material['id'] ?>)">
                                <i class="fa fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="deleteMaterial(<?= $material['id'] ?>)">
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
            alert('Malzeme ekleme formu açılacak');
        }
        
        function showStockMovement() {
            alert('Stok hareketi formu açılacak');
        }
        
        function showHistory(id) {
            window.location.href = 'materials.php?action=history&id=' + id;
        }
        
        function editMaterial(id) {
            alert('Malzeme düzenleme: ' + id);
        }
        
        function deleteMaterial(id) {
            if(confirm('Bu malzemeyi silmek istediğinize emin misiniz?')) {
                alert('Silme işlemi: ' + id);
            }
        }
    </script>
</body>
</html>
