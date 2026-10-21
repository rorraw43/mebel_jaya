<?php
session_start();
include '../koneksi.php';
/** @var mysqli $koneksi */

if (!isset($_SESSION['login']) || $_SESSION['role'] != 'kasir') {
    header('Location: ../login.php');
    exit;
}
if (isset($_POST['simpan'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $hp = mysqli_real_escape_string($koneksi, $_POST['no_hp']);
    $alamat = mysqli_real_escape_string($koneksi, $_POST['alamat']);
    mysqli_query($koneksi, "INSERT INTO pelanggan(nama,no_hp,alamat) VALUES('$nama','$hp','$alamat')");
    header('Location: pelanggan.php');
    exit;
}
include 'header.php';
?><div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white">Tambah Pelanggan</div>
        <div class="card-body">
            <form method="POST">
                <div class="mb-3"><label class="form-label">Nama</label><input name="nama" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">No. HP</label><input name="no_hp" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Alamat</label><textarea name="alamat" class="form-control" required></textarea></div><button name="simpan" class="btn btn-dark">Simpan</button> <a href="pelanggan.php" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
</div>
</body>

</html>