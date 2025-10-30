<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cari Hesaplar - Restaurant ERP</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="assets/css/modern.css">
</head>
<body>
    <div class="top-bar">
        <h2><i class="fa fa-book"></i> Cari Hesap Yönetimi</h2>
        <a href="admin.php" class="btn-modern btn-primary"><i class="fa fa-arrow-left"></i> Admin Panel</a>
    </div>

    <div style="padding: 25px;">
        <!-- Özet Kartlar -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="icon" style="color: #e74c3c;"><i class="fa fa-arrow-down"></i></div>
                <h3>Toplam Borç</h3>
                <div class="value" style="color: #e74c3c;"><?= number_format($totalDebt, 2) ?> TL</div>
            </div>
            <div class="stat-card">
                <div class="icon" style="color: #56ab2f;"><i class="fa fa-arrow-up"></i></div>
                <h3>Toplam Alacak</h3>
                <div class="value" style="color: #56ab2f;"><?= number_format($totalCredit, 2) ?> TL</div>
            </div>
            <div class="stat-card">
                <div class="icon" style="color: #667eea;"><i class="fa fa-balance-scale"></i></div>
                <h3>Net Durum</h3>
                <div class="value" style="color: <?= ($totalCredit - $totalDebt) >= 0 ? '#56ab2f' : '#e74c3c' ?>;">
                    <?= number_format($totalCredit - $totalDebt, 2) ?> TL
                </div>
            </div>
        </div>

        <div class="card-modern" style="margin-top: 25px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3>Tüm Cari Hesaplar</h3>
                <button class="btn-modern btn-success" onclick="showAddModal()">
                    <i class="fa fa-plus"></i> Yeni Cari Hesap
                </button>
            </div>

            <table class="table table-striped">
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
                                <span class="label label-info">Müşteri</span>
                            <?php else: ?>
                                <span class="label label-warning">Tedarikçi</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($account['phone'] ?? '-') ?></td>
                        <td>
                            <strong style="color: <?= $account['balance'] >= 0 ? '#56ab2f' : '#e74c3c' ?>;">
                                <?= number_format($account['balance'], 2) ?> TL
                            </strong>
                        </td>
                        <td>
                            <?php if($account['balance'] > 0): ?>
                                <span class="label label-success">Alacak</span>
                            <?php elseif($account['balance'] < 0): ?>
                                <span class="label label-danger">Borç</span>
                            <?php else: ?>
                                <span class="label label-default">Dengede</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-info" onclick="showStatement(<?= $account['id'] ?>)">
                                <i class="fa fa-file-text"></i> Ekstre
                            </button>
                            <button class="btn btn-sm btn-success" onclick="makePayment(<?= $account['id'] ?>)">
                                <i class="fa fa-money"></i> Ödeme
                            </button>
                            <button class="btn btn-sm btn-warning" onclick="editAccount(<?= $account['id'] ?>)">
                                <i class="fa fa-edit"></i>
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
            alert('Cari hesap ekleme formu açılacak');
        }
        
        function showStatement(id) {
            window.location.href = 'accounts.php?action=statement&id=' + id;
        }
        
        function makePayment(id) {
            alert('Ödeme formu açılacak - Cari ID: ' + id);
        }
        
        function editAccount(id) {
            alert('Cari hesap düzenleme: ' + id);
        }
    </script>
</body>
</html>
