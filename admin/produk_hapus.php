<?php
session_start();
include '../koneksi.php';
/** @var mysqli $koneksi */

if (!isset($_SESSION['login']) || $_SESSION['role'] != 'admin') {
    header('Location: ../login.php');
    exit;
}
$id = (int)($_GET['id'] ?? 0);
$hapus = mysqli_query($koneksi, "DELETE FROM produk WHERE id_produk=$id");
if (!$hapus) {
    echo "<script>alert('Produk tidak dapat dihapus karena sudah digunakan dalam transaksi.');location='produk.php';</script>";
} else {
    header('Location: produk.php');
}
exit;
