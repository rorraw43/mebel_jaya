<?php
session_start();
include '../koneksi.php';
/** @var mysqli $koneksi */

if (!isset($_SESSION['login']) || $_SESSION['role'] != 'kasir') {
    header('Location: ../login.php');
    exit;
}

if (isset($_POST['simpan'])) {
    $id_user = (int) $_SESSION['id_user'];
    $id_pelanggan = ($_POST['id_pelanggan'] ?? '') === '' ? 'NULL' : (int) $_POST['id_pelanggan'];
    $tanggal = mysqli_real_escape_string($koneksi, $_POST['tanggal'] ?? date('Y-m-d'));
    $jumlahs = $_POST['jumlah'] ?? [];
    $metode = $_POST['metode_pembayaran'] ?? 'cash';
    $uang_bayar = (int) preg_replace('/[^0-9]/', '', $_POST['uang_bayar'] ?? '0');

    if (!in_array($metode, ['cash', 'transfer'], true)) {
        $metode = 'cash';
    }

    $items = [];
    $total = 0;
    foreach ($jumlahs as $id_produk => $jumlah) {
        $id_produk = (int) $id_produk;
        $jumlah = (int) $jumlah;
        if ($id_produk > 0 && $jumlah > 0) {
            $items[$id_produk] = $jumlah;
        }
    }

    if (!$items) {
        echo "<script>alert('Pilih minimal satu produk.');history.back();</script>";
        exit;
    }

    if ($metode === 'cash' && $uang_bayar < 0) {
        $uang_bayar = 0;
    }

    mysqli_begin_transaction($koneksi);
    try {
        mysqli_query($koneksi, "INSERT INTO penjualan(id_user,id_pelanggan,tanggal) VALUES($id_user,$id_pelanggan,'$tanggal')");
        if (mysqli_affected_rows($koneksi) !== 1) {
            throw new Exception('Transaksi tidak dapat dibuat.');
        }
        $id_penjualan = mysqli_insert_id($koneksi);

        foreach ($items as $id_produk => $jumlah) {
            $q = mysqli_query($koneksi, "SELECT nama_produk,harga,stok FROM produk WHERE id_produk=$id_produk FOR UPDATE");
            $p = mysqli_fetch_assoc($q);
            if (!$p) {
                throw new Exception('Produk tidak ditemukan.');
            }
            if ($jumlah > (int)$p['stok']) {
                throw new Exception('Stok produk ' . $p['nama_produk'] . ' tidak mencukupi.');
            }

            $subtotal = (int)$p['harga'] * $jumlah;
            $total += $subtotal;
            mysqli_query($koneksi, "INSERT INTO detail_penjualan(id_penjualan,id_produk,jumlah,subtotal) VALUES($id_penjualan,$id_produk,$jumlah,$subtotal)");
            mysqli_query($koneksi, "UPDATE produk SET stok=stok-$jumlah WHERE id_produk=$id_produk");
        }

        if ($metode === 'cash' && $uang_bayar < $total) {
            throw new Exception('Uang bayar kurang dari total transaksi.');
        }

        $kembalian = $metode === 'cash' ? $uang_bayar - $total : 0;
        mysqli_commit($koneksi);

        // Database sengaja tidak diubah. Informasi pembayaran disimpan sementara
        // di session agar langsung muncul pada struk setelah transaksi selesai.
        $_SESSION['pembayaran_terakhir'][$id_penjualan] = [
            'metode' => $metode,
            'uang_bayar' => $metode === 'cash' ? $uang_bayar : $total,
            'kembalian' => $kembalian,
        ];

        header("Location: penjualan_invoice.php?id=$id_penjualan&baru=1");
        exit;
    } catch (Exception $e) {
        mysqli_rollback($koneksi);
        echo "<script>alert('Penjualan gagal: " . addslashes($e->getMessage()) . "');history.back();</script>";
        exit;
    }
}

