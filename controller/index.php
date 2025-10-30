<?php
require_once "inc/global.php";

$tblObj = new Table();
$usrObj = new User();

if($usrObj->isLoggedIn() == "")
{
    $usrObj->redirect('login.php');
}
