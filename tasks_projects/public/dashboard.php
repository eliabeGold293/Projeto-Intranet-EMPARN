<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

// Se chegou aqui, redireciona para index_admin.php
header("Location: classes/admin/index_admin.php");
exit();
?>