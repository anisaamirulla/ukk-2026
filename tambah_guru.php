
<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    exit("Anda tidak memiliki akses");
}

require_once __DIR__ . "/config/koneksi.php";

$pesan = "";
$nip = "";
$nama = "";
$email = "";
$status = "1";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nip = trim($_POST["nip"] ?? "");
    $nama = trim($_POST["nama"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $status = $_POST["status_aktif"] ?? "1";
    $password = $_POST["password"] ?? "";
    $konfirmasi_password = $_POST["konfirmasi_password"] ?? "";

    if (
        $nip === "" || $nama === "" || $email === "" ||
        $password === "" || $konfirmasi_password === ""
    ) {
        $pesan = "Semua kolom wajib diisi.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $pesan = "Format email tidak valid.";
    } elseif (!in_array($status, ["0", "1"], true)) {
        $pesan = "Status tidak valid.";
    } elseif (strlen($password) < 8) {
        $pesan = "Password minimal 8 karakter.";
    } elseif ($password !== $konfirmasi_password) {
        $pesan = "Konfirmasi password tidak cocok.";
    } else {
        // Periksa NIP dan email guru
        $cek = mysqli_prepare(
            $koneksi,
            "SELECT id FROM t_guru WHERE nip = ? OR email = ?"
        );

        mysqli_stmt_bind_param($cek, "ss", $nip, $email);
        mysqli_stmt_execute($cek);
        $hasil_cek = mysqli_stmt_get_result($cek);

        if (mysqli_num_rows($hasil_cek) > 0) {
            $pesan = "NIP atau email sudah terdaftar.";
        } else {
            // Periksa email akun pengguna
            $cekUser = mysqli_prepare(
                $koneksi,
                "SELECT id FROM t_user WHERE email = ?"
            );

            mysqli_stmt_bind_param($cekUser, "s", $email);
            mysqli_stmt_execute($cekUser);
            $hasilUser = mysqli_stmt_get_result($cekUser);

            if (mysqli_num_rows($hasilUser) > 0) {
                $pesan = "Email sudah digunakan oleh akun pengguna lain.";
            } else {
                mysqli_begin_transaction($koneksi);

                try {
                    $passwordHash = password_hash(
                        $password,
                        PASSWORD_DEFAULT
                    );

                    $role = "guru";

                    // Simpan akun pengguna
                    $simpanUser = mysqli_prepare(
                        $koneksi,
                        "INSERT INTO t_user (name, email, password, role)
                         VALUES (?, ?, ?, ?)"
                    );

                    mysqli_stmt_bind_param(
                        $simpanUser,
                        "ssss",
                        $nama,
                        $email,
                        $passwordHash,
                        $role
                    );

                    if (!mysqli_stmt_execute($simpanUser)) {
                        throw new Exception(
                            mysqli_stmt_error($simpanUser)
                        );
                    }

                    $user_id = mysqli_insert_id($koneksi);
                    $status_int = (int) $status;

                    // Simpan data guru
                    $simpanGuru = mysqli_prepare(
                        $koneksi,
                        "INSERT INTO t_guru
                        (nip, nama, email, status_aktif, user_id)
                        VALUES (?, ?, ?, ?, ?)"
                    );

                    mysqli_stmt_bind_param(
                        $simpanGuru,
                        "sssii",
                        $nip,
                        $nama,
                        $email,
                        $status_int,
                        $user_id
                    );

                    if (!mysqli_stmt_execute($simpanGuru)) {
                        throw new Exception(
                            mysqli_stmt_error($simpanGuru)
                        );
                    }

                    mysqli_commit($koneksi);

                    header("Location: guru.php");
                    exit;

                } catch (Throwable $e) {
                    mysqli_rollback($koneksi);
                    $pesan = "Data guru gagal disimpan. Periksa struktur database dan pastikan data belum terdaftar.";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Tambah Guru</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>

<body class="bg-light">

<div class="container-fluid">
    <div class="row min-vh-100">

        <!-- SIDEBAR -->
        <aside class="col-md-3 col-lg-2 bg-dark text-white p-3">

            <h4 class="text-center mb-4">Admin Panel</h4>

            <div class="nav nav-pills flex-column gap-2">
                <a href="dashboard.php" class="nav-link text-white">Dashboard</a>
                <a href="siswa.php" class="nav-link text-white">Data Siswa</a>
                <a href="guru.php" class="nav-link active">Data Guru</a>
                <a href="kelas.php" class="nav-link text-white">Data Kelas</a>
                <a href="tahun_ajaran.php" class="nav-link text-white">Tahun Ajaran</a>
                <a href="penempatan_siswa.php" class="nav-link text-white">Penempatan Siswa</a>
                <a href="wali_kelas.php" class="nav-link text-white">Wali Kelas</a>
                <a href="kategori_pelanggaran.php" class="nav-link text-white">Kategori Pelanggaran</a>
                <a href="jenis_pelanggaran.php" class="nav-link text-white">Jenis Pelanggaran</a>
                <a href="laporan.php" class="nav-link text-white">Laporan</a>
                <a href="abautme.php" class="nav-link text-white">About Me</a>

                <hr class="text-secondary">

                <a href="logout.php" class="nav-link text-danger">Logout</a>
            </div>

        </aside>

        <!-- KONTEN -->
        <main class="col-md-9 col-lg-10 p-3 p-md-4">

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                <div>
                    <h2 class="fw-bold mb-1">Tambah Data Guru</h2>
                    <p class="text-secondary mb-0">
                        Silakan isi formulir data guru dengan lengkap.
                    </p>
                </div>

                <a href="guru.php" class="btn btn-outline-secondary">
                    Kembali ke Data Guru
                </a>
            </div>

            <!-- PESAN -->
            <?php if ($pesan !== "") { ?>
                <div class="alert alert-danger alert-dismissible fade show"
                     role="alert">
                    <?= htmlspecialchars($pesan) ?>

                    <button type="button" class="btn-close"
                            data-bs-dismiss="alert"></button>
                </div>
            <?php } ?>

            <!-- FORM -->
            <div class="row justify-content-center">
                <div class="col-12 col-xl-10">

                    <div class="card border-0 shadow-sm">

                        <div class="card-header bg-white p-3">
                            <h5 class="fw-bold text-primary mb-1">
                                Formulir Guru
                            </h5>
                            <small class="text-secondary">
                                Isi informasi guru pada kolom yang tersedia.
                            </small>
                        </div>

                        <div class="card-body p-3 p-md-4">

                            <form method="POST" action="">

                                <div class="row g-3">

                                    <!-- NIP -->
                                    <div class="col-md-6">
                                        <label for="nip" class="form-label fw-semibold">
                                            NIP <span class="text-danger">*</span>
                                        </label>

                                        <input type="text"
                                               name="nip"
                                               id="nip"
                                               class="form-control"
                                               placeholder="Masukkan NIP"
                                               value="<?= htmlspecialchars($nip) ?>"
                                               required>
                                    </div>

                                    <!-- NAMA -->
                                    <div class="col-md-6">
                                        <label for="nama" class="form-label fw-semibold">
                                            Nama Guru <span class="text-danger">*</span>
                                        </label>

                                        <input type="text"
                                               name="nama"
                                               id="nama"
                                               class="form-control"
                                               placeholder="Masukkan nama guru"
                                               value="<?= htmlspecialchars($nama) ?>"
                                               required>
                                    </div>

                                    <!-- EMAIL -->
                                    <div class="col-md-6">
                                        <label for="email" class="form-label fw-semibold">
                                            Email <span class="text-danger">*</span>
                                        </label>

                                        <input type="email"
                                               name="email"
                                               id="email"
                                               class="form-control"
                                               placeholder="Masukkan email guru"
                                               value="<?= htmlspecialchars($email) ?>"
                                               required>
                                    </div>

                                    <!-- PASSWORD -->
                                    <div class="col-md-6">
                                        <label for="password" class="form-label fw-semibold">
                                            Password <span class="text-danger">*</span>
                                        </label>

                                        <input type="password"
                                               name="password"
                                               id="password"
                                               class="form-control"
                                               placeholder="Minimal 8 karakter"
                                               minlength="8"
                                               autocomplete="new-password"
                                               required>
                                    </div>

                                    <!-- KONFIRMASI PASSWORD -->
                                    <div class="col-md-6">
                                        <label for="konfirmasi_password"
                                               class="form-label fw-semibold">
                                            Konfirmasi Password
                                            <span class="text-danger">*</span>
                                        </label>

                                        <input type="password"
                                               name="konfirmasi_password"
                                               id="konfirmasi_password"
                                               class="form-control"
                                               placeholder="Masukkan ulang password"
                                               minlength="8"
                                               autocomplete="new-password"
                                               required>
                                    </div>

                                    <!-- STATUS -->
                                    <div class="col-md-6">
                                        <label for="status_aktif"
                                               class="form-label fw-semibold">
                                            Status <span class="text-danger">*</span>
                                        </label>

                                        <select name="status_aktif"
                                                id="status_aktif"
                                                class="form-select"
                                                required>
                                            <option value="1"
                                                <?= $status === "1" ? "selected" : "" ?>>
                                                Aktif
                                            </option>

                                            <option value="0"
                                                <?= $status === "0" ? "selected" : "" ?>>
                                                Tidak Aktif
                                            </option>
                                        </select>
                                    </div>

                                </div>

                                <hr class="my-4">

                                <div class="d-flex flex-wrap justify-content-end gap-2">
                                    <a href="guru.php" class="btn btn-secondary">
                                        Batal
                                    </a>

                                    <button type="submit"
                                            class="btn btn-primary px-4">
                                        Simpan Data Guru
                                    </button>
                                </div>

                                <p class="text-secondary small mt-3 mb-0">
                                    <span class="text-danger">*</span>
                                    Kolom bertanda bintang wajib diisi.
                                </p>

                            </form>

                        </div>
                    </div>

                </div>
            </div>

        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>