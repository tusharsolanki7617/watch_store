<?php
require_once 'includes/config.php';

if (isLoggedIn()) {
    session_destroy();
}

redirect(SITE_URL);
?>
