
<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

require_once __DIR__ . '/config/koneksi.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = mysqli_real_escape_string(
        $koneksi,
        trim($_POST['email'] ?? '')
    );

    $password = $_POST['password'] ?? '';

    $query = mysqli_query(
        $koneksi,
        "SELECT * FROM t_user WHERE email='$email'"
    );

    if (!$query) {
        die("ERROR QUERY: " . mysqli_error($koneksi));
    }

    $data = mysqli_fetch_assoc($query);

    if ($data) {

        if ($password == $data['password']) {

            $_SESSION['id'] = $data['id'];
            $_SESSION['name'] = $data['name'];
            $_SESSION['role'] = $data['role'];

            header("Location: dashboard.php");
            exit;

        } else {

            $error = "Password salah!";

        }

    } else {

        $error = "Email tidak ditemukan!";

    }
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Login - Sistem Informasi Pelanggaran Siswa</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

</head>

<body class="bg-light">

<div class="container min-vh-100 d-flex justify-content-center align-items-center py-4">

    <div class="row justify-content-center w-100">

        <div class="col-12 col-sm-9 col-md-6 col-lg-4">

            <div class="card border-0 shadow-sm rounded-3">

                <div class="card-body p-4">

                    <!-- LOGO DAN NAMA APLIKASI -->

                    <div class="text-center mb-4">

                        <img
                            src="logo.png"
                            alt="Logo Sekolah"
                            class="img-fluid mb-3"
                            width="90"
                            height="90"
                        >

                        <h5 class="fw-bold text-uppercase mb-2">
                            Sistem Informasi
                            <br>
                            Pelanggaran Siswa
                        </h5>

                        <p class="text-secondary small mb-0">
                            Silakan login untuk melanjutkan
                        </p>

                    </div>

                    <!-- PESAN ERROR -->

                    <?php if ($error != "") { ?>

                        <div
                            class="alert alert-danger py-2 small text-center"
                            role="alert"
                        >
                            <?= htmlspecialchars($error); ?>
                        </div>

                    <?php } ?>

                    <!-- FORM LOGIN -->

                    <form method="POST">

                        <div class="mb-3">

                            <label
                                for="email"
                                class="form-label fw-semibold"
                            >
                                Username / Email
                            </label>

                            <input
                                type="email"
                                class="form-control"
                                id="email"
                                name="email"
                                placeholder="Masukkan email"
                                value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                                autocomplete="username"
                                required
                            >

                        </div>

                        <div class="mb-4">

                            <label
                                for="password"
                                class="form-label fw-semibold"
                            >
                                Password
                            </label>

                            <input
                                type="password"
                                class="form-control"
                                id="password"
                                name="password"
                                placeholder="Masukkan password"
                                autocomplete="current-password"
                                required
                            >

                        </div>

                        <button
                            type="submit"
                            class="btn btn-primary w-100 py-2 fw-semibold"
                        >
                            MASUK
                        </button>

                    </form>

                    <div class="text-center mt-4">

                        <small class="text-secondary">
                            
                        </small>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

</body>

</html>
