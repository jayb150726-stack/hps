<?php

session_start();
if ($_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}



include 'config.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $conn->query("DELETE FROM barang WHERE id=$id");
}

header("Location: index.php");
exit;
