<?php 
$pageTitle = 'Alış Faturaları';
$activePage = 'purchases';
include 'layout/header.php'; 
?>

<div class="card">
    <div class="card-header">
        <h3><i class="fa fa-file-text"></i> Tüm Alış Faturaları</h3>
        <button class="btn-success" onclick="showAddModal()">
            <i class="fa fa-plus"></i> Yeni Fatura Ekle
        </button>
    </div>
    
    <table class="table">
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
                            <span style="background: #dfd; color: #363; padding: 5px 10px; border-radius: 5px; font-size: 12px; font-weight: 600;">Ödendi</span>
                        <?php elseif($purchase['payment_status'] == 'partial'): ?>
                            <span style="background: #ffd; color: #663; padding: 5px 10px; border-radius: 5px; font-size: 12px; font-weight: 600;">Kısmi</span>
                        <?php else: ?>
                            <span style="background: #fee; color: #c33; padding: 5px 10px; border-radius: 5px; font-size: 12px; font-weight: 600;">Ödenmedi</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <button class="btn-primary" style="padding: 5px 10px; font-size: 12px;" onclick="viewPurchase(<?= $purchase['id'] ?>)">
                            <i class="fa fa-eye"></i> Görüntüle
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 30px; color: #7f8c8d;">Henüz fatura kaydı yok</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
    function showAddModal() {
        alert('Fatura ekleme formu açılacak');
    }
    
    function viewPurchase(id) {
        window.location.href = 'purchases.php?action=view&id=' + id;
    }
</script>

<?php include 'layout/footer.php'; ?>
