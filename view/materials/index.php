<?php require "view/_header.php"; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="page-header">
                <h1>Stok Yönetimi <small>Malzemeler</small></h1>
            </div>

            <?php if(isset($_GET['msg'])): ?>
                <div class="alert alert-success">
                    <?php
                    $messages = [
                        'success' => 'Malzeme başarıyla eklendi!',
                        'updated' => 'Malzeme güncellendi!',
                        'deleted' => 'Malzeme silindi!',
                        'stock_updated' => 'Stok hareketi kaydedildi!'
                    ];
                    echo $messages[$_GET['msg']] ?? 'İşlem başarılı!';
                    ?>
                </div>
            <?php endif; ?>

            <div class="row mb-3">
                <div class="col-md-6">
                    <a href="materials.php?action=create" class="btn btn-primary">
                        <i class="fa fa-plus"></i> Yeni Malzeme Ekle
                    </a>
                </div>
                <div class="col-md-6 text-right">
                    <div class="btn-group">
                        <button class="btn btn-info" data-toggle="modal" data-target="#stockMovementModal">
                            <i class="fa fa-exchange"></i> Stok Hareketi
                        </button>
                    </div>
                </div>
            </div>

            <?php if(!empty($lowStock)): ?>
            <div class="alert alert-warning">
                <strong>⚠️ Düşük Stok Uyarısı!</strong> 
                <?= count($lowStock) ?> malzeme minimum stok seviyesinin altında.
            </div>
            <?php endif; ?>

            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Tüm Malzemeler</h3>
                </div>
                <div class="panel-body">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Malzeme Adı</th>
                                <th>Birim</th>
                                <th>Mevcut Stok</th>
                                <th>Min. Stok</th>
                                <th>Maliyet</th>
                                <th>Durum</th>
                                <th>İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($materials as $mat): ?>
                            <tr class="<?= $mat['current_stock'] <= $mat['min_stock'] ? 'danger' : '' ?>">
                                <td><?= $mat['id'] ?></td>
                                <td><strong><?= htmlspecialchars($mat['name']) ?></strong></td>
                                <td><?= htmlspecialchars($mat['unit']) ?></td>
                                <td>
                                    <span class="badge <?= $mat['current_stock'] <= $mat['min_stock'] ? 'badge-danger' : 'badge-success' ?>">
                                        <?= number_format($mat['current_stock'], 2) ?>
                                    </span>
                                </td>
                                <td><?= number_format($mat['min_stock'], 2) ?></td>
                                <td><?= number_format($mat['cost_price'], 2) ?> TL</td>
                                <td>
                                    <?php if($mat['current_stock'] <= $mat['min_stock']): ?>
                                        <span class="label label-danger">Düşük Stok</span>
                                    <?php else: ?>
                                        <span class="label label-success">Normal</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-xs">
                                        <a href="materials.php?action=history&id=<?= $mat['id'] ?>" class="btn btn-info" title="Geçmiş">
                                            <i class="fa fa-history"></i>
                                        </a>
                                        <a href="materials.php?action=edit&id=<?= $mat['id'] ?>" class="btn btn-warning" title="Düzenle">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        <a href="materials.php?action=delete&id=<?= $mat['id'] ?>" class="btn btn-danger" 
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

<!-- Stok Hareketi Modal -->
<div class="modal fade" id="stockMovementModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="materials.php?action=stock_movement">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Stok Hareketi Ekle</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Malzeme</label>
                        <select name="material_id" class="form-control" required>
                            <option value="">Seçiniz...</option>
                            <?php foreach($materials as $mat): ?>
                                <option value="<?= $mat['id'] ?>"><?= htmlspecialchars($mat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>İşlem Tipi</label>
                        <select name="type" class="form-control" required>
                            <option value="in">Giriş (+)</option>
                            <option value="out">Çıkış (-)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Miktar</label>
                        <input type="number" step="0.01" name="quantity" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Açıklama</label>
                        <textarea name="notes" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary">Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require "view/_footer.php"; ?>
