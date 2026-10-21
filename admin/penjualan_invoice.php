<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') { header('Location: ../login.php'); exit; }
$id = (int)($_GET['id'] ?? 0);
header("Location: ../kasir/penjualan_invoice.php?id=$id");
exit;
