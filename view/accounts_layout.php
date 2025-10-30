<?php 
$pageTitle = 'Cari Hesaplar';
$activePage = 'accounts';
include 'layout/header.php'; 
?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="icon" style="color: #e74c3c;"><i class="fa fa-arrow-down"></i></div>
        <h3>Toplam Borç</h3>
        <div class="value" style="color: #e74c3c;"><?= number_format($totalDebt, 2) ?> TL</div>
    </div>
    <div class="stat-card">
        <div class="icon" style="color: #27ae60;"><i class="fa fa-arrow-up"></i></div>
        <h3>Toplam Alacak</h3>
        <div class="value" style="color: #27ae60;"><?= number_format($totalCredit, 2) ?> TL</div>
    </div>
    <div class="stat-card">
        <div class="icon" style="color: #3498db;"><i class="fa fa-balance-scale"></i></div>
        <h3>Net Durum</h3>
        <div class="value" style="color: <?= ($totalCredit - $totalDebt) >= 0 ? '#27ae60' : '#e74c3c' ?>;">
            <?= number_format($totalCredit - $totalDebt, 2) ?> TL
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3><i class="fa fa-book"></i> Tüm Cari Hesaplar</h3>
        <button class="btn-success" onclick="showAddModal()">
            <i class="fa fa-plus"></i> Yeni Cari Hesap
        </button>
    </div>
    
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Cari Adı</th>
                <th>Tip</th>
                <th>Telefon</th>
                <th>Bakiye</th>
                <th>Durum</th>
                <th>İşlemler</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($accounts as $account): ?>
            <tr>
                <td><?= $account['id'] ?></td>
                <td><strong><?= htmlspecialchars($account['name']) ?></strong></td>
                <td>
                    <?php if($account['type'] == 'customer'): ?>
                        <span style="background: #d1ecf1; color: #0c5460; padding: 5px 10px; border-radius: 5px; font-size: 12px;">Müşteri</span>
                    <?php else: ?>
                        <span style="background: #fff3cd; color: #856404; padding: 5px 10px; border-radius: 5px; font-size: 12px;">Tedarikçi</span>
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($account['phone'] ?? '-') ?></td>
                <td>
                    <strong style="color: <?= $account['balance'] >= 0 ? '#27ae60' : '#e74c3c' ?>;">
                        <?= number_format($account['balance'], 2) ?> TL
                    </strong>
                </td>
                <td>
                    <?php if($account['balance'] > 0): ?>
                        <span style="background: #dfd; color: #363; padding: 5px 10px; border-radius: 5px; font-size: 12px; font-weight: 600;">Alacak</span>
                    <?php elseif($account['balance'] < 0): ?>
                        <span style="background: #fee; color: #c33; padding: 5px 10px; border-radius: 5px; font-size: 12px; font-weight: 600;">Borç</span>
                    <?php else: ?>
                        <span style="background: #f8f9fa; color: #6c757d; padding: 5px 10px; border-radius: 5px; font-size: 12px; font-weight: 600;">Dengede</span>
                    <?php endif; ?>
                </td>
                <td>
                    <button class="btn-primary" style="padding: 5px 10px; font-size: 12px;" onclick="showStatement(<?= $account['id'] ?>)">
                        <i class="fa fa-file-text"></i> Ekstre
                    </button>
                    <button class="btn-success" style="padding: 5px 10px; font-size: 12px;" onclick="makePayment(<?= $account['id'] ?>)">
                        <i class="fa fa-money"></i> Ödeme
                    </button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
    function showAddModal() {
        alert('Cari hesap ekleme formu açılacak');
    }
    
    function showStatement(id) {
        window.location.href = 'accounts.php?action=statement&id=' + id;
    }
    
    function makePayment(id) {
        alert('Ödeme formu açılacak - Cari ID: ' + id);
    }
</script>

<?php include 'layout/footer.php'; ?>
