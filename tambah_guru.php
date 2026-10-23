
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

$namaPengguna = htmlspecialchars(
    $_SESSION['name'] ?? 'Pengguna',
    ENT_QUOTES,
    'UTF-8'
);

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
    } elseif (!preg_match('/^[0-9]{18}$/', $nip)) {
        $pesan = "NIP harus terdiri dari tepat 18 digit angka.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $pesan = "Format email tidak valid.";
    } elseif (!in_array($status, ["0", "1"], true)) {
        $pesan = "Status tidak valid.";
    } elseif (strlen($password) < 8) {
        $pesan = "Password minimal 8 karakter.";
    } elseif ($password !== $konfirmasi_password) {
        $pesan = "Konfirmasi password tidak cocok.";
    } else {
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
                        throw new Exception(mysqli_stmt_error($simpanUser));
                    }

                    $user_id = mysqli_insert_id($koneksi);
                    $status_int = (int) $status;

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
                        throw new Exception(mysqli_stmt_error($simpanGuru));
                    }

                    mysqli_commit($koneksi);

                    header("Location: guru.php?pesan=berhasil");
                    exit;

                } catch (Throwable $e) {
                    mysqli_rollback($koneksi);
                    $pesan = "Data guru gagal disimpan. Periksa struktur database dan pastikan data belum terdaftar.";
                }
            }
        }
    }
}

