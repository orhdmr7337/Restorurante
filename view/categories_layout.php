<?php 
$pageTitle = 'Kategori Yönetimi';
$activePage = 'categories';
include 'layout/header.php'; 
?>

<div class="card">
    <div class="card-header">
        <h3><i class="fa fa-list"></i> Tüm Kategoriler</h3>
        <button class="btn-success" onclick="showAddModal()">
            <i class="fa fa-plus"></i> Yeni Kategori Ekle
        </button>
    </div>
    
    <div class="stats-grid">
        <?php foreach($categories as $category): ?>
        <div class="stat-card">
            <div class="icon" style="color: #3498db;"><i class="fa fa-folder"></i></div>
            <h3><?= htmlspecialchars($category['name']) ?></h3>
            <div style="margin-top: 15px;">
                <button class="btn-warning" style="padding: 8px 15px; font-size: 13px; margin: 5px;" onclick="editCategory(<?= $category['id'] ?>)">
                    <i class="fa fa-edit"></i> Düzenle
                </button>
                <button class="btn-danger" style="padding: 8px 15px; font-size: 13px; margin: 5px;" onclick="deleteCategory(<?= $category['id'] ?>)">
                    <i class="fa fa-trash"></i> Sil
                </button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
    function showAddModal() {
        const content = `
            <form id="addCategoryForm">
                <input type="hidden" name="task" value="catAdd">
                <div class="form-group">
                    <label>Kategori Adı *</label>
                    <input type="text" name="catName" placeholder="Örn: Yiyecek, İçecek, Tatlı" required autofocus>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-danger" onclick="closeModal()">İptal</button>
                    <button type="submit" class="btn-success">
                        <i class="fa fa-save"></i> Kaydet
                    </button>
                </div>
            </form>
        `;
        showModal('<i class="fa fa-plus"></i> Yeni Kategori Ekle', content, 'small');
        
        document.getElementById('addCategoryForm').onsubmit = function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('menuTasks.php', {
                method: 'POST',
                body: formData
            }).then(() => {
                closeModal();
                location.reload();
            });
        };
    }
    
    function editCategory(id) {
        window.location.href = 'categoriTasks.php?task=edit&id=' + id;
    }
    
    function deleteCategory(id) {
        if(confirm('Bu kategoriyi silmek istediğinize emin misiniz?')) {
            window.location.href = 'categoriTasks.php?task=delete&id=' + id;
        }
    }
</script>

<?php include 'layout/footer.php'; ?>
