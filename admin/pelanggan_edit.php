<?php
session_start();
include '../koneksi.php';
/** @var mysqli $koneksi */

if (!isset($_SESSION['login']) || $_SESSION['role'] != 'admin') {
    header('Location: ../login.php');
    exit;
}
$id = (int)($_GET['id'] ?? 0);
$q = mysqli_query($koneksi, "SELECT * FROM pelanggan WHERE id_pelanggan=$id");
$p = mysqli_fetch_assoc($q);
if (!$p) {
    header('Location: pelanggan.php');
    exit;
}
if (isset($_POST['update'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $hp = mysqli_real_escape_string($koneksi, $_POST['no_hp']);
    $alamat = mysqli_real_escape_string($koneksi, $_POST['alamat']);
    mysqli_query($koneksi, "UPDATE pelanggan SET nama='$nama',no_hp='$hp',alamat='$alamat' WHERE id_pelanggan=$id");
    header('Location: pelanggan.php');
    exit;
}
include 'header.php';
?><div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white">Edit Pelanggan</div>
        <div class="card-body">
            <form method="POST">
                <div class="mb-3"><label class="form-label">Nama</label><input name="nama" value="<?= htmlspecialchars($p['nama']) ?>" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">No. HP</label><input name="no_hp" value="<?= htmlspecialchars($p['no_hp']) ?>" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Alamat</label><textarea name="alamat" class="form-control" required><?= htmlspecialchars($p['alamat']) ?></textarea></div><button name="update" class="btn btn-dark">Simpan Perubahan</button> <a href="pelanggan.php" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
</div>
</body>

</html>