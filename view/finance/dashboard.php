<?php require "view/_header.php"; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="page-header">
                <h1>Muhasebe Dashboard</h1>
            </div>

            <!-- İstatistik Kartları -->
            <div class="row">
                <div class="col-md-3">
                    <div class="panel panel-success">
                        <div class="panel-heading">
                            <h3 class="panel-title">Kasa Bakiyesi</h3>
                        </div>
                        <div class="panel-body text-center">
                            <h2><?= number_format($cashBalance, 2) ?> TL</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="panel panel-info">
                        <div class="panel-heading">
                            <h3 class="panel-title">Banka Bakiyesi</h3>
                        </div>
                        <div class="panel-body text-center">
                            <h2><?= number_format($bankBalance, 2) ?> TL</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="panel panel-primary">
                        <div class="panel-heading">
                            <h3 class="panel-title">Günlük Gelir</h3>
                        </div>
                        <div class="panel-body text-center">
                            <h2><?= number_format($dailyReport['income'], 2) ?> TL</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="panel panel-warning">
                        <div class="panel-heading">
                            <h3 class="panel-title">Günlük Gider</h3>
                        </div>
                        <div class="panel-body text-center">
                            <h2><?= number_format($dailyReport['expense'], 2) ?> TL</h2>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Günlük Kâr/Zarar -->
            <div class="row">
                <div class="col-md-12">
                    <div class="panel <?= $dailyReport['profit'] >= 0 ? 'panel-success' : 'panel-danger' ?>">
                        <div class="panel-heading">
                            <h3 class="panel-title">Günlük Kâr/Zarar</h3>
                        </div>
                        <div class="panel-body text-center">
                            <h1><?= number_format($dailyReport['profit'], 2) ?> TL</h1>
                            <p><?= $dailyReport['profit'] >= 0 ? 'Kâr' : 'Zarar' ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Aylık Özet -->
            <div class="row">
                <div class="col-md-6">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h3 class="panel-title">Aylık Gelir</h3>
                        </div>
                        <div class="panel-body">
                            <h3><?= number_format($monthlyReport['income'], 2) ?> TL</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h3 class="panel-title">Aylık Gider</h3>
                        </div>
                        <div class="panel-body">
                            <h3><?= number_format($monthlyReport['expense'], 2) ?> TL</h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Hızlı İşlemler -->
            <div class="row">
                <div class="col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h3 class="panel-title">Hızlı İşlemler</h3>
                        </div>
                        <div class="panel-body">
                            <a href="finance.php?action=add_income" class="btn btn-success btn-lg">
                                <i class="fa fa-plus-circle"></i> Gelir Ekle
                            </a>
                            <a href="finance.php?action=add_expense" class="btn btn-danger btn-lg">
                                <i class="fa fa-minus-circle"></i> Gider Ekle
                            </a>
                            <a href="finance.php?action=reports" class="btn btn-info btn-lg">
                                <i class="fa fa-bar-chart"></i> Raporlar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require "view/_footer.php"; ?>
