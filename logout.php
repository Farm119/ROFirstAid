<?php
// Logout ออกจากระบบ
if (isset($_GET['logout']) && $_GET['logout'] == 'true') {
    session_start(); // เริ่มต้นการใช้งาน Session
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

?>