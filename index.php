<?php
include 'koneksi.php';

$query = mysqli_query(
    $koneksi,
    "SELECT * FROM produk
     ORDER BY id_produk DESC"
);

$ringkasan = mysqli_query(
    $koneksi,
    "SELECT
        COUNT(*) AS total_produk,
        SUM(stok) AS total_stok,
        SUM(CASE WHEN stok > 0 THEN 1 ELSE 0 END) AS produk_tersedia
     FROM produk"
);

$info = mysqli_fetch_assoc($ringkasan);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Mebel Jaya</title>

    <link rel="stylesheet" href="css/bootstrap.min.css">
</head>

<body class="bg-light">

<nav class="navbar navbar-dark bg-dark py-3">

    <div class="container">

        <a href="index.php" class="navbar-brand fw-bold">
            MEBEL JAYA
        </a>

        <a href="login.php" class="btn btn-light btn-sm px-3">
            Login
        </a>

    </div>

</nav>


<section class="py-4">

    <div class="container">

        <div class="position-relative overflow-hidden rounded shadow">

            <img
                src="mebel.jpg"
                alt="Mebel Jaya"
                class="img-fluid w-100"
            >

            <div class="position-absolute top-0 start-0 w-100 h-100 bg-dark bg-opacity-25"></div>

            <div class="position-absolute bottom-0 start-0 p-4 p-md-5 text-white">

                <p class="small fw-bold mb-2">
                    SISTEM INFORMASI PENJUALAN
                </p>

                <h1 class="display-6 fw-bold mb-3">
                    MEBEL JAYA
                </h1>

                <a href="login.php" class="btn btn-light px-4">
                    Login Admin / Kasir
                </a>

            </div>

        </div>

    </div>

</section>


<section class="pb-4">

    <div class="container">

        <div class="row g-3">

            <div class="col-md-4">

                <div class="card border-0 shadow-sm h-100">

                    <div class="card-body p-4">

                        <p class="text-muted mb-2">
                            Total Produk
                        </p>

                        <h3 class="fw-bold mb-1">
                            <?= $info['total_produk'] ?>
                        </h3>

                        <small class="text-muted">
                            Produk dalam sistem
                        </small>

                    </div>

                </div>

            </div>


            <div class="col-md-4">

                <div class="card border-0 shadow-sm h-100">

                    <div class="card-body p-4">

                        <p class="text-muted mb-2">
                            Total Stok
                        </p>

                        <h3 class="fw-bold mb-1">
                            <?= $info['total_stok'] ?? 0 ?>
                        </h3>

                        <small class="text-muted">
                            Jumlah seluruh stok
                        </small>

                    </div>

                </div>

            </div>


            <div class="col-md-4">

                <div class="card border-0 shadow-sm h-100">

                    <div class="card-body p-4">

                        <p class="text-muted mb-2">
                            Produk Tersedia
                        </p>

                        <h3 class="fw-bold mb-1">
                            <?= $info['produk_tersedia'] ?>
                        </h3>

                        <small class="text-muted">
                            Produk dengan stok tersedia
                        </small>

                    </div>

                </div>

            </div>

        </div>

    </div>

</section>


<section class="py-4 pb-5">

    <div class="container">

        <div class="card border-0 shadow-sm overflow-hidden">

            <div class="card-body p-4">

                <div class="row align-items-center mb-4">

                    <div class="col-md-7">

                        <div class="d-flex align-items-center gap-2 mb-2">

                            <h2 class="h3 fw-bold mb-0">
                                Daftar Produk
                            </h2>


                        </div>

                        <p class="text-muted mb-0">
                            Informasi harga dan ketersediaan produk Mebel Jaya.
                        </p>

                    </div>

                    <div class="col-md-5 mt-3 mt-md-0">

                        <input
                            type="text"
                            id="cariProduk"
                            class="form-control"
                            placeholder="Cari nama produk..."
                        >

                    </div>

                </div>


                <div class="table-responsive">

                    <table class="table table-hover align-middle mb-0" id="tabelProduk">

                        <thead class="table-dark">

                            <tr>

                                <th class="py-3 ps-3">
                                    No
                                </th>

                                <th class="py-3">
                                    ID Produk
                                </th>

                                <th class="py-3">
                                    Nama Produk
                                </th>

                                <th class="py-3">
                                    Harga
                                </th>

                                <th class="py-3">
                                    Ketersediaan
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            <?php
                            $no = 1;

                            while ($produk = mysqli_fetch_assoc($query)) {
                            ?>

                                <tr>

                                    <td class="ps-3 text-muted">
                                        <?= $no++ ?>
                                    </td>

                                    <td class="text-muted">
                                        #<?= $produk['id_produk'] ?>
                                    </td>

                                    <td class="fw-semibold">
                                        <?= htmlspecialchars($produk['nama_produk']) ?>
                                    </td>

                                    <td class="fw-semibold">
                                        Rp <?= number_format(
                                            $produk['harga'],
                                            0,
                                            ',',
                                            '.'
                                        ) ?>
                                    </td>

                                    <td>

                                        <?php if ($produk['stok'] > 5) { ?>

                                            <span class="badge rounded-pill text-bg-secondary px-3 py-2">
                                                <?= $produk['stok'] ?> Tersedia
                                            </span>

                                        <?php } elseif ($produk['stok'] > 0) { ?>

                                            <span class="badge rounded-pill text-bg-warning px-3 py-2">
                                                <?= $produk['stok'] ?> Terbatas
                                            </span>

                                        <?php } else { ?>

                                            <span class="badge rounded-pill text-bg-dark px-3 py-2">
                                                Habis
                                            </span>

                                        <?php } ?>

                                    </td>

                                </tr>

                            <?php } ?>

                        </tbody>

                    </table>

                </div>


            </div>

        </div>

    </div>

</section>


<footer class="bg-dark text-white py-3">

    <div class="container">

        <div class="d-flex justify-content-between">

            <span class="fw-bold">
                MEBEL JAYA
            </span>

            <span class="text-secondary">
                &copy; 2026
            </span>

        </div>

    </div>

</footer>


<script>
    document.getElementById('cariProduk').addEventListener('keyup', function () {

        let kataKunci = this.value.toLowerCase();
        let baris = document.querySelectorAll('#tabelProduk tbody tr');

        baris.forEach(function (row) {

            let namaProduk = row.cells[2].textContent.toLowerCase();

            if (namaProduk.includes(kataKunci)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }

        });

    });
</script>

</body>

</html>