<?php
session_start();
require_once __DIR__ . "/config/koneksi.php";

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    exit("Anda tidak memiliki akses");
}

mysqli_report(MYSQLI_REPORT_OFF);

$pesan = "";
$jenis_pesan = "danger";

$nama_kelas = "";
$tingkat = "";
$jurusan = "";
$tahun_ajaran_id = "";
$status_aktif = "1";

/* AMBIL DATA TAHUN AJARAN */
$query_tahun = mysqli_query(
    $koneksi,
    "SELECT id, nama, status_aktif
     FROM t_tahun_ajaran
     ORDER BY id DESC"
);

if (!$query_tahun) {
    die("Gagal mengambil data tahun ajaran: " . mysqli_error($koneksi));
}

/* PROSES SIMPAN KELAS */
if (isset($_POST['simpan'])) {
    $nama_kelas = trim($_POST['nama_kelas'] ?? "");
    $tingkat = trim($_POST['tingkat'] ?? "");
    $jurusan = trim($_POST['jurusan'] ?? "");
    $tahun_ajaran_id = trim($_POST['tahun_ajaran_id'] ?? "");
    $status_aktif = (string) ($_POST['status_aktif'] ?? "1");

    if (
        $nama_kelas === "" ||
        $tingkat === "" ||
        $jurusan === "" ||
        $tahun_ajaran_id === "" ||
        !ctype_digit($tahun_ajaran_id) ||
        !in_array($tingkat, ["X", "XI", "XII"], true) ||
        !in_array($status_aktif, ["0", "1"], true)
    ) {
        $pesan = "Semua data wajib diisi dengan benar.";
    } else {
        $id_tahun = (int) $tahun_ajaran_id;
        $status = (int) $status_aktif;

        /* CEK TAHUN AJARAN AKTIF */
        $cek_tahun = mysqli_prepare(
            $koneksi,
            "SELECT id
             FROM t_tahun_ajaran
             WHERE id = ? AND status_aktif = 1"
        );

        if (!$cek_tahun) {
            $pesan = "Gagal memeriksa tahun ajaran.";
        } else {
            mysqli_stmt_bind_param($cek_tahun, "i", $id_tahun);
            mysqli_stmt_execute($cek_tahun);
            $hasil_tahun = mysqli_stmt_get_result($cek_tahun);

            if (mysqli_num_rows($hasil_tahun) === 0) {
                $pesan = "Tahun ajaran tidak ditemukan atau tidak aktif.";
            } else {
                /* CEK KELAS DUPLIKAT */
                $cek_kelas = mysqli_prepare(
                    $koneksi,
                    "SELECT id
                     FROM t_kelas
                     WHERE LOWER(TRIM(nama)) = LOWER(TRIM(?))
                       AND tingkat = ?
                       AND LOWER(TRIM(jurusan)) = LOWER(TRIM(?))
                     LIMIT 1"
                );

                if (!$cek_kelas) {
                    $pesan = "Gagal memeriksa data kelas.";
                } else {
                    mysqli_stmt_bind_param(
                        $cek_kelas,
                        "sss",
                        $nama_kelas,
                        $tingkat,
                        $jurusan
                    );

                    mysqli_stmt_execute($cek_kelas);
                    $hasil_kelas = mysqli_stmt_get_result($cek_kelas);

                    if (mysqli_num_rows($hasil_kelas) > 0) {
                        $pesan = "Kelas sudah terdaftar! Data tidak disimpan.";
                    } else {
                        /*
                         * SIMPAN MENGGUNAKAN KOLOM ASLI T_KELAS
                         * Tidak menambahkan kolom tahun_ajaran_id.
                         */
                        $simpan = mysqli_prepare(
                            $koneksi,
                            "INSERT INTO t_kelas
                             (nama, tingkat, jurusan, status_aktif)
                             VALUES (?, ?, ?, ?)"
                        );

                        if (!$simpan) {
                            $pesan = "Gagal menyiapkan penyimpanan data kelas.";
                        } else {
                            mysqli_stmt_bind_param(
                                $simpan,
                                "sssi",
                                $nama_kelas,
                                $tingkat,
                                $jurusan,
                                $status
                            );

                            if (mysqli_stmt_execute($simpan)) {
                                mysqli_stmt_close($simpan);
                                mysqli_stmt_close($cek_kelas);
                                mysqli_stmt_close($cek_tahun);

                                $_SESSION['pesan_kelas'] = "Data kelas berhasil ditambahkan!";
                                $_SESSION['jenis_pesan_kelas'] = "success";

                                header("Location: kelas.php");
                                exit;
                            } else {
                                $pesan = "Data kelas gagal disimpan.";
                                mysqli_stmt_close($simpan);
                            }
                        }
                    }

                    mysqli_stmt_close($cek_kelas);
                }
            }

            mysqli_stmt_close($cek_tahun);
        }
    }

    /* AMBIL ULANG DATA TAHUN AJARAN */
    $query_tahun = mysqli_query(
        $koneksi,
        "SELECT id, nama, status_aktif
         FROM t_tahun_ajaran
         ORDER BY id DESC"
    );

    if (!$query_tahun) {
        die("Gagal mengambil data tahun ajaran: " . mysqli_error($koneksi));
    }
}

