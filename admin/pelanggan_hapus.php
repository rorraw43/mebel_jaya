<?php
session_start();
include '../koneksi.php';
/** @var mysqli $koneksi */

if (!isset($_SESSION['login']) || $_SESSION['role'] != 'admin') {
    header('Location: ../login.php');
    exit;
}
$id = (int)($_GET['id'] ?? 0);
$hapus = mysqli_query($koneksi, "DELETE FROM pelanggan WHERE id_pelanggan=$id");
if (!$hapus) {
    echo "<script>alert('Pelanggan tidak dapat dihapus karena sudah memiliki transaksi.');location='pelanggan.php';</script>";
} else {
    header('Location: pelanggan.php');
}
exit;
