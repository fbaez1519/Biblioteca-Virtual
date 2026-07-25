<?php
session_start();
session_destroy();
header('Location: ' . SITE_URL . 'modules/auth/login.php');
exit;
?>