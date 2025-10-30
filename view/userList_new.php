<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personel Yönetimi - Restaurant ERP</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="assets/css/modern.css">
</head>
<body>
    <div class="top-bar">
        <h2><i class="fa fa-users"></i> Personel Yönetimi</h2>
        <a href="admin.php" class="btn-modern btn-primary"><i class="fa fa-arrow-left"></i> Admin Panel</a>
    </div>

    <div style="padding: 25px;">
        <div class="card-modern">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3>Tüm Personel</h3>
                <button class="btn-modern btn-success" onclick="showAddModal()">
                    <i class="fa fa-plus"></i> Yeni Personel Ekle
                </button>
            </div>

            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Kullanıcı Adı</th>
                        <th>Ad Soyad</th>
                        <th>E-posta</th>
                        <th>Pozisyon</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($users as $user): ?>
                    <tr>
                        <td><?= $user['id'] ?></td>
                        <td><strong><?= htmlspecialchars($user['username']) ?></strong></td>
                        <td><?= htmlspecialchars($user['fullname']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td>
                            <?php
                            $positions = [1 => 'Admin', 2 => 'Yetkili', 3 => 'Garson'];
                            $positionClass = [1 => 'danger', 2 => 'warning', 3 => 'info'];
                            $pos = $user['user_position'];
                            ?>
                            <span class="label label-<?= $positionClass[$pos] ?? 'default' ?>">
                                <?= $positions[$pos] ?? 'Bilinmiyor' ?>
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-warning" onclick="editUser(<?= $user['id'] ?>)">
                                <i class="fa fa-edit"></i> Düzenle
                            </button>
                            <?php if($user['id'] != 1): ?>
                            <button class="btn btn-sm btn-danger" onclick="deleteUser(<?= $user['id'] ?>)">
                                <i class="fa fa-trash"></i> Sil
                            </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function showAddModal() {
            alert('Personel ekleme formu açılacak');
        }
        
        function editUser(id) {
            window.location.href = 'userTasks.php?task=edit&id=' + id;
        }
        
        function deleteUser(id) {
            if(confirm('Bu personeli silmek istediğinize emin misiniz?')) {
                window.location.href = 'userTasks.php?task=delete&id=' + id;
            }
        }
    </script>
</body>
</html>
