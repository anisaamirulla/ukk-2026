            
<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    echo "Anda tidak memiliki akses";
    exit;
}

require_once __DIR__ . "/config/koneksi.php";

$pesan = "";
$nip = "";
$nama = "";
$email = "";
$status = "1";
$user_id = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nip = trim($_POST["nip"] ?? "");
    $nama = trim($_POST["nama"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $status = $_POST["status_aktif"] ?? "1";
    $user_id = $_POST["user_id"] ?? "";

    if ($nip === "" || $nama === "" || $email === "" || $user_id === "") {
        $pesan = "Semua kolom wajib diisi.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $pesan = "Format email tidak valid.";
    } elseif (!in_array($status, ["0", "1"], true)) {
        $pesan = "Status tidak valid.";
    } elseif (!ctype_digit((string)$user_id)) {
        $pesan = "Pilih akun pengguna yang valid.";
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
            $cek_user = mysqli_prepare(
                $koneksi,
                "SELECT id FROM t_user WHERE id = ?"
            );
            $id_user = (int)$user_id;
            mysqli_stmt_bind_param($cek_user, "i", $id_user);
            mysqli_stmt_execute($cek_user);
            $hasil_user = mysqli_stmt_get_result($cek_user);

            if (mysqli_num_rows($hasil_user) === 0) {
                $pesan = "Akun pengguna tidak ditemukan.";
            } else {
                $simpan = mysqli_prepare(
                    $koneksi,
                    "INSERT INTO t_guru
                    (nip, nama, email, status_aktif, user_id)
                    VALUES (?, ?, ?, ?, ?)"
                );

                $status_int = (int)$status;

                mysqli_stmt_bind_param(
                    $simpan,
                    "sssii",
                    $nip,
                    $nama,
                    $email,
                    $status_int,
                    $id_user
                );

                if (mysqli_stmt_execute($simpan)) {
                    header("Location: guru.php");
                    exit;
                } else {
                    $pesan = "Data guru gagal disimpan: " .
                        mysqli_stmt_error($simpan);
                }
            }
        }
    }
}

$akun = mysqli_query(
    $koneksi,
    "SELECT id, name, email FROM t_user ORDER BY name ASC"
);

if (!$akun) {
    die("Gagal mengambil akun pengguna: " . mysqli_error($koneksi));
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

            <h4 class="text-center mb-4">
                Admin Panel
            </h4>

            <div class="nav nav-pills flex-column gap-2">

                <a href="dashboard.php" class="nav-link text-white">
                    Dashboard
                </a>

                <a href="siswa.php" class="nav-link text-white">
                    Data Siswa
                </a>

                <a href="guru.php" class="nav-link active">
                    Data Guru
                </a>

                <a href="kelas.php" class="nav-link text-white">
                    Data Kelas
                </a>

                <a href="tahun_ajaran.php" class="nav-link text-white">
                    Tahun Ajaran
                </a>

                <a href="penempatan_siswa.php" class="nav-link text-white">
                    Penempatan Siswa
                </a>

                <a href="wali_kelas.php" class="nav-link text-white">
                    Wali Kelas
                </a>

                <a href="kategori_pelanggaran.php" class="nav-link text-white">
                    Kategori Pelanggaran
                </a>

                <a href="jenis_pelanggaran.php" class="nav-link text-white">
                    Jenis Pelanggaran
                </a>

                <a href="laporan.php" class="nav-link text-white">
                    Laporan
                </a>

                <a href="abautme.php" class="nav-link text-white">
                    About Me
                </a>

                <hr class="text-secondary">

                <a href="logout.php" class="nav-link text-danger">
                    Logout
                </a>

            </div>

        </aside>

        <!-- KONTEN TAMBAH GURU -->
        <main class="col-md-9 col-lg-10 p-3 p-md-4">

            <!-- JUDUL HALAMAN -->
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">

                <div>
                    <h2 class="fw-bold mb-1">
                        Tambah Data Guru
                    </h2>

                    <p class="text-secondary mb-0">
                        Silakan isi formulir data guru dengan lengkap.
                    </p>
                </div>

                <a href="guru.php" class="btn btn-outline-secondary">
                    Kembali ke Data Guru
                </a>

            </div>

            <!-- PESAN ERROR -->
            <?php if ($pesan != "") { ?>
                <div class="alert alert-danger alert-dismissible fade show"
                     role="alert">

                    <?= htmlspecialchars($pesan); ?>

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="alert"
                            aria-label="Tutup"></button>
                </div>
            <?php } ?>

            <!-- FORMULIR -->
            <div class="row justify-content-center">
                <div class="col-12 col-xl-10">

                    <div class="card border-0 shadow-sm">

                        <div class="card-header bg-white p-3">
                            <h5 class="fw-bold mb-1">
                                <span class="text-primary">Formulir Guru</span>
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

                                    <!-- NAMA GURU -->
                                    <div class="col-md-6">
                                        <label for="nama" class="form-label fw-semibold">
                                            Nama Guru <span class="text-danger">*</span>
                                        </label>

                                        <input type="text"
                                               name="nama"
                                               id="nama"
                                               class="form-control"
                                               placeholder="Masukkan nama lengkap guru"
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

                                    <!-- STATUS -->
                                    <div class="col-md-6">
                                        <label for="status_aktif" class="form-label fw-semibold">
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

                                    <!-- AKUN PENGGUNA -->
                                    <div class="col-12">
                                        <label for="user_id" class="form-label fw-semibold">
                                            Akun Pengguna <span class="text-danger">*</span>
                                        </label>

                                        <select name="user_id"
                                                id="user_id"
                                                class="form-select"
                                                required>

                                            <option value="">
                                                -- Pilih Akun Pengguna --
                                            </option>

                                            <?php while ($u = mysqli_fetch_assoc($akun)) : ?>
                                                <option value="<?= (int)$u['id'] ?>"
                                                    <?= (string)$user_id === (string)$u['id'] ? "selected" : "" ?>>

                                                    <?= htmlspecialchars($u['name']) ?>
                                                    (<?= htmlspecialchars($u['email']) ?>)

                                                </option>
                                            <?php endwhile; ?>

                                        </select>

                                        <div class="form-text">
                                            Pilih akun yang sudah tersedia di database.
                                        </div>
                                    </div>

                                </div>

                                <hr class="my-4">

                                <!-- TOMBOL -->
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
