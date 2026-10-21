<?php
session_start();
include '../koneksi.php';
/** @var mysqli $koneksi */

if (!isset($_SESSION['login']) || $_SESSION['role'] != 'admin') {
    header('Location: ../login.php');
    exit;
}
if (isset($_POST['simpan'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama_produk']);
    $harga = str_replace('.', '', $_POST['harga']);
    $stok = (int)$_POST['stok'];
    mysqli_query($koneksi, "INSERT INTO produk(nama_produk,harga,stok) VALUES('$nama','$harga','$stok')");
    header('Location: produk.php');
    exit;
}
include 'header.php';
?>
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white">Tambah Produk</div>
        <div class="card-body">
            <form method="POST">
                <div class="mb-3"><label class="form-label">Nama Produk</label><input name="nama_produk" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Harga</label><input name="harga" class="form-control" placeholder="Contoh: 4.500.000" required></div>
                <div class="mb-3"><label class="form-label">Stok</label><input type="number" name="stok" min="0" class="form-control" required></div><button name="simpan" class="btn btn-dark">Simpan</button> <a href="produk.php" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
</div>
</body>

</html>