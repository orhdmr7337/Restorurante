<div class="container">
    <?php
        $userId = $_SESSION['user_session'];
        $userInfo = $usrObj->getOneUser($userId);
        if($userInfo["user_position"] == 1):
        ?>
        <div class="row text-center">
            <p class="pull-left"> Hoşgeldin <b><?=$userInfo["fullname"]; ?>,</b></p>
            <a href="admin.php" class="btn btn-warning">Admin Paneli</a>
            <a href="products.php" class="btn btn-warning">Ürün Yönetimi</a>
            <a href="materials.php" class="btn btn-success">Stok Yönetimi</a>
            <a href="suppliers.php" class="btn btn-info">Tedarikçiler</a>
            <a href="purchases.php" class="btn btn-primary">Alış Faturaları</a>
            <a href="finance.php" class="btn btn-danger">Muhasebe</a>
            <label class="pull-right"><a href="<?=$sitePath?>logout.php?logOut=true"><i class="glyphicon glyphicon-log-out"></i> Çıkış Yap</a></label>
        </div>

        <?php
         elseif ($userInfo["user_position"] == 2):
        ?>
        <div class="row text-center">
            <p class="pull-left"> Hoşgeldin <b><?=$userInfo["fullname"]; ?>,</b></p>
            <a href="products.php" class="btn btn-warning">Ürün Yönetimi</a>
            <a href="materials.php" class="btn btn-success">Stok Yönetimi</a>
            <a href="suppliers.php" class="btn btn-info">Tedarikçiler</a>
            <a href="finance.php" class="btn btn-danger">Muhasebe</a>
            <label class="pull-right"><a href="<?=$sitePath?>logout.php?logOut=true"><i class="glyphicon glyphicon-log-out"></i> Çıkış Yap</a></label>
        </div>
        <?php
        else:
        ?>
        <div class="row">
            <p class="pull-left"> Hoşgeldin <b><?=$userInfo["fullname"]; ?>,</b></p>
            <label class="pull-right"><a href="<?=$sitePath?>logout.php?logOut=true"><i class="glyphicon glyphicon-log-out"></i> Çıkış Yap</a></label>
        </div>
        <?php

        endif;
        ?>


    <hr />
    <?php
    foreach ($tblObj->getAllTables() as $table):
        $btnClass = "btn-default";
        if ($table['status'] == 1) $btnClass = "btn-info";
        ?>
        <a href="table.php?id=<?= $table['id'] ?>"
           class="btn <?= $btnClass ?> btn-lg col-sm-3 col-xs-6"><?= $table['name'] ?></a>
    <?php endforeach; ?>
</div>
