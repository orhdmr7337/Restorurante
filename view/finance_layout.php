<?php 
$pageTitle = 'Muhasebe';
$activePage = 'finance';
include 'layout/header.php'; 
?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="icon" style="color: #27ae60;"><i class="fa fa-money"></i></div>
        <h3>Kasa Bakiyesi</h3>
        <div class="value" style="color: #27ae60;"><?= number_format($cashBalance, 2) ?> TL</div>
    </div>
    <div class="stat-card">
        <div class="icon" style="color: #3498db;"><i class="fa fa-bank"></i></div>
        <h3>Banka Bakiyesi</h3>
        <div class="value" style="color: #3498db;"><?= number_format($bankBalance, 2) ?> TL</div>
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

<div class="card" style="text-align: center; background: <?= $dailyReport['profit'] >= 0 ? 'linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%)' : 'linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%)' ?>;">
    <h3 style="color: #2c3e50;">Günlük Kâr/Zarar</h3>
    <h1 style="font-size: 48px; color: <?= $dailyReport['profit'] >= 0 ? '#28a745' : '#dc3545' ?>; margin: 20px 0;">
        <?= number_format($dailyReport['profit'], 2) ?> TL
    </h1>
    <p style="color: #6c757d;"><?= $dailyReport['profit'] >= 0 ? 'Kâr' : 'Zarar' ?></p>
</div>

<div class="card">
    <div class="card-header">
        <h3><i class="fa fa-bolt"></i> Hızlı İşlemler</h3>
    </div>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
        <button class="btn-success" onclick="addIncome()">
            <i class="fa fa-plus-circle"></i> Gelir Ekle
        </button>
        <button class="btn-danger" onclick="addExpense()">
            <i class="fa fa-minus-circle"></i> Gider Ekle
        </button>
        <button class="btn-primary" onclick="viewReports()">
            <i class="fa fa-bar-chart"></i> Raporlar
        </button>
        <button class="btn-warning" onclick="viewTransactions()">
            <i class="fa fa-exchange"></i> Hareketler
        </button>
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

<?php include 'layout/footer.php'; ?>
