<?php
session_start();
include '../koneksi.php';
/** @var mysqli $koneksi */
if (!isset($_SESSION['login']) || $_SESSION['role'] != 'admin') {
    header('Location: ../login.php');
    exit;
}
include 'header.php';
$produk = mysqli_query($koneksi, "SELECT * FROM produk ORDER BY id_produk DESC");
?>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="fw-bold mb-1">Produk</h2>
            <p class="text-secondary mb-0">Kelola daftar furniture dan stok.</p>
        </div><a href="produk_tambah.php" class="btn btn-dark">Tambah Produk</a>
    </div>
    <div class="card shadow-sm">
        <div class="card-body table-responsive">
            <table class="table table-bordered table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>No</th>
                        <th>Nama Produk</th>
                        <th>Harga</th>
                        <th>Stok</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1;
                    while ($d = mysqli_fetch_assoc($produk)) { ?><tr>
                            <td><?= $no++ ?></td>
                            <td><?= htmlspecialchars($d['nama_produk']) ?></td>
                            <td>Rp <?= number_format($d['harga'], 0, ',', '.') ?></td>
                            <td><?= $d['stok'] ?></td>
                            <td><a href="produk_edit.php?id=<?= $d['id_produk'] ?>" class="btn btn-dark btn-sm">Edit</a> <a href="produk_hapus.php?id=<?= $d['id_produk'] ?>" class="btn btn-outline-dark btn-sm" onclick="return confirm('Hapus produk ini?')">Hapus</a></td>
                        </tr><?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>

</html>