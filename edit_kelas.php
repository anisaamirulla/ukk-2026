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

mysqli_report(MYSQLI_REPORT_OFF);

$pesan = "";
$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

if ($id <= 0) {
    header("Location: kelas.php");
    exit;
}

// PROSES UPDATE
if (isset($_POST['update'])) {
    $nama = trim($_POST['nama'] ?? '');
    $tingkat = trim($_POST['tingkat'] ?? '');
    $jurusan = trim($_POST['jurusan'] ?? '');
    $status = (string) ($_POST['status_aktif'] ?? '');

    if (
        $nama === '' ||
        $tingkat === '' ||
        $jurusan === '' ||
        !in_array($status, ['0', '1'], true)
    ) {
        $pesan = "Semua kolom wajib diisi.";
    } else {
        $cek = mysqli_prepare(
            $koneksi,
            "SELECT id FROM t_kelas
             WHERE nama = ? AND tingkat = ? AND jurusan = ? AND id != ?"
        );

        if (!$cek) {
            $pesan = "Gagal memeriksa data kelas.";
        } else {
            mysqli_stmt_bind_param($cek, "sssi", $nama, $tingkat, $jurusan, $id);
            mysqli_stmt_execute($cek);
            $hasilCek = mysqli_stmt_get_result($cek);
            $kelasDuplikat = mysqli_num_rows($hasilCek) > 0;
            mysqli_stmt_close($cek);

            if ($kelasDuplikat) {
                $pesan = "Data kelas tersebut sudah tersedia.";
            } else {
                $statusInt = (int) $status;

                $update = mysqli_prepare(
                    $koneksi,
                    "UPDATE t_kelas
                     SET nama = ?, tingkat = ?, jurusan = ?, status_aktif = ?
                     WHERE id = ?"
                );

                if (!$update) {
                    $pesan = "Gagal menyiapkan perubahan data kelas.";
                } else {
                    mysqli_stmt_bind_param(
                        $update,
                        "sssii",
                        $nama,
                        $tingkat,
                        $jurusan,
                        $statusInt,
                        $id
                    );

                    if (mysqli_stmt_execute($update)) {
                        mysqli_stmt_close($update);
                        header("Location: kelas.php?pesan=edit_berhasil");
                        exit;
                    } else {
                        $pesan = "Gagal mengubah data kelas.";
                        mysqli_stmt_close($update);
                    }
                }
            }
        }
    }
}

// AMBIL DATA KELAS
$stmt = mysqli_prepare($koneksi, "SELECT * FROM t_kelas WHERE id = ?");

if (!$stmt) {
    exit("Gagal mengambil data kelas.");
}

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$query = mysqli_stmt_get_result($stmt);

if (!$query || mysqli_num_rows($query) == 0) {
    mysqli_stmt_close($stmt);
    exit("Data kelas tidak ditemukan.");
}

$kelas = mysqli_fetch_assoc($query);
mysqli_stmt_close($stmt);

// PERTAHANKAN ISIAN JIKA VALIDASI GAGAL
if (isset($_POST['update'])) {
    $kelas['nama'] = $_POST['nama'] ?? '';
    $kelas['tingkat'] = $_POST['tingkat'] ?? '';
    $kelas['jurusan'] = $_POST['jurusan'] ?? '';
    $kelas['status_aktif'] = $_POST['status_aktif'] ?? '1';
}

function amanKelas($nilai) {
    return htmlspecialchars((string) ($nilai ?? ''), ENT_QUOTES, 'UTF-8');
}

$namaPengguna = amanKelas($_SESSION['name'] ?? 'Pengguna');
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Edit Data Kelas - Sistem Informasi Pelanggaran Siswa</title>

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

            <!-- MENU SIDEBAR -->
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
                    Edit Data Kelas
                </h2>

                <p class="text-secondary mb-0">
                    Silakan perbarui data kelas dengan lengkap.
                </p>
            </div>

            <!-- FORM EDIT KELAS -->
            <div class="row justify-content-center">
                <div class="col-12 col-xl-10">

                    <div class="card border-0 shadow-sm rounded-3">

                        <div class="card-header bg-white p-3">
                            <h5 class="text-primary fw-bold mb-1">
                                Formulir Kelas
                            </h5>

                            <small class="text-secondary">
                                Perbarui informasi kelas pada kolom yang tersedia.
                            </small>
                        </div>

                        <div class="card-body p-4">

                            <?php if ($pesan !== ''): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <strong>Gagal!</strong>
                                    <?= amanKelas($pesan); ?>

                                    <button
                                        type="button"
                                        class="btn-close"
                                        data-bs-dismiss="alert"
                                        aria-label="Tutup"
                                    ></button>
                                </div>
                            <?php endif; ?>

                            <form method="POST" action="edit_kelas.php?id=<?= $id; ?>">

                                <input type="hidden" name="id" value="<?= $id; ?>">

                                <div class="row g-3">

                                    <div class="col-12">
                                        <label for="nama" class="form-label fw-semibold">
                                            Nama Kelas <span class="text-danger">*</span>
                                        </label>

                                        <input
                                            type="text"
                                            name="nama"
                                            id="nama"
                                            class="form-control"
                                            placeholder="Masukkan nama kelas"
                                            value="<?= amanKelas($kelas['nama']); ?>"
                                            required
                                        >
                                    </div>

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

                                            <?php foreach (['X', 'XI', 'XII'] as $t): ?>
                                                <option
                                                    value="<?= $t; ?>"
                                                    <?= $kelas['tingkat'] === $t ? 'selected' : ''; ?>
                                                >
                                                    <?= $t; ?>
                                                </option>
                                            <?php endforeach; ?>

                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="jurusan" class="form-label fw-semibold">
                                            Jurusan <span class="text-danger">*</span>
                                        </label>

                                        <input
                                            type="text"
                                            name="jurusan"
                                            id="jurusan"
                                            class="form-control"
                                            placeholder="Masukkan jurusan"
                                            value="<?= amanKelas($kelas['jurusan']); ?>"
                                            required
                                        >
                                    </div>

                                    <div class="col-12">
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
                                                <?= (string) $kelas['status_aktif'] === '1' ? 'selected' : ''; ?>
                                            >
                                                Aktif
                                            </option>

                                            <option
                                                value="0"
                                                <?= (string) $kelas['status_aktif'] === '0' ? 'selected' : ''; ?>
                                            >
                                                Tidak Aktif
                                            </option>
                                        </select>
                                    </div>

                                </div>

                                <hr class="my-4">

                                <div class="d-flex flex-wrap justify-content-end gap-2">
                                    <a href="kelas.php" class="btn btn-secondary px-4">
                                        Batal
                                    </a>

                                    <button
                                        type="submit"
                                        name="update"
                                        class="btn btn-primary px-4"
                                    >
                                        Simpan Perubahan
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
