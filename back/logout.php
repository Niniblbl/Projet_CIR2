<?php
session_start();
session_destroy();
header('Location: ../back/admin_login.html');
exit;
?>