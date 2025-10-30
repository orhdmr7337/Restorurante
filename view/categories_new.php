<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kategoriler - Restaurant ERP</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="assets/css/modern.css">
</head>
<body>
    <div class="top-bar">
        <h2><i class="fa fa-list"></i> Kategori Yönetimi</h2>
        <a href="admin.php" class="btn-modern btn-primary"><i class="fa fa-arrow-left"></i> Admin Panel</a>
    </div>

    <div style="padding: 25px;">
        <div class="card-modern">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3>Tüm Kategoriler</h3>
                <button class="btn-modern btn-success" onclick="showAddModal()">
                    <i class="fa fa-plus"></i> Yeni Kategori Ekle
                </button>
            </div>

            <div class="stats-grid">
                <?php foreach($categories as $category): ?>
                <div class="stat-card">
                    <div class="icon"><i class="fa fa-folder"></i></div>
                    <h3><?= htmlspecialchars($category['name']) ?></h3>
                    <div style="margin-top: 15px;">
                        <button class="btn btn-sm btn-warning" onclick="editCategory(<?= $category['id'] ?>)">
                            <i class="fa fa-edit"></i> Düzenle
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="deleteCategory(<?= $category['id'] ?>)">
                            <i class="fa fa-trash"></i> Sil
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script>
        function showAddModal() {
            const name = prompt('Kategori adı:');
            if(name) {
                window.location.href = 'categoriTasks.php?task=add&name=' + encodeURIComponent(name);
            }
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
</body>
</html>
