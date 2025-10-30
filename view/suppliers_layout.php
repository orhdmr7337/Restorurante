<?php 
$pageTitle = 'Tedarikçi Yönetimi';
$activePage = 'suppliers';
include 'layout/header.php'; 
?>

<div class="card">
    <div class="card-header">
        <h3><i class="fa fa-truck"></i> Tüm Tedarikçiler</h3>
        <div>
            <span style="margin-right: 20px;">Toplam Borç: <strong style="color: #e74c3c;"><?= number_format($totalDebt, 2) ?> TL</strong></span>
            <button class="btn-success" onclick="showAddModal()">
                <i class="fa fa-plus"></i> Yeni Tedarikçi
            </button>
        </div>
    </div>
    
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Firma Adı</th>
                <th>Yetkili</th>
                <th>Telefon</th>
                <th>E-posta</th>
                <th>Bakiye</th>
                <th>İşlemler</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($suppliers as $supplier): ?>
            <tr>
                <td><?= $supplier['id'] ?></td>
                <td><strong><?= htmlspecialchars($supplier['name']) ?></strong></td>
                <td><?= htmlspecialchars($supplier['contact_person'] ?? '-') ?></td>
                <td><?= htmlspecialchars($supplier['phone'] ?? '-') ?></td>
                <td><?= htmlspecialchars($supplier['email'] ?? '-') ?></td>
                <td>
                    <?php if($supplier['balance'] > 0): ?>
                        <span style="background: #fee; color: #c33; padding: 5px 10px; border-radius: 5px; font-weight: 600;"><?= number_format($supplier['balance'], 2) ?> TL</span>
                    <?php else: ?>
                        <span style="background: #dfd; color: #363; padding: 5px 10px; border-radius: 5px; font-weight: 600;">0.00 TL</span>
                    <?php endif; ?>
                </td>
                <td>
                    <button class="btn-primary" style="padding: 5px 10px; font-size: 12px;" onclick="showStatement(<?= $supplier['id'] ?>)">
                        <i class="fa fa-file-text"></i> Ekstre
                    </button>
                    <button class="btn-warning" style="padding: 5px 10px; font-size: 12px;" onclick="editSupplier(<?= $supplier['id'] ?>)">
                        <i class="fa fa-edit"></i>
                    </button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
    function showAddModal() {
        alert('Tedarikçi ekleme formu açılacak');
    }
    
    function showStatement(id) {
        window.location.href = 'suppliers.php?action=statement&id=' + id;
    }
    
    function editSupplier(id) {
        alert('Tedarikçi düzenleme: ' + id);
    }
</script>

<?php include 'layout/footer.php'; ?>
