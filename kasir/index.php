<?php
session_start();
include '../koneksi.php';
if (!isset($_SESSION['login']) || $_SESSION['role'] != 'kasir') { header('Location: ../login.php'); exit; }
$id_user=(int)$_SESSION['id_user'];
$data=mysqli_query($koneksi,"SELECT p.id_penjualan,p.tanggal,COALESCE(pl.nama,'Pelanggan Umum') nama,COALESCE(SUM(d.subtotal),0) total FROM penjualan p LEFT JOIN pelanggan pl ON p.id_pelanggan=pl.id_pelanggan LEFT JOIN detail_penjualan d ON p.id_penjualan=d.id_penjualan WHERE p.id_user=$id_user GROUP BY p.id_penjualan ORDER BY p.id_penjualan DESC LIMIT 8");
$rekap=mysqli_fetch_assoc(mysqli_query($koneksi,"SELECT COUNT(DISTINCT p.id_penjualan) transaksi,COALESCE(SUM(d.jumlah),0) barang,COALESCE(SUM(d.subtotal),0) total FROM penjualan p LEFT JOIN detail_penjualan d ON p.id_penjualan=d.id_penjualan WHERE p.id_user=$id_user"));
$hari=mysqli_fetch_assoc(mysqli_query($koneksi,"SELECT COUNT(DISTINCT p.id_penjualan) transaksi,COALESCE(SUM(d.subtotal),0) total FROM penjualan p LEFT JOIN detail_penjualan d ON p.id_penjualan=d.id_penjualan WHERE p.id_user=$id_user AND p.tanggal=CURDATE()"));
$stok=mysqli_query($koneksi,"SELECT nama_produk,stok FROM produk WHERE stok<=5 ORDER BY stok ASC,nama_produk ASC LIMIT 6");
include 'header.php';
?>
<div class="container py-4">
<div class="d-flex justify-content-between align-items-center mb-4"><div><h2 class="fw-bold mb-1">Dashboard Kasir</h2><p class="text-secondary mb-0">Selamat datang, <?=htmlspecialchars($_SESSION['username'])?>.</p></div><a href="penjualan_tambah.php" class="btn btn-dark">+ Transaksi Baru</a></div>
<div class="row g-3 mb-4">
<?php $cards=[['Transaksi Hari Ini',$hari['transaksi'],'Semua transaksi kamu hari ini'],['Penjualan Hari Ini','Rp '.number_format($hari['total'],0,',','.'),'Pendapatan hari ini'],['Barang Terjual',$rekap['barang'],'Total sepanjang riwayat kasir'],['Total Penjualan','Rp '.number_format($rekap['total'],0,',','.'),'Total sepanjang riwayat kasir']]; foreach($cards as $c): ?><div class="col-md-3"><div class="card shadow-sm h-100"><div class="card-body"><div class="text-secondary"><?=$c[0]?></div><h3 class="fw-bold"><?=$c[1]?></h3><small class="text-secondary"><?=$c[2]?></small></div></div></div><?php endforeach; ?>
</div>
<div class="row g-4">
<div class="col-lg-8"><div class="card shadow-sm"><div class="card-header bg-dark text-white d-flex justify-content-between"><span class="fw-bold">Transaksi Terbaru</span><a href="laporan.php" class="btn btn-light btn-sm">Lihat Laporan</a></div><div class="card-body table-responsive"><table class="table table-bordered table-hover mb-0"><thead class="table-dark"><tr><th>No</th><th>Invoice</th><th>Tanggal</th><th>Pelanggan</th><th>Total</th><th>Aksi</th></tr></thead><tbody>
<?php $no=1; while($d=mysqli_fetch_assoc($data)): ?><tr><td><?=$no++?></td><td>INV-<?=str_pad($d['id_penjualan'],4,'0',STR_PAD_LEFT)?></td><td><?=date('d/m/Y',strtotime($d['tanggal']))?></td><td><?=htmlspecialchars($d['nama'])?></td><td>Rp <?=number_format($d['total'],0,',','.')?></td><td><a href="penjualan_invoice.php?id=<?=$d['id_penjualan']?>" class="btn btn-dark btn-sm">Struk</a></td></tr><?php endwhile; if($no===1): ?><tr><td colspan="6" class="text-center text-secondary">Belum ada transaksi.</td></tr><?php endif; ?></tbody></table></div></div></div>
<div class="col-lg-4"><div class="card shadow-sm h-100"><div class="card-header bg-dark text-white fw-bold">Stok Menipis</div><div class="card-body"><?php if(mysqli_num_rows($stok)>0): while($s=mysqli_fetch_assoc($stok)): ?><div class="d-flex justify-content-between border-bottom py-2"><span><?=htmlspecialchars($s['nama_produk'])?></span><strong><?=$s['stok']?> tersisa</strong></div><?php endwhile; else: ?><p class="text-secondary mb-0">Tidak ada stok yang menipis.</p><?php endif; ?></div></div></div>
</div></div></body></html>
