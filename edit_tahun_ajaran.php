
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
$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

if ($id <= 0) {
    header("Location: tahun_ajaran.php");
    exit;
}

// AMBIL DATA TAHUN AJARAN
$stmt = mysqli_prepare(
    $koneksi,
    "SELECT * FROM t_tahun_ajaran WHERE id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$query = mysqli_stmt_get_result($stmt);
$tahun = mysqli_fetch_assoc($query);

mysqli_stmt_close($stmt);

if (!$tahun) {
    header("Location: tahun_ajaran.php");
    exit;
}

// PROSES EDIT
if (isset($_POST['simpan'])) {
    $nama = trim($_POST['nama'] ?? '');
    $tanggal_mulai = $_POST['tanggal_mulai'] ?? '';
    $tanggal_selesai = $_POST['tanggal_selesai'] ?? '';
    $status_aktif = $_POST['status_aktif'] ?? '';

    if (
        $nama === '' ||
        $tanggal_mulai === '' ||
        $tanggal_selesai === '' ||
        !in_array($status_aktif, ['0', '1'], true)
    ) {
        $pesan = "Semua data harus diisi dengan benar.";
    } elseif ($tanggal_selesai < $tanggal_mulai) {
        $pesan = "Tanggal selesai tidak boleh sebelum tanggal mulai.";
    } else {
        mysqli_begin_transaction($koneksi);

        try {
            $statusInt = (int) $status_aktif;

            // Jika aktif, nonaktifkan tahun ajaran lainnya.
            if ($statusInt === 1) {
                $nonaktifkan = mysqli_prepare(
                    $koneksi,
                    "UPDATE t_tahun_ajaran
                     SET status_aktif = 0
                     WHERE id != ?"
                );

                if (!$nonaktifkan) {
                    throw new Exception(mysqli_error($koneksi));
                }

                mysqli_stmt_bind_param($nonaktifkan, "i", $id);

                if (!mysqli_stmt_execute($nonaktifkan)) {
                    throw new Exception(mysqli_stmt_error($nonaktifkan));
                }

                mysqli_stmt_close($nonaktifkan);
            }

            // UPDATE DATA TAHUN AJARAN
            $update = mysqli_prepare(
                $koneksi,
                "UPDATE t_tahun_ajaran
                 SET nama = ?,
                     tanggal_mulai = ?,
                     tanggal_selesai = ?,
                     status_aktif = ?
                 WHERE id = ?"
            );

            if (!$update) {
                throw new Exception(mysqli_error($koneksi));
            }

            mysqli_stmt_bind_param(
                $update,
                "sssii",
                $nama,
                $tanggal_mulai,
                $tanggal_selesai,
                $statusInt,
                $id
            );

            if (!mysqli_stmt_execute($update)) {
                throw new Exception(mysqli_stmt_error($update));
            }

            mysqli_stmt_close($update);
            mysqli_commit($koneksi);

            header("Location: tahun_ajaran.php?pesan=edit_berhasil");
            exit;

        } catch (Exception $e) {
            mysqli_rollback($koneksi);
            $pesan = "Gagal mengubah data tahun ajaran.";
        }
    }

    // PERTAHANKAN ISIAN JIKA TERJADI KESALAHAN
    $tahun['nama'] = $nama;
    $tahun['tanggal_mulai'] = $tanggal_mulai;
    $tahun['tanggal_selesai'] = $tanggal_selesai;
    $tahun['status_aktif'] = $status_aktif;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Edit Tahun Ajaran</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
        rel="stylesheet"
    >
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

                <small class="text-secondary">
                    Panel Admin
                </small>
            </div>

            <!-- PROFIL PENGGUNA -->
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
                        <div class="fw-semibold">
                            <?= htmlspecialchars($_SESSION['name'] ?? 'Pengguna'); ?>
                        </div>

                        <small class="text-secondary">
                            ADMIN
                        </small>
                    </div>

                </div>
            </div>

            <div class="text-secondary small fw-semibold mb-3">
                MENU UTAMA
            </div>

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
                    <a href="kelas.php" class="nav-link text-white rounded-2 py-2 px-3">
                        Kelas
                    </a>
                </li>

                <li class="nav-item">
                    <a href="tahun_ajaran.php" class="nav-link active rounded-2 py-2 px-3">
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
            <div class="mb-4 pb-3 border-bottom">
                <h2 class="fw-bold mb-2">
                    Edit Tahun Ajaran
                </h2>

                <p class="text-secondary mb-0">
                    Perbarui data tahun ajaran melalui formulir berikut.
                </p>
            </div>

            <!-- PESAN ERROR -->
            <?php if ($pesan !== ''): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">

                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <?= htmlspecialchars($pesan); ?>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"
                        aria-label="Tutup"
                    ></button>

                </div>
            <?php endif; ?>

            <!-- FORMULIR EDIT -->
            <div class="row justify-content-center">
                <div class="col-12 col-xl-10">

                    <div class="card border-0 shadow-sm rounded-3">

                        <div class="card-header bg-white p-3">
                            <h5 class="fw-bold mb-1 text-primary">
                                Formulir Edit Tahun Ajaran
                            </h5>

                            <small class="text-secondary">
                                Periksa kembali data sebelum menyimpan perubahan.
                            </small>
                        </div>

                        <div class="card-body p-3 p-md-4">

                            <form
                                method="POST"
                                action="edit_tahun_ajaran.php?id=<?= $id; ?>"
                            >

                                <input
                                    type="hidden"
                                    name="id"
                                    value="<?= $id; ?>"
                                >

                                <div class="row g-3">

                                    <!-- NAMA TAHUN AJARAN -->
                                    <div class="col-12">
                                        <label for="nama" class="form-label fw-semibold">
                                            Nama Tahun Ajaran
                                            <span class="text-danger">*</span>
                                        </label>

                                        <input
                                            type="text"
                                            name="nama"
                                            id="nama"
                                            class="form-control"
                                            placeholder="Contoh: 2026/2027"
                                            value="<?= htmlspecialchars($tahun['nama'] ?? ''); ?>"
                                            required
                                        >
                                    </div>

                                    <!-- TANGGAL MULAI -->
                                    <div class="col-12 col-md-6">
                                        <label for="tanggal_mulai" class="form-label fw-semibold">
                                            Tanggal Mulai
                                            <span class="text-danger">*</span>
                                        </label>

                                        <input
                                            type="date"
                                            name="tanggal_mulai"
                                            id="tanggal_mulai"
                                            class="form-control"
                                            value="<?= htmlspecialchars($tahun['tanggal_mulai'] ?? ''); ?>"
                                            required
                                        >
                                    </div>

                                    <!-- TANGGAL SELESAI -->
                                    <div class="col-12 col-md-6">
                                        <label for="tanggal_selesai" class="form-label fw-semibold">
                                            Tanggal Selesai
                                            <span class="text-danger">*</span>
                                        </label>

                                        <input
                                            type="date"
                                            name="tanggal_selesai"
                                            id="tanggal_selesai"
                                            class="form-control"
                                            value="<?= htmlspecialchars($tahun['tanggal_selesai'] ?? ''); ?>"
                                            required
                                        >
                                    </div>

                                    <!-- STATUS -->
                                    <div class="col-12">
                                        <label for="status_aktif" class="form-label fw-semibold">
                                            Status Tahun Ajaran
                                            <span class="text-danger">*</span>
                                        </label>

                                        <select
                                            name="status_aktif"
                                            id="status_aktif"
                                            class="form-select"
                                            required
                                        >
                                            <option
                                                value="1"
                                                <?= (string) $tahun['status_aktif'] === '1' ? 'selected' : ''; ?>
                                            >
                                                Aktif
                                            </option>

                                            <option
                                                value="0"
                                                <?= (string) $tahun['status_aktif'] === '0' ? 'selected' : ''; ?>
                                            >
                                                Tidak Aktif
                                            </option>
                                        </select>

                                        <small class="text-secondary">
                                            Jika status Aktif dipilih, tahun ajaran lainnya akan dinonaktifkan.
                                        </small>
                                    </div>

                                </div>

                                <hr class="my-4">

                                <!-- TOMBOL AKSI -->
                                <div class="d-flex flex-wrap justify-content-end gap-2">

                                    <a href="tahun_ajaran.php" class="btn btn-secondary">
                                        Batal
                                    </a>

                                    <button
                                        type="submit"
                                        name="simpan"
                                        class="btn btn-primary px-4"
                                    >
                                        <i class="bi bi-check-circle me-1"></i>
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
