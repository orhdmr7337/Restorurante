<?php require "view/_header.php"; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="page-header">
                <h1>Alış Faturaları</h1>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <a href="purchases.php?action=create" class="btn btn-primary">
                        <i class="fa fa-plus"></i> Yeni Alış Faturası
                    </a>
                </div>
                <div class="col-md-6">
                    <div class="alert alert-warning text-right" style="margin-bottom: 0;">
                        <strong>Ödenmemiş Fatura:</strong> <?= count($unpaid) ?> adet
                    </div>
                </div>
            </div>

            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Fatura Listesi</h3>
                </div>
                <div class="panel-body">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Fatura No</th>
                                <th>Tedarikçi</th>
                                <th>Tarih</th>
                                <th>Tutar</th>
                                <th>KDV</th>
                                <th>Toplam</th>
                                <th>Ödeme Durumu</th>
                                <th>İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($purchases as $purchase): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($purchase['invoice_number'] ?? 'F-'.$purchase['id']) ?></strong></td>
                                <td><?= htmlspecialchars($purchase['supplier_name']) ?></td>
                                <td><?= date('d.m.Y', strtotime($purchase['purchase_date'])) ?></td>
                                <td><?= number_format($purchase['total_amount'] - $purchase['tax_amount'], 2) ?> TL</td>
                                <td><?= number_format($purchase['tax_amount'], 2) ?> TL</td>
                                <td><?= number_format($purchase['total_amount'], 2) ?> TL</td>
                                <td>
                                    <?php
                                    $statusLabels = [
                                        'unpaid' => '<span class="label label-danger">Ödenmedi</span>',
                                        'partial' => '<span class="label label-warning">Kısmi Ödendi</span>',
                                        'paid' => '<span class="label label-success">Ödendi</span>'
                                    ];
                                    echo $statusLabels[$purchase['payment_status']];
                                    ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-xs">
                                        <a href="purchases.php?action=view&id=<?= $purchase['id'] ?>" class="btn btn-info" title="Görüntüle">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        <?php if($purchase['payment_status'] != 'paid'): ?>
                                        <button class="btn btn-success" onclick="showPaymentModal(<?= $purchase['id'] ?>)" title="Ödeme Yap">
                                            <i class="fa fa-money"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require "view/_footer.php"; ?>
