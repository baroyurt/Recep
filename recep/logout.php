<?php
// logout.php - Çıkış Sayfası
session_start();
session_destroy();
header('Location: login.php');
exit;
?>
