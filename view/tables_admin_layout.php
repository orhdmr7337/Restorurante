<?php 
$pageTitle = 'Masa Yönetimi';
$activePage = 'tables';
include 'layout/header.php'; 
?>

<style>
    .floor-section { margin-bottom: 30px; }
    .floor-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 15px 20px; border-radius: 10px 10px 0 0; display: flex; justify-content: space-between; align-items: center; }
    .floor-header h3 { margin: 0; font-size: 18px; }
    .tables-grid { background: white; padding: 20px; border-radius: 0 0 10px 10px; display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 15px; }
    
    .table-card { background: #f8f9fa; border-radius: 10px; padding: 20px; text-align: center; cursor: move; transition: all 0.3s; border: 2px solid #e0e0e0; position: relative; }
    .table-card:hover { transform: translateY(-3px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
    .table-card.occupied { background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%); border-color: #ff9800; }
    .table-card.dragging { opacity: 0.5; transform: scale(0.95); }
    .table-card.drag-over { border: 3px dashed #667eea; background: #e8eaf6; }
    
    .table-icon { font-size: 40px; margin-bottom: 10px; pointer-events: none; }
    .table-card.empty .table-icon { color: #bbb; }
    .table-card.occupied .table-icon { color: #ff9800; }
    
    .table-name { font-size: 16px; font-weight: 700; margin-bottom: 5px; pointer-events: none; }
    .table-status { font-size: 12px; color: #666; margin-bottom: 8px; pointer-events: none; }
    .table-amount { font-size: 18px; font-weight: 700; color: #e91e63; margin-top: 8px; pointer-events: none; }
    
    .table-actions { margin-top: 10px; display: flex; gap: 5px; justify-content: center; }
    .table-action-btn { padding: 5px 10px; background: #667eea; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 11px; transition: all 0.3s; }
    .table-action-btn:hover { background: #5568d3; transform: scale(1.05); }
    
    .quick-actions { position: fixed; bottom: 30px; right: 30px; display: flex; flex-direction: column; gap: 10px; z-index: 1000; }
    .fab-btn { width: 60px; height: 60px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; font-size: 24px; cursor: pointer; box-shadow: 0 5px 20px rgba(0,0,0,0.3); transition: all 0.3s; }
    .fab-btn:hover { transform: scale(1.1); box-shadow: 0 8px 30px rgba(0,0,0,0.4); }
    
    .table-select-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 10px; max-height: 400px; overflow-y: auto; }
    .table-select-card { background: #f8f9fa; padding: 15px; border-radius: 8px; text-align: center; cursor: pointer; border: 2px solid #e0e0e0; transition: all 0.3s; }
    .table-select-card:hover { border-color: #667eea; background: #e8eaf6; }
    .table-select-card.occupied { background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%); border-color: #ff9800; }
    .table-select-card.disabled { opacity: 0.5; cursor: not-allowed; }
</style>

<?php
// Masaları kata göre grupla
$floorGroups = [];
foreach ($allTables as $table) {
    $floor = $table['floor'] ?? 'Zemin Kat';
    if (!isset($floorGroups[$floor])) {
        $floorGroups[$floor] = [];
    }
    $floorGroups[$floor][] = $table;
}
?>

<?php foreach ($floorGroups as $floorName => $tables): ?>
<div class="floor-section">
    <div class="floor-header">
        <h3><i class="fa fa-building"></i> <?= htmlspecialchars($floorName) ?></h3>
        <div>
            <span style="margin-right: 15px;">
                <i class="fa fa-users"></i> 
                <?= count(array_filter($tables, function($t) { return $t['status'] == 1; })) ?> Dolu
            </span>
            <span>
                <i class="fa fa-check-circle"></i> 
                <?= count(array_filter($tables, function($t) { return $t['status'] == 0; })) ?> Boş
            </span>
        </div>
    </div>
    
    <div class="tables-grid">
        <?php foreach ($tables as $table): ?>
            <?php 
            $isOccupied = $table['status'] == 1;
            $totalAmount = 0;
            
            if ($isOccupied) {
                $tableOrders = $orderObj->getOrdersByTable($table['id']);
                foreach ($tableOrders as $item) {
                    $totalAmount += $item['product_price'] * 1;
                }
            }
            ?>
            <div class="table-card <?= $isOccupied ? 'occupied' : 'empty' ?>" 
                 data-table-id="<?= $table['id'] ?>"
                 data-table-name="<?= htmlspecialchars($table['name']) ?>"
                 data-table-status="<?= $isOccupied ? 'occupied' : 'empty' ?>"
                 draggable="<?= $isOccupied ? 'true' : 'false' ?>"
                 onclick="openTable(<?= $table['id'] ?>)">
                <div class="table-icon">
                    <i class="fa fa-<?= $isOccupied ? 'users' : 'check-circle' ?>"></i>
                </div>
                <div class="table-name">Masa <?= htmlspecialchars($table['name']) ?></div>
                <div class="table-status"><?= $isOccupied ? 'Dolu' : 'Boş' ?></div>
                
                <?php if ($isOccupied): ?>
                    <div class="table-amount"><?= number_format($totalAmount, 2) ?> TL</div>
                    <div class="table-actions" onclick="event.stopPropagation()">
                        <button class="table-action-btn" onclick="showMoveModal(<?= $table['id'] ?>, '<?= htmlspecialchars($table['name']) ?>')">
                            <i class="fa fa-arrows"></i> Taşı
                        </button>
                        <button class="table-action-btn" onclick="showMergeModal(<?= $table['id'] ?>, '<?= htmlspecialchars($table['name']) ?>')">
                            <i class="fa fa-compress"></i> Birleştir
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endforeach; ?>

<script src="assets/js/modal.js"></script>
<script>
    let draggedTable = null;
    
    // Drag & Drop İşlemleri
    document.querySelectorAll('.table-card').forEach(card => {
        card.addEventListener('dragstart', function(e) {
            if (this.getAttribute('data-table-status') === 'occupied') {
                draggedTable = {
                    id: this.getAttribute('data-table-id'),
                    name: this.getAttribute('data-table-name')
                };
                this.classList.add('dragging');
            }
        });
        
        card.addEventListener('dragend', function(e) {
            this.classList.remove('dragging');
            document.querySelectorAll('.table-card').forEach(c => c.classList.remove('drag-over'));
        });
        
        card.addEventListener('dragover', function(e) {
            e.preventDefault();
            if (this.getAttribute('data-table-status') === 'empty' && draggedTable) {
                this.classList.add('drag-over');
            }
        });
        
        card.addEventListener('dragleave', function(e) {
            this.classList.remove('drag-over');
        });
        
        card.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('drag-over');
            
            if (this.getAttribute('data-table-status') === 'empty' && draggedTable) {
                const targetId = this.getAttribute('data-table-id');
                const targetName = this.getAttribute('data-table-name');
                
                if (confirm(`Masa ${draggedTable.name} → Masa ${targetName}\n\nTaşıma işlemi yapılsın mı?`)) {
                    moveTableAction(draggedTable.id, targetId);
                }
            }
        });
    });
    
    function openTable(tableId) {
        window.location.href = 'table.php?id=' + tableId;
    }
    
    function showMoveModal(tableId, tableName) {
        const content = `
            <p style="text-align: center; margin-bottom: 20px;">
                <strong style="font-size: 18px;">Masa ${tableName}</strong> hangi masaya taşınacak?
            </p>
            <div class="table-select-grid">
                ${generateTableSelectCards('empty', tableId)}
            </div>
        `;
        showModal('<i class="fa fa-arrows"></i> Masa Taşı', content, 'large');
        
        // Modal içindeki kartlara tıklama olayı ekle
        setTimeout(() => {
            document.querySelectorAll('.table-select-card').forEach(card => {
                card.addEventListener('click', function() {
                    const targetId = this.getAttribute('data-table-id');
                    const targetName = this.getAttribute('data-table-name');
                    
                    if (confirm(`Masa ${tableName} → Masa ${targetName}\n\nTaşıma işlemi yapılsın mı?`)) {
                        moveTableAction(tableId, targetId);
                    }
                });
            });
        }, 100);
    }
    
    function showMergeModal(tableId, tableName) {
        const content = `
            <p style="text-align: center; margin-bottom: 20px;">
                <strong style="font-size: 18px;">Masa ${tableName}</strong> hangi masa ile birleştirilecek?
            </p>
            <div class="table-select-grid">
                ${generateTableSelectCards('occupied', tableId)}
            </div>
        `;
        showModal('<i class="fa fa-compress"></i> Masa Birleştir', content, 'large');
        
        // Modal içindeki kartlara tıklama olayı ekle
        setTimeout(() => {
            document.querySelectorAll('.table-select-card').forEach(card => {
                card.addEventListener('click', function() {
                    const targetId = this.getAttribute('data-table-id');
                    const targetName = this.getAttribute('data-table-name');
                    
                    if (confirm(`Masa ${tableName} + Masa ${targetName}\n\nBirleştirme işlemi yapılsın mı?`)) {
                        mergeTableAction(tableId, targetId);
                    }
                });
            });
        }, 100);
    }
    
    function showAllTablesModal() {
        const content = `
            <div class="table-select-grid">
                ${generateTableSelectCards('all')}
            </div>
        `;
        showModal('<i class="fa fa-th"></i> Tüm Masalar', content, 'xlarge');
        
        setTimeout(() => {
            document.querySelectorAll('.table-select-card').forEach(card => {
                card.addEventListener('click', function() {
                    const tableId = this.getAttribute('data-table-id');
                    window.location.href = 'table.php?id=' + tableId;
                });
            });
        }, 100);
    }
    
    function generateTableSelectCards(filter, excludeId = null) {
        const cards = [];
        document.querySelectorAll('.table-card').forEach(card => {
            const id = card.getAttribute('data-table-id');
            const name = card.getAttribute('data-table-name');
            const status = card.getAttribute('data-table-status');
            
            if (excludeId && id == excludeId) return;
            
            if (filter === 'all' || filter === status) {
                const isOccupied = status === 'occupied';
                const icon = isOccupied ? 'users' : 'check-circle';
                const statusText = isOccupied ? 'Dolu' : 'Boş';
                const occupiedClass = isOccupied ? 'occupied' : '';
                
                cards.push(`
                    <div class="table-select-card ${occupiedClass}" data-table-id="${id}" data-table-name="${name}">
                        <div style="font-size: 32px; margin-bottom: 8px;">
                            <i class="fa fa-${icon}"></i>
                        </div>
                        <div style="font-weight: 700; margin-bottom: 3px;">Masa ${name}</div>
                        <div style="font-size: 12px; color: #666;">${statusText}</div>
                    </div>
                `);
            }
        });
        return cards.join('');
    }
    
    function moveTableAction(sourceId, targetId) {
        fetch('tableTasks.php?task=move&tableId=' + sourceId + '&targetTableId=' + targetId)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✅ Masa başarıyla taşındı!');
                    location.reload();
                } else {
                    alert('❌ Hata: ' + (data.message || 'Masa taşınamadı'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('❌ Bir hata oluştu!');
            });
        
        closeModal();
    }
    
    function mergeTableAction(sourceId, targetId) {
        fetch('tableTasks.php?task=merge&tableId=' + sourceId + '&targetTableId=' + targetId)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✅ Masalar başarıyla birleştirildi!');
                    location.reload();
                } else {
                    alert('❌ Hata: ' + (data.message || 'Masalar birleştirilemedi'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('❌ Bir hata oluştu!');
            });
        
        closeModal();
    }
</script>

<?php include 'layout/footer.php'; ?>
