<?php require "view/_header.php"; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="page-header">
                <h1>Tedarikçi Yönetimi</h1>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <a href="suppliers.php?action=create" class="btn btn-primary">
                        <i class="fa fa-plus"></i> Yeni Tedarikçi Ekle
                    </a>
                </div>
                <div class="col-md-6">
                    <div class="alert alert-info text-right" style="margin-bottom: 0;">
                        <strong>Toplam Borç:</strong> <?= number_format($totalDebt, 2) ?> TL
                    </div>
                </div>
            </div>

            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Tedarikçi Listesi</h3>
                </div>
                <div class="panel-body">
                    <table class="table table-striped table-hover">
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
                                        <span class="label label-danger"><?= number_format($supplier['balance'], 2) ?> TL Borç</span>
                                    <?php else: ?>
                                        <span class="label label-success">0.00 TL</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-xs">
                                        <a href="suppliers.php?action=statement&id=<?= $supplier['id'] ?>" class="btn btn-info" title="Ekstre">
                                            <i class="fa fa-file-text"></i>
                                        </a>
                                        <a href="suppliers.php?action=edit&id=<?= $supplier['id'] ?>" class="btn btn-warning" title="Düzenle">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        <a href="suppliers.php?action=delete&id=<?= $supplier['id'] ?>" class="btn btn-danger" 
                                           onclick="return confirm('Silmek istediğinize emin misiniz?')" title="Sil">
                                            <i class="fa fa-trash"></i>
                                        </a>
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
