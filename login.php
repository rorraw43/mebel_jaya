<?php
session_start();
if (isset($_SESSION['login'])) {
    if ($_SESSION['role'] == 'admin') {
        header("Location: admin/index.php");
    } else {
        header("Location: kasir/index.php");
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Mebel Jaya</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container">
    <div class="row justify-content-center mt-5">
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h3 class="fw-bold text-center mb-1">Mebel Jaya</h3>
                    <p class="text-secondary text-center mb-4">Login Admin / Kasir</p>

                    <?php if (isset($_GET['error'])) { ?>
                        <div class="alert alert-danger">Username atau password salah.</div>
                    <?php } ?>

                    <form action="proses_login.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <button class="btn btn-dark w-100">Login</button>
                    </form>
                </div>
            </div>
            <div class="text-center mt-3">
                <a href="index.php" class="text-secondary text-decoration-none">Kembali ke halaman utama</a>
            </div>
        </div>
    </div>
</div>
</body>
</html>
