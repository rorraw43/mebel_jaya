<?php
session_start();
include '../koneksi.php';
/** @var mysqli $koneksi */
if (!isset($_SESSION['login']) || $_SESSION['role'] != 'admin') {
    header('Location: ../login.php');
    exit;
}
include 'header.php';
$data = mysqli_query($koneksi, "SELECT p.id_penjualan,p.tanggal,u.username,COALESCE(pl.nama,'Pelanggan Umum') nama,COALESCE(SUM(d.subtotal),0) total FROM penjualan p JOIN users u ON p.id_user=u.id_user LEFT JOIN pelanggan pl ON p.id_pelanggan=pl.id_pelanggan LEFT JOIN detail_penjualan d ON p.id_penjualan=d.id_penjualan GROUP BY p.id_penjualan ORDER BY p.id_penjualan DESC");
?>
<div class="container py-4">
    <h2 class="fw-bold mb-1">Penjualan</h2>
    <p class="text-secondary">Semua transaksi yang dilakukan admin dan kasir.</p>
    <div class="card shadow-sm">
        <div class="card-body table-responsive">
            <table class="table table-bordered table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>No</th>
                        <th>Invoice</th>
                        <th>Tanggal</th>
                        <th>Kasir</th>
                        <th>Pelanggan</th>
                        <th>Total</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody><?php $no = 1;
                        while ($d = mysqli_fetch_assoc($data)) { ?><tr>
                            <td><?= $no++ ?></td>
                            <td>INV-<?= str_pad($d['id_penjualan'], 4, '0', STR_PAD_LEFT) ?></td>
                            <td><?= $d['tanggal'] ?></td>
                            <td><?= htmlspecialchars($d['username']) ?></td>
                            <td><?= htmlspecialchars($d['nama']) ?></td>
                            <td>Rp <?= number_format($d['total'], 0, ',', '.') ?></td>
                            <td><a href="penjualan_invoice.php?id=<?= $d['id_penjualan'] ?>" class="btn btn-dark btn-sm">Invoice</a></td>
                        </tr><?php } ?></tbody>
            </table>
        </div>
    </div>
</div>
</body>

</html>