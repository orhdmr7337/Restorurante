<?php
$usrObj = new User();

// Zaten giriş yapmışsa rol bazlı yönlendirme
if($usrObj->isLoggedIn() != "")
{
    $userId = $_SESSION['user_session'];
    $userInfo = $usrObj->getOneUser($userId);
    
    // Rol bazlı yönlendirme
    if ($userInfo['user_position'] == 1) {
        // Admin -> Admin paneline
        redirect('admin.php');
    } else {
        // Diğerleri -> Masalar sayfasına
        redirect('index.php');
    }
}
