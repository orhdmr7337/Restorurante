<?php 
$pageTitle = 'Stok Yönetimi';
$activePage = 'materials';
include 'layout/header.php'; 
?>

<?php if(!empty($lowStock)): ?>
<div class="alert alert-warning" style="background: #fff3cd; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #ffc107;">
    <strong><i class="fa fa-exclamation-triangle"></i> Düşük Stok Uyarısı!</strong>
    <?= count($lowStock) ?> malzeme minimum stok seviyesinin altında.
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h3><i class="fa fa-cubes"></i> Tüm Malzemeler</h3>
        <div>
            <button class="btn-success" onclick="showAddModal()">
                <i class="fa fa-plus"></i> Yeni Malzeme
            </button>
            <button class="btn-primary" onclick="showStockMovement()">
                <i class="fa fa-exchange"></i> Stok Hareketi
            </button>
        </div>
    </div>
    
    <table class="table">
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
            <tr style="<?= $material['current_stock'] <= $material['min_stock'] ? 'background: #fee;' : '' ?>">
                <td><?= $material['id'] ?></td>
                <td><strong><?= htmlspecialchars($material['name']) ?></strong></td>
                <td><?= htmlspecialchars($material['unit']) ?></td>
                <td>
                    <span style="padding: 5px 10px; border-radius: 5px; font-weight: 600; <?= $material['current_stock'] <= $material['min_stock'] ? 'background: #fee; color: #c33;' : 'background: #dfd; color: #363;' ?>">
                        <?= number_format($material['current_stock'], 2) ?>
                    </span>
                </td>
                <td><?= number_format($material['min_stock'], 2) ?></td>
                <td><?= number_format($material['cost_price'], 2) ?> TL</td>
                <td>
                    <?php if($material['current_stock'] <= $material['min_stock']): ?>
                        <span style="background: #fee; color: #c33; padding: 5px 10px; border-radius: 5px; font-size: 12px; font-weight: 600;">Düşük Stok</span>
                    <?php else: ?>
                        <span style="background: #dfd; color: #363; padding: 5px 10px; border-radius: 5px; font-size: 12px; font-weight: 600;">Normal</span>
                    <?php endif; ?>
                </td>
                <td>
                    <button class="btn-primary" style="padding: 5px 10px; font-size: 12px;" onclick="showHistory(<?= $material['id'] ?>)">
                        <i class="fa fa-history"></i>
                    </button>
                    <button class="btn-warning" style="padding: 5px 10px; font-size: 12px;" onclick="editMaterial(<?= $material['id'] ?>)">
                        <i class="fa fa-edit"></i>
                    </button>
                    <button class="btn-danger" style="padding: 5px 10px; font-size: 12px;" onclick="deleteMaterial(<?= $material['id'] ?>)">
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

<?php include 'layout/footer.php'; ?>