$menuAdmin = [
    ['Dashboard', 'dashboard.php'],
    ['Data Siswa', 'siswa.php'],
    ['Data Guru', 'guru.php'],
    ['Kelas', 'kelas.php'],
    ['Tahun Ajaran', 'tahun_ajaran.php'],
    ['Penempatan Siswa', 'penempatan_siswa.php'],
    ['Wali Kelas', 'wali_kelas.php'],
    ['Kategori Pelanggaran', 'kategori_pelanggaran.php'],
    ['Jenis Pelanggaran', 'jenis_pelanggaran.php'],
    ['Laporan', 'laporan.php'],
    ['About Me', 'abautme.php']
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Tambah Data Guru - Sistem Informasi Pelanggaran Siswa</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container-fluid">
    <div class="row">

        <!-- SIDEBAR -->
        <aside class="col-12 col-md-3 col-lg-2 bg-dark min-vh-100 p-3">

            <div class="text-white mb-4 pt-3 pb-3 border-bottom border-secondary">
                <h5 class="fw-bold mb-2">
                    <i class="bi bi-shield-check me-2"></i>
                    Sistem Pelanggaran
                </h5>
                <small class="text-secondary">Panel Admin</small>
            </div>

            <div class="bg-secondary bg-opacity-25 rounded-3 p-3 mb-4">
                <div class="d-flex align-items-center gap-3">
                    <img
                        src="logo.png"
                        alt="Logo Aplikasi"
                        width="44"
                        height="44"
                        class="rounded-circle bg-white p-1"
                    >

                    <div class="text-white text-break">
                        <div class="fw-semibold"><?= $namaPengguna; ?></div>
                        <small class="text-secondary">ADMIN</small>
                    </div>
                </div>
            </div>

            <div class="text-secondary small fw-semibold mb-3">
                MENU UTAMA
            </div>

            <ul class="nav nav-pills flex-column gap-2">
                <?php foreach ($menuAdmin as $item): ?>
                    <li class="nav-item">
                        <a
                            href="<?= htmlspecialchars($item[1], ENT_QUOTES, 'UTF-8'); ?>"
                            class="nav-link <?= $item[0] === 'Data Guru' ? 'active' : 'text-white'; ?> rounded-2 py-2 px-3"
                        >
                            <?= htmlspecialchars($item[0], ENT_QUOTES, 'UTF-8'); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>

            <hr class="border-secondary my-4">

            <a href="logout.php" class="nav-link text-danger rounded-2 py-2 px-3">
                <i class="bi bi-box-arrow-left me-2"></i>
                Logout
            </a>

        </aside>

        <!-- KONTEN TAMBAH GURU -->
        <main class="col-12 col-md-9 col-lg-10 p-3 p-lg-5">

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-4 border-bottom">
                <div>
                    <h2 class="fw-bold mb-2">Tambah Data Guru</h2>
                    <p class="text-secondary mb-0">
                        Silakan isi formulir data guru dengan lengkap.
                    </p>
                </div>
            </div>

            <!-- NOTIFIKASI ERROR -->
            <?php if ($pesan !== ""): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($pesan, ENT_QUOTES, 'UTF-8'); ?>
                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"
                        aria-label="Tutup"
                    ></button>
                </div>
            <?php endif; ?>

            <!-- FORMULIR GURU -->
            <div class="row justify-content-center">
                <div class="col-12 col-xl-10">

                    <div class="card border-0 shadow-sm rounded-3">

                        <div class="card-header bg-white p-3">
                            <h5 class="text-primary fw-bold mb-1">Formulir Guru</h5>
                            <small class="text-secondary">
                                Isi informasi guru pada kolom yang tersedia.
                            </small>
                        </div>

                        <div class="card-body p-4">

                            <form method="POST" action="">

                                <div class="row g-3">

                                    <div class="col-md-6">
                                        <label for="nip" class="form-label">
                                            NIP <span class="text-danger">*</span>
                                        </label>
                                        <input
                                            type="text"
                                            name="nip"
                                            id="nip"
                                            class="form-control"
                                            placeholder="Masukkan 18 digit NIP"
                                            inputmode="numeric"
                                            maxlength="18"
                                            minlength="18"
                                            pattern="[0-9]{18}"
                                            title="NIP harus terdiri dari tepat 18 digit angka"
                                            oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,18)"
                                            value="<?= htmlspecialchars($nip, ENT_QUOTES, 'UTF-8'); ?>"
                                            required
                                        >
                                    </div>

                                    <div class="col-md-6">
                                        <label for="nama" class="form-label">
                                            Nama Guru <span class="text-danger">*</span>
                                        </label>
                                        <input
                                            type="text"
                                            name="nama"
                                            id="nama"
                                            class="form-control"
                                            placeholder="Masukkan nama guru"
                                            value="<?= htmlspecialchars($nama, ENT_QUOTES, 'UTF-8'); ?>"
                                            required
                                        >
                                    </div>

                                    <div class="col-12">
                                        <label for="email" class="form-label">
                                            Email <span class="text-danger">*</span>
                                        </label>
                                        <input
                                            type="email"
                                            name="email"
                                            id="email"
                                            class="form-control"
                                            placeholder="Masukkan email guru"
                                            value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>"
                                            required
                                        >
                                    </div>

                                    <div class="col-md-6">
                                        <label for="password" class="form-label">
                                            Password <span class="text-danger">*</span>
                                        </label>
                                        <input
                                            type="password"
                                            name="password"
                                            id="password"
                                            class="form-control"
                                            placeholder="Minimal 8 karakter"
                                            minlength="8"
                                            autocomplete="new-password"
                                            required
                                        >
                                    </div>

                                    <div class="col-md-6">
                                        <label for="konfirmasi_password" class="form-label">
                                            Konfirmasi Password <span class="text-danger">*</span>
                                        </label>
                                        <input
                                            type="password"
                                            name="konfirmasi_password"
                                            id="konfirmasi_password"
                                            class="form-control"
                                            placeholder="Masukkan ulang password"
                                            minlength="8"
                                            autocomplete="new-password"
                                            required
                                        >
                                    </div>

                                    <div class="col-md-6">
                                        <label for="status_aktif" class="form-label">
                                            Status <span class="text-danger">*</span>
                                        </label>
                                        <select
                                            name="status_aktif"
                                            id="status_aktif"
                                            class="form-select"
                                            required
                                        >
                                            <option value="1" <?= $status === "1" ? "selected" : ""; ?>>
                                                Aktif
                                            </option>
                                            <option value="0" <?= $status === "0" ? "selected" : ""; ?>>
                                                Tidak Aktif
                                            </option>
                                        </select>
                                    </div>

                                </div>

                                <hr class="my-4">

                                <div class="d-flex justify-content-end gap-2">
                                    <a href="guru.php" class="btn btn-secondary">
                                        Batal
                                    </a>

                                    <button type="submit" class="btn btn-primary">
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

            <div class="text-secondary small mt-5 pt-3 border-top">
                Sistem Informasi Pelanggaran Siswa
            </div>

        </main>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
