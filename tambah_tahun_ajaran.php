
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
            // Jika tahun ajaran ini aktif, nonaktifkan tahun ajaran lainnya.
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

                <a href="guru.php" class="nav-link text-white">
                    Data Guru
                </a>

                <a href="kelas.php" class="nav-link text-white">
                    Data Kelas
                </a>

                <a href="tahun_ajaran.php" class="nav-link active">
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

        <!-- KONTEN TAMBAH TAHUN AJARAN -->
        <main class="col-md-9 col-lg-10 p-3 p-md-4">

            <!-- JUDUL HALAMAN -->
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">

                <div>
                    <h2 class="fw-bold mb-1">
                        Tambah Tahun Ajaran
                    </h2>

                    <p class="text-secondary mb-0">
                        Silakan isi formulir tahun ajaran dengan lengkap.
                    </p>
                </div>

                <a href="tahun_ajaran.php" class="btn btn-outline-secondary">
                    Kembali ke Data Tahun Ajaran
                </a>

            </div>

            <?php if ($pesan !== '') { ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">

                    <?= htmlspecialchars($pesan); ?>

                    <button type="button" class="btn-close"
                            data-bs-dismiss="alert" aria-label="Tutup"></button>

                </div>
            <?php } ?>

            <!-- FORMULIR -->
            <div class="row justify-content-center">
                <div class="col-12 col-xl-10">

                    <div class="card border-0 shadow-sm">

                        <div class="card-header bg-white p-3">
                            <h5 class="fw-bold mb-1">
                                <span class="text-primary">Formulir Tahun Ajaran</span>
                            </h5>

                            <small class="text-secondary">
                                Isi informasi tahun ajaran pada kolom yang tersedia.
                            </small>
                        </div>

                        <div class="card-body p-3 p-md-4">

                            <form method="POST" action="">

                                <div class="row g-3">

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
                                            value="<?= htmlspecialchars($_POST['nama'] ?? ''); ?>"
                                            required>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="tanggal_mulai" class="form-label fw-semibold">
                                            Tanggal Mulai
                                            <span class="text-danger">*</span>
                                        </label>

                                        <input
                                            type="date"
                                            name="tanggal_mulai"
                                            id="tanggal_mulai"
                                            class="form-control"
                                            value="<?= htmlspecialchars($_POST['tanggal_mulai'] ?? ''); ?>"
                                            required>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="tanggal_selesai" class="form-label fw-semibold">
                                            Tanggal Selesai
                                            <span class="text-danger">*</span>
                                        </label>

                                        <input
                                            type="date"
                                            name="tanggal_selesai"
                                            id="tanggal_selesai"
                                            class="form-control"
                                            value="<?= htmlspecialchars($_POST['tanggal_selesai'] ?? ''); ?>"
                                            required>
                                    </div>

                                    <div class="col-12">
                                        <div class="form-check">
                                            <input
                                                type="checkbox"
                                                name="status_aktif"
                                                id="status_aktif"
                                                class="form-check-input"
                                                value="1"
                                                <?= isset($_POST['status_aktif']) ? 'checked' : ''; ?>>

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

                                    <a href="tahun_ajaran.php" class="btn btn-secondary">
                                        Batal
                                    </a>

                                    <button type="submit" class="btn btn-primary px-4">
                                        Simpan Tahun Ajaran
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
