<?php
include '../koneksi.php';
session_start();
if(!isset($_SESSION['login']) || $_SESSION['role']!='kasir'){header('Location: ../login.php');exit;}
$data=mysqli_query($koneksi,"SELECT p.*,COALESCE((SELECT FLOOR(SUM(d.subtotal)/10000) FROM penjualan pj JOIN detail_penjualan d ON pj.id_penjualan=d.id_penjualan WHERE pj.id_pelanggan=p.id_pelanggan),0) poin FROM pelanggan p ORDER BY p.id_pelanggan DESC");
include 'header.php';
?>
<div class="container py-4"><div class="d-flex justify-content-between align-items-center mb-4"><div><h2 class="fw-bold mb-1">Member</h2><p class="text-secondary mb-0">Data pembeli yang terdaftar sebagai member toko.</p></div><a href="pelanggan_tambah.php" class="btn btn-dark">Tambah Member</a></div>
<div class="card shadow-sm"><div class="card-body"><div class="input-group mb-3"><span class="input-group-text">Cari</span><input type="text" id="cariMember" class="form-control" placeholder="Cari nama atau nomor HP..."></div><div class="table-responsive"><table class="table table-bordered table-hover" id="memberTable"><thead class="table-dark"><tr><th>No</th><th>Nama</th><th>No. HP</th><th>Alamat</th><th>Poin</th></tr></thead><tbody>
<?php $no=1; while($d=mysqli_fetch_assoc($data)): ?><tr><td><?=$no++?></td><td><?=htmlspecialchars($d['nama'])?></td><td><?=htmlspecialchars($d['no_hp'])?></td><td><?=htmlspecialchars($d['alamat'])?></td><td><strong><?=$d['poin']?></strong> poin</td></tr><?php endwhile; ?>
</tbody></table></div></div></div></div>
<script>document.getElementById('cariMember').addEventListener('input',function(){let k=this.value.toLowerCase();document.querySelectorAll('#memberTable tbody tr').forEach(r=>r.classList.toggle('d-none',!r.innerText.toLowerCase().includes(k)));});</script></body></html>