<?php
session_start();
include '../koneksi.php';
/** @var mysqli $koneksi */

if (!isset($_SESSION['login']) || !in_array($_SESSION['role'], ['kasir', 'admin'], true)) {
    header('Location: ../login.php');
    exit;
}

$id_penjualan = (int)($_GET['id'] ?? 0);
$query = mysqli_query($koneksi, "SELECT p.id_penjualan,p.tanggal,u.username,pl.nama,pl.no_hp,pl.alamat FROM penjualan p JOIN users u ON p.id_user=u.id_user LEFT JOIN pelanggan pl ON p.id_pelanggan=pl.id_pelanggan WHERE p.id_penjualan=$id_penjualan");
$penjualan = mysqli_fetch_assoc($query);
if (!$penjualan) { echo 'Data penjualan tidak ditemukan.'; exit; }

$detail = mysqli_query($koneksi, "SELECT produk.nama_produk,produk.harga,detail_penjualan.jumlah,detail_penjualan.subtotal FROM detail_penjualan JOIN produk ON detail_penjualan.id_produk=produk.id_produk WHERE detail_penjualan.id_penjualan=$id_penjualan ORDER BY detail_penjualan.id_detail ASC");
$bayar = $_SESSION['pembayaran_terakhir'][$id_penjualan] ?? null;
$total = 0;
$items = [];
while ($d = mysqli_fetch_assoc($detail)) { $items[] = $d; $total += (int)$d['subtotal']; }
$member = $penjualan['nama'] ?: 'Pelanggan Umum';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Struk - Mebel Jaya</title>
<link rel="stylesheet" href="../css/bootstrap.min.css">
<style>
body{background:#f4f4f4}.struk{width:380px;max-width:100%;margin:30px auto;background:#fff;padding:24px;font-family:Arial,sans-serif}.garis{border-top:1px dashed #555}.item{display:flex;justify-content:space-between;gap:10px}.muted{color:#666;font-size:13px}@media print{body{background:#fff}.struk{width:80mm;margin:0;padding:4mm;box-shadow:none}.no-print{display:none!important}@page{size:80mm auto;margin:0}}
</style>
</head>
<body>
<div class="struk shadow-sm">
    <div class="text-center">
        <h4 class="fw-bold mb-1">MEBEL JAYA</h4>
        <div class="muted">Toko Furniture & Interior</div>
        <div class="muted">Sistem Informasi Penjualan</div>
    </div>
    <div class="garis my-3"></div>
    <div class="muted">No. Transaksi: <strong>INV-<?= str_pad($id_penjualan,4,'0',STR_PAD_LEFT) ?></strong></div>
    <div class="muted">Tanggal: <?= date('d/m/Y',strtotime($penjualan['tanggal'])) ?></div>
    <div class="muted">Kasir: <?= htmlspecialchars($penjualan['username']) ?></div>
    <div class="muted">Pelanggan: <?= htmlspecialchars($member) ?></div>
    <div class="garis my-3"></div>
    <?php foreach($items as $d){ ?>
        <div class="mb-2">
            <div><?= htmlspecialchars($d['nama_produk']) ?></div>
            <div class="item muted"><span><?= $d['jumlah'] ?> x Rp <?= number_format($d['harga'],0,',','.') ?></span><strong class="text-dark">Rp <?= number_format($d['subtotal'],0,',','.') ?></strong></div>
        </div>
    <?php } ?>
    <div class="garis my-3"></div>
    <div class="item fw-bold fs-5"><span>TOTAL</span><span>Rp <?= number_format($total,0,',','.') ?></span></div>
    <div class="mt-3">
        <div class="item"><span>Pembayaran</span><span><?= $bayar ? strtoupper($bayar['metode']) : 'Tidak dicatat' ?></span></div>
        <?php if($bayar){ ?>
        <div class="item"><span>Bayar</span><span>Rp <?= number_format($bayar['uang_bayar'],0,',','.') ?></span></div>
        <div class="item fw-bold"><span>Kembalian</span><span>Rp <?= number_format($bayar['kembalian'],0,',','.') ?></span></div>
        <?php } ?>
    </div>
    <div class="garis my-3"></div>
    <div class="text-center fw-bold">Terima kasih</div>
    <div class="text-center muted">Simpan struk sebagai bukti transaksi.</div>
</div>
<div class="text-center no-print mb-4">
    <button onclick="window.print()" class="btn btn-dark">Cetak Struk</button>
    <?php if($_SESSION['role']==='kasir'){ ?><a href="penjualan_tambah.php" class="btn btn-outline-dark">Transaksi Baru</a><?php } ?>
    <a href="<?= $_SESSION['role']==='admin' ? 'penjualan.php' : 'index.php' ?>" class="btn btn-outline-secondary">Kembali</a>
</div>
</body></html>