include 'header.php';
$pelanggan = mysqli_query($koneksi, "SELECT * FROM pelanggan ORDER BY nama ASC");
$produk = mysqli_query($koneksi, "SELECT * FROM produk WHERE stok > 0 ORDER BY nama_produk ASC");
$produk_data = [];
while ($p = mysqli_fetch_assoc($produk)) {
    $produk_data[] = $p;
}
?>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Tambah Penjualan</h2>
            <p class="text-secondary mb-0">Pilih member atau pelanggan umum, lalu tambahkan beberapa produk ke dalam satu transaksi.</p>
        </div>
        <a href="index.php" class="btn btn-outline-dark">Kembali</a>
    </div>

    <form method="POST" id="formPenjualan">
        <div class="row g-4">
            <div class="col-lg-7">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-dark text-white fw-bold">Informasi Transaksi</div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Pelanggan</label>
                                <select name="id_pelanggan" class="form-select" id="id_pelanggan">
                                    <option value="">Pelanggan Umum</option>
                                    <?php mysqli_data_seek($pelanggan, 0); while ($p = mysqli_fetch_assoc($pelanggan)) { ?>
                                        <option value="<?= $p['id_pelanggan'] ?>"><?= htmlspecialchars($p['nama']) ?> - <?= htmlspecialchars($p['no_hp']) ?></option>
                                    <?php } ?>
                                </select>
                                <small class="text-secondary">Pilih member jika pembeli terdaftar sebagai member.</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Tanggal</label>
                                <input type="date" name="tanggal" value="<?= date('Y-m-d') ?>" class="form-control" required>
                            </div>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h5 class="fw-bold mb-1">Pilih Produk</h5>
                                <small class="text-secondary">Ketik nama produk untuk menemukan barang dengan cepat.</small>
                            </div>
                        </div>
                        <div class="input-group mb-3">
                            <span class="input-group-text">Cari</span>
                            <input type="text" id="cariProduk" class="form-control" placeholder="Cari nama produk..." autocomplete="off">
                        </div>

                        <div class="table-responsive" style="max-height: 430px; overflow-y: auto;">
                            <table class="table table-hover align-middle" id="tabelProduk">
                                <thead class="table-dark" style="position:sticky;top:0;z-index:1;">
                                    <tr><th>Produk</th><th>Harga</th><th>Stok</th><th class="text-end">Aksi</th></tr>
                                </thead>
                                <tbody>
                                <?php foreach ($produk_data as $p) { ?>
                                    <tr class="baris-produk" data-nama="<?= htmlspecialchars(strtolower($p['nama_produk'])) ?>">
                                        <td><?= htmlspecialchars($p['nama_produk']) ?></td>
                                        <td>Rp <?= number_format($p['harga'], 0, ',', '.') ?></td>
                                        <td><?= $p['stok'] ?></td>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-dark btn-sm btn-tambah" data-id="<?= $p['id_produk'] ?>" data-nama="<?= htmlspecialchars($p['nama_produk']) ?>" data-harga="<?= $p['harga'] ?>" data-stok="<?= $p['stok'] ?>">Tambah</button>
                                        </td>
                                    </tr>
                                <?php } ?>
                                </tbody>
                            </table>
                        </div>
                        <div id="tidakDitemukan" class="text-center text-secondary py-4 d-none">Produk tidak ditemukan.</div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-dark text-white fw-bold">Keranjang Belanja</div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead class="table-light"><tr><th>Produk</th><th width="100">Qty</th><th class="text-end">Subtotal</th><th width="45"></th></tr></thead>
                                <tbody id="keranjang"><tr id="keranjangKosong"><td colspan="4" class="text-center text-secondary py-4">Belum ada produk.</td></tr></tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-white">
                        <div class="d-flex justify-content-between fs-5 fw-bold"><span>Total</span><span id="totalText">Rp 0</span></div>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header bg-dark text-white fw-bold">Pembayaran</div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Metode Pembayaran</label>
                            <select name="metode_pembayaran" id="metodePembayaran" class="form-select">
                                <option value="cash">Cash</option>
                                <option value="transfer">Transfer</option>
                            </select>
                        </div>
                        <div id="bagianCash">
                            <label class="form-label fw-semibold">Uang Dibayar</label>
                            <input type="number" min="0" name="uang_bayar" id="uangBayar" class="form-control" placeholder="Masukkan uang yang diterima">
                            <div class="mt-3 d-flex justify-content-between">
                                <span class="text-secondary">Kembalian</span>
                                <strong id="kembalianText">Rp 0</strong>
                            </div>
                        </div>
                        <div id="infoTransfer" class="alert alert-secondary d-none mb-0">Pembayaran dilakukan melalui transfer. Tidak ada perhitungan kembalian.</div>
                        <button type="submit" name="simpan" class="btn btn-dark w-100 mt-4" id="btnSimpan">Simpan & Cetak Struk</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
