
<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

require_once __DIR__ . '/config/koneksi.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {

        $error = "Email dan password wajib diisi!";

    } else {

        // Mencari akun berdasarkan email
        $stmt = mysqli_prepare(
            $koneksi,
            "SELECT * FROM t_user WHERE email = ? LIMIT 1"
        );

        if (!$stmt) {
            die("ERROR DATABASE: " . mysqli_error($koneksi));
        }

        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);

        $query = mysqli_stmt_get_result($stmt);
        $data = mysqli_fetch_assoc($query);

        mysqli_stmt_close($stmt);

        if ($data) {

            $password_database = (string) $data['password'];

            // Memeriksa password menggunakan password_verify()
            $password_benar = password_verify(
                $password,
                $password_database
            );

            // Mendukung password lama yang masih berupa teks biasa
            if (
                !$password_benar &&
                hash_equals($password_database, (string) $password)
            ) {

                $password_benar = true;

                // Mengubah password lama menjadi hash
                $password_hash = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

                $update = mysqli_prepare(
                    $koneksi,
                    "UPDATE t_user SET password = ? WHERE id = ?"
                );

                if ($update) {

                    mysqli_stmt_bind_param(
                        $update,
                        "si",
                        $password_hash,
                        $data['id']
                    );

                    mysqli_stmt_execute($update);
                    mysqli_stmt_close($update);
                }
            }

            if ($password_benar) {

                $role = strtolower(trim($data['role'] ?? ''));

                if ($role === 'admin' || $role === 'guru') {

                    session_regenerate_id(true);

                    $_SESSION['id'] = $data['id'];
                    $_SESSION['name'] = $data['name'];
                    $_SESSION['role'] = $role;

                    // Memeriksa status Wali Kelas
                    unset($_SESSION['status_wali_kelas']);

                    if ($role === 'guru') {

                        $stmtGuru = mysqli_prepare(
                            $koneksi,
                            "SELECT id
                             FROM t_guru
                             WHERE user_id = ?
                             AND status_aktif = 1
                             LIMIT 1"
                        );

                        if ($stmtGuru) {

                            mysqli_stmt_bind_param(
                                $stmtGuru,
                                "i",
                                $data['id']
                            );

                            mysqli_stmt_execute($stmtGuru);

                            $hasilGuru = mysqli_stmt_get_result($stmtGuru);
                            $dataGuru = mysqli_fetch_assoc($hasilGuru);

                            mysqli_stmt_close($stmtGuru);

                            if ($dataGuru) {

                                $guruId = (int) $dataGuru['id'];

                                $stmtWali = mysqli_prepare(
                                    $koneksi,
                                    "SELECT id
                                     FROM t_wali_kelas
                                     WHERE guru_id = ?
                                     AND status_aktif = 1
                                     AND tanggal_mulai <= CURDATE()
                                     AND (
                                         tanggal_selesai IS NULL
                                         OR tanggal_selesai >= CURDATE()
                                     )
                                     LIMIT 1"
                                );

                                if ($stmtWali) {

                                    mysqli_stmt_bind_param(
                                        $stmtWali,
                                        "i",
                                        $guruId
                                    );

                                    mysqli_stmt_execute($stmtWali);

                                    $hasilWali = mysqli_stmt_get_result($stmtWali);
                                    $dataWali = mysqli_fetch_assoc($hasilWali);

                                    $_SESSION['status_wali_kelas'] =
                                        $dataWali ? true : false;

                                    mysqli_stmt_close($stmtWali);
                                } else {
                                    $_SESSION['status_wali_kelas'] = false;
                                }

                            } else {
                                $_SESSION['status_wali_kelas'] = false;
                            }

                        } else {
                            $_SESSION['status_wali_kelas'] = false;
                        }
                    }

                    header("Location: dashboard.php");
                    exit;

                } else {

                    $error = "Role akun tidak dikenali!";
                }

            } else {

                $error = "Password salah!";
            }

        } else {

            $error = "Email tidak ditemukan!";
        }
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
                            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                        </div>

                    <?php } ?>

                    <!-- FORM LOGIN -->

                    <form method="POST" action="">

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
                                value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
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

                </div>

            </div>

        </div>

    </div>

</div>

</body>

</html>