/* DATA PENGGUNA */
$namaPengguna = htmlspecialchars(
    $_SESSION['name'] ?? 'Pengguna',
    ENT_QUOTES,
    'UTF-8'
);

function amanTambahKelas($nilai) {
    return htmlspecialchars(
        (string) ($nilai ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Tambah Data Kelas - Sistem Informasi Pelanggaran Siswa</title>

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

            <!-- INFORMASI PENGGUNA -->
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

            <!-- MENU NAVIGASI -->
            <ul class="nav nav-pills flex-column gap-2">

                <li class="nav-item">
                    <a href="dashboard.php" class="nav-link text-white rounded-2 py-2 px-3">
                        Dashboard
                    </a>
                </li>

                <li class="nav-item">
                    <a href="siswa.php" class="nav-link text-white rounded-2 py-2 px-3">
                        Data Siswa
                    </a>
                </li>

                <li class="nav-item">
                    <a href="guru.php" class="nav-link text-white rounded-2 py-2 px-3">
                        Data Guru
                    </a>
                </li>

                <li class="nav-item">
                    <a href="kelas.php" class="nav-link active rounded-2 py-2 px-3" aria-current="page">
                        Kelas
                    </a>
                </li>

                <li class="nav-item">
                    <a href="tahun_ajaran.php" class="nav-link text-white rounded-2 py-2 px-3">
                        Tahun Ajaran
                    </a>
                </li>

                <li class="nav-item">
                    <a href="penempatan_siswa.php" class="nav-link text-white rounded-2 py-2 px-3">
                        Penempatan Siswa
                    </a>
                </li>

                <li class="nav-item">
                    <a href="wali_kelas.php" class="nav-link text-white rounded-2 py-2 px-3">
                        Wali Kelas
                    </a>
                </li>

                <li class="nav-item">
                    <a href="kategori_pelanggaran.php" class="nav-link text-white rounded-2 py-2 px-3">
                        Kategori Pelanggaran
                    </a>
                </li>

                <li class="nav-item">
                    <a href="jenis_pelanggaran.php" class="nav-link text-white rounded-2 py-2 px-3">
                        Jenis Pelanggaran
                    </a>
                </li>

                <li class="nav-item">
                    <a href="laporan.php" class="nav-link text-white rounded-2 py-2 px-3">
                        Laporan
                    </a>
                </li>

                <li class="nav-item">
                    <a href="abautme.php" class="nav-link text-white rounded-2 py-2 px-3">
                        About Me
                    </a>
                </li>

            </ul>

            <hr class="border-secondary my-4">

            <a href="logout.php" class="nav-link text-danger rounded-2 py-2 px-3">
                <i class="bi bi-box-arrow-left me-2"></i>
                Logout
            </a>

        </aside>

        <!-- KONTEN UTAMA -->
        <main class="col-12 col-md-9 col-lg-10 p-3 p-lg-5">

            <!-- JUDUL HALAMAN -->
            <div class="mb-4 pb-4 border-bottom">
                <h2 class="fw-bold text-dark mb-2">
                    Tambah Data Kelas
                </h2>

                <p class="text-secondary mb-0">
                    Silakan isi formulir data kelas dengan lengkap.
                </p>
            </div>

            <!-- NOTIFIKASI -->
            <?php if ($pesan !== ""): ?>
                <div
                    class="alert alert-<?= amanTambahKelas($jenis_pesan); ?> alert-dismissible fade show"
                    role="alert"
                >
                    <?= amanTambahKelas($pesan); ?>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"
                        aria-label="Tutup"
                    ></button>
                </div>
            <?php endif; ?>

            <!-- FORM TAMBAH KELAS -->
            <div class="row justify-content-center">
                <div class="col-12 col-xl-10">

                    <div class="card border-0 shadow-sm rounded-3">

                        <div class="card-header bg-white p-3">
                            <h5 class="text-primary fw-bold mb-1">
                                Formulir Kelas
                            </h5>

                            <small class="text-secondary">
                                Isi informasi kelas dan tahun ajaran.
                            </small>
                        </div>

                        <div class="card-body p-4">

                            <form method="POST" action="tambah_kelas.php">

                                <div class="row g-4">

                                    <!-- NAMA KELAS -->
                                    <div class="col-md-6">
                                        <label for="nama_kelas" class="form-label fw-semibold">
                                            Nama Kelas <span class="text-danger">*</span>
                                        </label>

                                        <input
                                            type="text"
                                            name="nama_kelas"
                                            id="nama_kelas"
                                            class="form-control"
                                            value="<?= amanTambahKelas($nama_kelas); ?>"
                                            placeholder="Contoh: RPL 1"
                                            required
                                        >
                                    </div>

                                    <!-- TINGKAT -->
                                    <div class="col-md-6">
                                        <label for="tingkat" class="form-label fw-semibold">
                                            Tingkat <span class="text-danger">*</span>
                                        </label>

                                        <select
                                            name="tingkat"
                                            id="tingkat"
                                            class="form-select"
                                            required
                                        >
                                            <option value="">-- Pilih Tingkat --</option>

                                            <?php foreach (["X", "XI", "XII"] as $pilihan): ?>
                                                <option
                                                    value="<?= $pilihan; ?>"
                                                    <?= $tingkat === $pilihan ? "selected" : ""; ?>
                                                >
                                                    <?= $pilihan; ?>
                                                </option>
                                            <?php endforeach; ?>

                                        </select>
                                    </div>

                                    <!-- JURUSAN -->
                                    <div class="col-md-6">
                                        <label for="jurusan" class="form-label fw-semibold">
                                            Jurusan <span class="text-danger">*</span>
                                        </label>

                                        <input
                                            type="text"
                                            name="jurusan"
                                            id="jurusan"
                                            class="form-control"
                                            value="<?= amanTambahKelas($jurusan); ?>"
                                            placeholder="Contoh: Rekayasa Perangkat Lunak"
                                            required
                                        >
                                    </div>

                                    <!-- TAHUN AJARAN -->
                                    <div class="col-md-6">
                                        <label for="tahun_ajaran_id" class="form-label fw-semibold">
                                            Tahun Ajaran <span class="text-danger">*</span>
                                        </label>

                                        <select
                                            name="tahun_ajaran_id"
                                            id="tahun_ajaran_id"
                                            class="form-select"
                                            required
                                        >
                                            <option value="">-- Pilih Tahun Ajaran --</option>

                                            <?php while ($tahun = mysqli_fetch_assoc($query_tahun)): ?>
                                                <?php if ((int) $tahun['status_aktif'] === 1): ?>
                                                    <option
                                                        value="<?= (int) $tahun['id']; ?>"
                                                        <?= (string) $tahun_ajaran_id === (string) $tahun['id'] ? "selected" : ""; ?>
                                                    >
                                                        <?= amanTambahKelas($tahun['nama']); ?>
                                                    </option>
                                                <?php endif; ?>
                                            <?php endwhile; ?>

                                        </select>
                                    </div>

                                    <!-- STATUS -->
                                    <div class="col-md-6">
                                        <label for="status_aktif" class="form-label fw-semibold">
                                            Status Kelas <span class="text-danger">*</span>
                                        </label>

                                        <select
                                            name="status_aktif"
                                            id="status_aktif"
                                            class="form-select"
                                            required
                                        >
                                            <option
                                                value="1"
                                                <?= $status_aktif === "1" ? "selected" : ""; ?>
                                            >
                                                Aktif
                                            </option>

                                            <option
                                                value="0"
                                                <?= $status_aktif === "0" ? "selected" : ""; ?>
                                            >
                                                Tidak Aktif
                                            </option>
                                        </select>
                                    </div>

                                </div>

                                <hr class="my-4">

                                <!-- TOMBOL FORM -->
                                <div class="d-flex flex-wrap justify-content-end gap-2">

                                    <a href="kelas.php" class="btn btn-secondary px-4">
                                        Batal
                                    </a>

                                    <button
                                        type="submit"
                                        name="simpan"
                                        class="btn btn-primary px-4"
                                    >
                                        Simpan Data Kelas
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
