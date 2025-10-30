<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Muhasebe - Restaurant ERP</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="assets/css/modern.css">
</head>
<body>
    <div class="top-bar">
        <h2><i class="fa fa-money"></i> Muhasebe Yönetimi</h2>
        <a href="admin.php" class="btn-modern btn-primary"><i class="fa fa-arrow-left"></i> Admin Panel</a>
    </div>

    <div style="padding: 25px;">
        <!-- İstatistik Kartları -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="icon" style="color: #56ab2f;"><i class="fa fa-money"></i></div>
                <h3>Kasa Bakiyesi</h3>
                <div class="value" style="color: #56ab2f;"><?= number_format($cashBalance, 2) ?> TL</div>
            </div>
            <div class="stat-card">
                <div class="icon" style="color: #4facfe;"><i class="fa fa-bank"></i></div>
                <h3>Banka Bakiyesi</h3>
                <div class="value" style="color: #4facfe;"><?= number_format($bankBalance, 2) ?> TL</div>
            </div>
            <div class="stat-card">
                <div class="icon" style="color: #667eea;"><i class="fa fa-arrow-up"></i></div>
                <h3>Günlük Gelir</h3>
                <div class="value" style="color: #667eea;"><?= number_format($dailyReport['income'], 2) ?> TL</div>
            </div>
            <div class="stat-card">
                <div class="icon" style="color: #e74c3c;"><i class="fa fa-arrow-down"></i></div>
                <h3>Günlük Gider</h3>
                <div class="value" style="color: #e74c3c;"><?= number_format($dailyReport['expense'], 2) ?> TL</div>
            </div>
        </div>

        <!-- Günlük Kâr/Zarar -->
        <div class="card-modern" style="margin-top: 25px; text-align: center; background: <?= $dailyReport['profit'] >= 0 ? 'linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%)' : 'linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%)' ?>;">
            <h3 style="color: #2c3e50;">Günlük Kâr/Zarar</h3>
            <h1 style="font-size: 48px; color: <?= $dailyReport['profit'] >= 0 ? '#28a745' : '#dc3545' ?>;">
                <?= number_format($dailyReport['profit'], 2) ?> TL
            </h1>
            <p style="color: #6c757d;"><?= $dailyReport['profit'] >= 0 ? 'Kâr' : 'Zarar' ?></p>
        </div>

        <!-- Hızlı İşlemler -->
        <div class="card-modern" style="margin-top: 25px;">
            <h3 style="margin-bottom: 20px;">Hızlı İşlemler</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <button class="btn-modern btn-success" onclick="addIncome()">
                    <i class="fa fa-plus-circle"></i> Gelir Ekle
                </button>
                <button class="btn-modern btn-danger" onclick="addExpense()">
                    <i class="fa fa-minus-circle"></i> Gider Ekle
                </button>
                <button class="btn-modern btn-primary" onclick="viewReports()">
                    <i class="fa fa-bar-chart"></i> Raporlar
                </button>
                <button class="btn-modern" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white;" onclick="viewTransactions()">
                    <i class="fa fa-exchange"></i> Hareketler
                </button>
            </div>
        </div>

        <!-- Aylık Özet -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px; margin-top: 25px;">
            <div class="card-modern">
                <h3>Aylık Gelir</h3>
                <h2 style="color: #56ab2f;"><?= number_format($monthlyReport['income'], 2) ?> TL</h2>
            </div>
            <div class="card-modern">
                <h3>Aylık Gider</h3>
                <h2 style="color: #e74c3c;"><?= number_format($monthlyReport['expense'], 2) ?> TL</h2>
            </div>
        </div>
    </div>

    <script>
        function addIncome() {
            alert('Gelir ekleme formu açılacak');
        }
        
        function addExpense() {
            alert('Gider ekleme formu açılacak');
        }
        
        function viewReports() {
            window.location.href = 'reports.php';
        }
        
        function viewTransactions() {
            alert('Hareket listesi açılacak');
        }
    </script>
</body>
</html>