const keranjang = {};
const formatRupiah = angka => 'Rp ' + Number(angka).toLocaleString('id-ID');

function renderKeranjang() {
    const tbody = document.getElementById('keranjang');
    const keys = Object.keys(keranjang);
    tbody.innerHTML = '';
    let total = 0;
    if (!keys.length) {
        tbody.innerHTML = '<tr><td colspan="4" class="text-center text-secondary py-4">Belum ada produk.</td></tr>';
    }
    keys.forEach(id => {
        const item = keranjang[id];
        const subtotal = item.harga * item.jumlah;
        total += subtotal;
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><strong>${escapeHtml(item.nama)}</strong><input type="hidden" name="jumlah[${id}]" value="${item.jumlah}"></td>
            <td><input type="number" min="1" max="${item.stok}" value="${item.jumlah}" class="form-control form-control-sm input-qty" data-id="${id}"></td>
            <td class="text-end">${formatRupiah(subtotal)}</td>
            <td><button type="button" class="btn btn-sm btn-outline-dark btn-hapus" data-id="${id}">×</button></td>`;
        tbody.appendChild(tr);
    });
    document.getElementById('totalText').textContent = formatRupiah(total);
    hitungKembalian();
}

function escapeHtml(text) {
    return text.replace(/[&<>'"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[c]));
}
function totalKeranjang() {
    return Object.values(keranjang).reduce((sum, item) => sum + item.harga * item.jumlah, 0);
}
function hitungKembalian() {
    const total = totalKeranjang();
    const bayar = Number(document.getElementById('uangBayar').value || 0);
    const kembali = Math.max(0, bayar - total);
    document.getElementById('kembalianText').textContent = formatRupiah(kembali);
}

document.querySelectorAll('.btn-tambah').forEach(btn => {
    btn.addEventListener('click', () => {
        const id = btn.dataset.id;
        if (!keranjang[id]) keranjang[id] = {nama: btn.dataset.nama, harga: Number(btn.dataset.harga), stok: Number(btn.dataset.stok), jumlah: 0};
        if (keranjang[id].jumlah < keranjang[id].stok) keranjang[id].jumlah++;
        renderKeranjang();
    });
});

document.getElementById('keranjang').addEventListener('click', e => {
    if (e.target.classList.contains('btn-hapus')) {
        delete keranjang[e.target.dataset.id];
        renderKeranjang();
    }
});

document.getElementById('keranjang').addEventListener('input', e => {
    if (e.target.classList.contains('input-qty')) {
        const id = e.target.dataset.id;
        let jumlah = Number(e.target.value || 1);
        jumlah = Math.max(1, Math.min(jumlah, keranjang[id].stok));
        keranjang[id].jumlah = jumlah;
        renderKeranjang();
    }
});

document.getElementById('cariProduk').addEventListener('input', function() {
    const keyword = this.value.toLowerCase().trim();
    let terlihat = 0;
    document.querySelectorAll('.baris-produk').forEach(row => {
        const cocok = row.dataset.nama.includes(keyword);
        row.classList.toggle('d-none', !cocok);
        if (cocok) terlihat++;
    });
    document.getElementById('tidakDitemukan').classList.toggle('d-none', terlihat !== 0);
});

document.getElementById('uangBayar').addEventListener('input', hitungKembalian);
document.getElementById('metodePembayaran').addEventListener('change', function() {
    const cash = this.value === 'cash';
    document.getElementById('bagianCash').classList.toggle('d-none', !cash);
    document.getElementById('infoTransfer').classList.toggle('d-none', cash);
    document.getElementById('uangBayar').required = cash;
    if (!cash) document.getElementById('uangBayar').value = '';
    hitungKembalian();
});

document.getElementById('formPenjualan').addEventListener('submit', e => {
    if (!Object.keys(keranjang).length) {
        e.preventDefault();
        alert('Pilih minimal satu produk.');
        return;
    }
    const metode = document.getElementById('metodePembayaran').value;
    if (metode === 'cash' && Number(document.getElementById('uangBayar').value || 0) < totalKeranjang()) {
        e.preventDefault();
        alert('Uang bayar kurang dari total transaksi.');
    }
});
</script>
</body>
</html>
