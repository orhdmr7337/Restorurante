<?php 
$pageTitle = 'Personel Yönetimi';
$activePage = 'users';
include 'layout/header.php'; 
?>

<div class="card">
    <div class="card-header">
        <h3><i class="fa fa-users"></i> Tüm Personel</h3>
        <button class="btn-success" onclick="showAddModal()">
            <i class="fa fa-plus"></i> Yeni Personel Ekle
        </button>
    </div>
    
    <table class="table">
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
                    $positionColors = [1 => '#e74c3c', 2 => '#f39c12', 3 => '#3498db'];
                    $pos = $user['user_position'];
                    ?>
                    <span style="background: <?= $positionColors[$pos] ?? '#95a5a6' ?>; color: white; padding: 5px 10px; border-radius: 5px; font-size: 12px; font-weight: 600;">
                        <?= $positions[$pos] ?? 'Bilinmiyor' ?>
                    </span>
                </td>
                <td>
                    <button class="btn-warning" style="padding: 5px 10px; font-size: 12px;" onclick="editUser(<?= $user['id'] ?>)">
                        <i class="fa fa-edit"></i> Düzenle
                    </button>
                    <?php if($user['id'] != 1): ?>
                    <button class="btn-danger" style="padding: 5px 10px; font-size: 12px;" onclick="deleteUser(<?= $user['id'] ?>)">
                        <i class="fa fa-trash"></i> Sil
                    </button>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
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

<?php include 'layout/footer.php'; ?>
