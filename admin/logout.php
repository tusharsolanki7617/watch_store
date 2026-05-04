<?php
require_once '../includes/config.php';

if (isAdminLoggedIn()) {
    unset($_SESSION['admin_id']);
    unset($_SESSION['admin_username']);
    unset($_SESSION['admin_name']);
    session_destroy();
}

redirect('../admin/login.php');
?>
