<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tedarikçiler - Restaurant ERP</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="assets/css/modern.css">
</head>
<body>
    <div class="top-bar">
        <h2><i class="fa fa-truck"></i> Tedarikçi Yönetimi</h2>
        <div>
            <span style="margin-right: 20px;">Toplam Borç: <strong style="color: #e74c3c;"><?= number_format($totalDebt, 2) ?> TL</strong></span>
            <a href="admin.php" class="btn-modern btn-primary"><i class="fa fa-arrow-left"></i> Admin Panel</a>
        </div>
    </div>

    <div style="padding: 25px;">
        <div class="card-modern">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3>Tüm Tedarikçiler</h3>
                <button class="btn-modern btn-success" onclick="showAddModal()">
                    <i class="fa fa-plus"></i> Yeni Tedarikçi Ekle
                </button>
            </div>

            <table class="table table-striped">
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
                                <span class="label label-danger"><?= number_format($supplier['balance'], 2) ?> TL</span>
                            <?php else: ?>
                                <span class="label label-success">0.00 TL</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-info" onclick="showStatement(<?= $supplier['id'] ?>)">
                                <i class="fa fa-file-text"></i> Ekstre
                            </button>
                            <button class="btn btn-sm btn-warning" onclick="editSupplier(<?= $supplier['id'] ?>)">
                                <i class="fa fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="deleteSupplier(<?= $supplier['id'] ?>)">
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
            alert('Tedarikçi ekleme formu açılacak');
        }
        
        function showStatement(id) {
            window.location.href = 'suppliers.php?action=statement&id=' + id;
        }
        
        function editSupplier(id) {
            alert('Tedarikçi düzenleme: ' + id);
        }
        
        function deleteSupplier(id) {
            if(confirm('Bu tedarikçiyi silmek istediğinize emin misiniz?')) {
                alert('Silme işlemi: ' + id);
            }
        }
    </script>
</body>
</html>
