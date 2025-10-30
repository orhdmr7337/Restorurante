<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alış Faturaları - Restaurant ERP</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="assets/css/modern.css">
</head>
<body>
    <div class="top-bar">
        <h2><i class="fa fa-file-text"></i> Alış Faturaları</h2>
        <a href="admin.php" class="btn-modern btn-primary"><i class="fa fa-arrow-left"></i> Admin Panel</a>
    </div>

    <div style="padding: 25px;">
        <div class="card-modern">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3>Tüm Alış Faturaları</h3>
                <button class="btn-modern btn-success" onclick="showAddModal()">
                    <i class="fa fa-plus"></i> Yeni Fatura Ekle
                </button>
            </div>

            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Fatura No</th>
                        <th>Tedarikçi</th>
                        <th>Tarih</th>
                        <th>Tutar</th>
                        <th>Ödeme Durumu</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($purchases)): ?>
                        <?php foreach($purchases as $purchase): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($purchase['invoice_number'] ?? '-') ?></strong></td>
                            <td><?= htmlspecialchars($purchase['supplier_name'] ?? '-') ?></td>
                            <td><?= date('d.m.Y', strtotime($purchase['purchase_date'])) ?></td>
                            <td><strong><?= number_format($purchase['total_amount'], 2) ?> TL</strong></td>
                            <td>
                                <?php if($purchase['payment_status'] == 'paid'): ?>
                                    <span class="label label-success">Ödendi</span>
                                <?php elseif($purchase['payment_status'] == 'partial'): ?>
                                    <span class="label label-warning">Kısmi</span>
                                <?php else: ?>
                                    <span class="label label-danger">Ödenmedi</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-info" onclick="viewPurchase(<?= $purchase['id'] ?>)">
                                    <i class="fa fa-eye"></i> Görüntüle
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">Henüz fatura kaydı yok</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function showAddModal() {
            alert('Fatura ekleme formu açılacak');
        }
        
        function viewPurchase(id) {
            window.location.href = 'purchases.php?action=view&id=' + id;
        }
    </script>
</body>
</html>
