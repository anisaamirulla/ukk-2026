
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

$nama = "";
$tanggal_mulai = "";
$tanggal_selesai = "";
$status_aktif = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $tanggal_mulai = $_POST['tanggal_mulai'] ?? '';
    $tanggal_selesai = $_POST['tanggal_selesai'] ?? '';
    $status_aktif = isset($_POST['status_aktif']) ? 1 : 0;

    if ($nama === '' || $tanggal_mulai === '' || $tanggal_selesai === '') {
        $pesan = "Semua data wajib diisi.";
    } elseif ($tanggal_selesai < $tanggal_mulai) {
        $pesan = "Tanggal selesai tidak boleh sebelum tanggal mulai.";
    } else {
        mysqli_begin_transaction($koneksi);

        try {
            if ($status_aktif === 1) {
                $nonaktifkan = mysqli_query(
                    $koneksi,
                    "UPDATE t_tahun_ajaran SET status_aktif = 0"
                );

                if (!$nonaktifkan) {
                    throw new Exception(mysqli_error($koneksi));
                }
            }

            $stmt = mysqli_prepare(
                $koneksi,
                "INSERT INTO t_tahun_ajaran
                (nama, tanggal_mulai, tanggal_selesai, status_aktif)
                VALUES (?, ?, ?, ?)"
            );

            if (!$stmt) {
                throw new Exception(mysqli_error($koneksi));
            }

            mysqli_stmt_bind_param(
                $stmt,
                "sssi",
                $nama,
                $tanggal_mulai,
                $tanggal_selesai,
                $status_aktif
            );

            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception(mysqli_stmt_error($stmt));
            }

            mysqli_stmt_close($stmt);
            mysqli_commit($koneksi);

            header("Location: tahun_ajaran.php?pesan=berhasil");
            exit;

        } catch (Exception $e) {
            mysqli_rollback($koneksi);
            $pesan = "Gagal menyimpan data tahun ajaran.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tambah Tahun Ajaran</title>

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
                    <img src="logo.png" alt="Logo Aplikasi" width="44" height="44" class="rounded-circle bg-white p-1">

                    <div class="text-white text-break">
                        <div class="fw-semibold">
                            <?= htmlspecialchars($_SESSION['name'] ?? 'Pengguna'); ?>
                        </div>
                        <small class="text-secondary">ADMIN</small>
                    </div>
                </div>
            </div>

            <div class="text-secondary small fw-semibold mb-3">MENU UTAMA</div>

            <ul class="nav nav-pills flex-column gap-2">
                <li class="nav-item"><a href="dashboard.php" class="nav-link text-white rounded-2 py-2 px-3">Dashboard</a></li>
                <li class="nav-item"><a href="siswa.php" class="nav-link text-white rounded-2 py-2 px-3">Data Siswa</a></li>
                <li class="nav-item"><a href="guru.php" class="nav-link text-white rounded-2 py-2 px-3">Data Guru</a></li>
                <li class="nav-item"><a href="kelas.php" class="nav-link text-white rounded-2 py-2 px-3">Kelas</a></li>
                <li class="nav-item"><a href="tahun_ajaran.php" class="nav-link active rounded-2 py-2 px-3">Tahun Ajaran</a></li>
                <li class="nav-item"><a href="penempatan_siswa.php" class="nav-link text-white rounded-2 py-2 px-3">Penempatan Siswa</a></li>
                <li class="nav-item"><a href="wali_kelas.php" class="nav-link text-white rounded-2 py-2 px-3">Wali Kelas</a></li>
                <li class="nav-item"><a href="kategori_pelanggaran.php" class="nav-link text-white rounded-2 py-2 px-3">Kategori Pelanggaran</a></li>
                <li class="nav-item"><a href="jenis_pelanggaran.php" class="nav-link text-white rounded-2 py-2 px-3">Jenis Pelanggaran</a></li>
                <li class="nav-item"><a href="laporan.php" class="nav-link text-white rounded-2 py-2 px-3">Laporan</a></li>
                <li class="nav-item"><a href="abautme.php" class="nav-link text-white rounded-2 py-2 px-3">About Me</a></li>
            </ul>

            <hr class="border-secondary my-4">

            <a href="logout.php" class="nav-link text-danger rounded-2 py-2 px-3">
                <i class="bi bi-box-arrow-left me-2"></i> Logout
            </a>

        </aside>

        <!-- FORM TAMBAH -->
        <main class="col-12 col-md-9 col-lg-10 p-3 p-lg-5">

            <div class="mb-4 pb-3 border-bottom">
                <h2 class="fw-bold mb-2">Tambah Tahun Ajaran</h2>
                <p class="text-secondary mb-0">Silakan isi formulir tahun ajaran dengan lengkap.</p>
            </div>

            <?php if ($pesan !== ''): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <?= htmlspecialchars($pesan); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
                </div>
            <?php endif; ?>

            <div class="row justify-content-center">
                <div class="col-12 col-xl-10">

                    <div class="card border-0 shadow-sm rounded-3">

                        <div class="card-header bg-white p-3">
                            <h5 class="fw-bold mb-1 text-primary">Formulir Tahun Ajaran</h5>
                            <small class="text-secondary">Isi informasi tahun ajaran pada kolom yang tersedia.</small>
                        </div>

                        <div class="card-body p-3 p-md-4">

                            <form method="POST" action="tambah_tahun_ajaran.php">

                                <div class="row g-3">

                                    <div class="col-12">
                                        <label for="nama" class="form-label fw-semibold">
                                            Nama Tahun Ajaran <span class="text-danger">*</span>
                                        </label>
                                        <input
                                            type="text"
                                            name="nama"
                                            id="nama"
                                            class="form-control"
                                            placeholder="Contoh: 2026/2027"
                                            value="<?= htmlspecialchars($nama); ?>"
                                            required
                                        >
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <label for="tanggal_mulai" class="form-label fw-semibold">
                                            Tanggal Mulai <span class="text-danger">*</span>
                                        </label>
                                        <input
                                            type="date"
                                            name="tanggal_mulai"
                                            id="tanggal_mulai"
                                            class="form-control"
                                            value="<?= htmlspecialchars($tanggal_mulai); ?>"
                                            required
                                        >
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <label for="tanggal_selesai" class="form-label fw-semibold">
                                            Tanggal Selesai <span class="text-danger">*</span>
                                        </label>
                                        <input
                                            type="date"
                                            name="tanggal_selesai"
                                            id="tanggal_selesai"
                                            class="form-control"
                                            value="<?= htmlspecialchars($tanggal_selesai); ?>"
                                            required
                                        >
                                    </div>

                                    <div class="col-12">
                                        <div class="form-check">
                                            <input
                                                type="checkbox"
                                                name="status_aktif"
                                                id="status_aktif"
                                                class="form-check-input"
                                                value="1"
                                                <?= $status_aktif === 1 ? 'checked' : ''; ?>
                                            >
                                            <label for="status_aktif" class="form-check-label fw-semibold">
                                                Jadikan tahun ajaran aktif
                                            </label>
                                        </div>
                                        <small class="text-secondary">
                                            Jika dipilih, tahun ajaran lainnya akan dinonaktifkan.
                                        </small>
                                    </div>

                                </div>

                                <hr class="my-4">

                                <div class="d-flex flex-wrap justify-content-end gap-2">
                                    <a href="tahun_ajaran.php" class="btn btn-secondary">Batal</a>
                                    <button type="submit" class="btn btn-primary px-4">Simpan Tahun Ajaran</button>
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
