<?php
session_start();
include '../koneksi.php';
/** @var mysqli $koneksi */

if (!isset($_SESSION['login']) || $_SESSION['role'] != 'admin') {
    header('Location: ../login.php');
    exit;
}
$id = (int)($_GET['id'] ?? 0);
$q = mysqli_query($koneksi, "SELECT * FROM produk WHERE id_produk=$id");
$p = mysqli_fetch_assoc($q);
if (!$p) {
    header('Location: produk.php');
    exit;
}
if (isset($_POST['update'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama_produk']);
    $harga = str_replace('.', '', $_POST['harga']);
    $stok = (int)$_POST['stok'];
    mysqli_query($koneksi, "UPDATE produk SET nama_produk='$nama',harga='$harga',stok='$stok' WHERE id_produk=$id");
    header('Location: produk.php');
    exit;
}
include 'header.php';
?>
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white">Edit Produk</div>
        <div class="card-body">
            <form method="POST">
                <div class="mb-3"><label class="form-label">Nama Produk</label><input name="nama_produk" value="<?= htmlspecialchars($p['nama_produk']) ?>" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Harga</label><input name="harga" value="<?= number_format($p['harga'], 0, ',', '.') ?>" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Stok</label><input type="number" name="stok" value="<?= $p['stok'] ?>" min="0" class="form-control" required></div><button name="update" class="btn btn-dark">Simpan Perubahan</button> <a href="produk.php" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
</div>
</body>

</html>