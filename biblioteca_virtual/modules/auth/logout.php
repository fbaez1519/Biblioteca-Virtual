<?php
session_start();
session_destroy();
header('Location: http://localhost:8080/biblioteca_virtual/modules/auth/login.php');
exit;
?>