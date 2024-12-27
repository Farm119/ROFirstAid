<?php
session_start();
if (!isset($_SESSION['st_idcard'])) {
    header("Location: login.php");
    exit();
}
?>