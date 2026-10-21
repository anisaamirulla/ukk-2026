
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

if (!$query || mysqli_num_rows($query) == 0) {
    header("Location: tahun_ajaran.php");
    exit;
}

$tahun = mysqli_fetch_assoc($query);

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
        $update = mysqli_prepare(
            $koneksi,
            "UPDATE t_tahun_ajaran
             SET nama = ?, tanggal_mulai = ?,
                 tanggal_selesai = ?, status_aktif = ?
             WHERE id = ?"
        );

        $statusInt = (int) $status_aktif;

        mysqli_stmt_bind_param(
            $update,
            "sssii",
            $nama,
            $tanggal_mulai,
            $tanggal_selesai,
            $statusInt,
            $id
        );

        if (mysqli_stmt_execute($update)) {
            header("Location: tahun_ajaran.php?pesan=edit_berhasil");
            exit;
        } else {
            $pesan = "Gagal mengubah data tahun ajaran.";
        }
    }

    // Pertahankan isian jika terjadi kesalahan
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

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
          rel="stylesheet">
</head>

<body class="bg-white">

<div class="container-fluid">
    <div class="row">

        <!-- SIDEBAR SAMA SEPERTI EDIT DATA KELAS -->
        <aside class="col-12 col-md-3 col-lg-2 bg-dark min-vh-100 p-3">

            <div class="text-white mb-4">
                <h4 class="fw-bold mb-0">
                    <i class="bi bi-shield-check me-2"></i>
                    Sistem Pelanggaran
                </h4>
                <small class="text-secondary">Panel Admin</small>
            </div>

            <div class="bg-secondary bg-opacity-25 rounded-3 p-3 mb-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-white text-dark rounded-circle p-2">
                        <i class="bi bi-person-fill fs-4"></i>
                    </div>
                    <div class="text-white">
                        <div class="fw-semibold">
                            <?= htmlspecialchars($_SESSION['name'] ?? 'Pengguna'); ?>
                        </div>
                        <small class="text-secondary">ADMIN</small>
                    </div>
                </div>
            </div>

            <div class="text-secondary small fw-semibold mb-2">
                MENU UTAMA
            </div>

            <ul class="nav nav-pills flex-column gap-1">
                <li class="nav-item">
                    <a href="dashboard.php" class="nav-link text-white">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a href="siswa.php" class="nav-link text-white">Data Siswa</a>
                </li>
                <li class="nav-item">
                    <a href="guru.php" class="nav-link text-white">Data Guru</a>
                </li>
                <li class="nav-item">
                    <a href="kelas.php" class="nav-link text-white">Kelas</a>
                </li>
                <li class="nav-item">
                    <a href="tahun_ajaran.php" class="nav-link active">Tahun Ajaran</a>
                </li>
                <li class="nav-item">
                    <a href="penempatan_siswa.php" class="nav-link text-white">Penempatan Siswa</a>
                </li>
                <li class="nav-item">
                    <a href="wali_kelas.php" class="nav-link text-white">Wali Kelas</a>
                </li>
                <li class="nav-item">
                    <a href="kategori_pelanggaran.php" class="nav-link text-white">Kategori Pelanggaran</a>
                </li>
                <li class="nav-item">
                    <a href="jenis_pelanggaran.php" class="nav-link text-white">Jenis Pelanggaran</a>
                </li>
                <li class="nav-item">
                    <a href="laporan.php" class="nav-link text-white">Laporan</a>
                </li>
                <li class="nav-item">
                    <a href="abautme.php" class="nav-link text-white">About Me</a>
                </li>
            </ul>

            <hr class="border-secondary">

            <a href="logout.php" class="nav-link text-danger">
                <i class="bi bi-box-arrow-left me-2"></i>Logout
            </a>
        </aside>

        <!-- KONTEN UTAMA -->
        <main class="col-12 col-md-9 col-lg-10 p-3 p-lg-4">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold mb-1">Edit Tahun Ajaran</h2>
                    <p class="text-secondary mb-2">
                        Perbarui data tahun ajaran.
                    </p>
                    <a href="tahun_ajaran.php" class="btn btn-secondary btn-sm">
                        Kembali ke Data Tahun Ajaran
                    </a>
                </div>
            </div>

            <div class="row justify-content-center">
                <div class="col-12 col-xl-8">

                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-dark text-white p-3">
                            <h4 class="mb-0">Edit Tahun Ajaran</h4>
                        </div>

                        <div class="card-body p-3 p-md-4">

                            <?php if ($pesan !== '') { ?>
                                <div class="alert alert-danger alert-dismissible fade show"
                                     role="alert">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                    <?= htmlspecialchars($pesan); ?>
                                    <button type="button" class="btn-close"
                                            data-bs-dismiss="alert"></button>
                                </div>
                            <?php } ?>

                            <form method="POST"
                                  action="edit_tahun_ajaran.php?id=<?= $id; ?>">

                                <input type="hidden" name="id" value="<?= $id; ?>">

                                <div class="mb-3">
                                    <label class="form-label">Nama Tahun Ajaran</label>
                                    <input type="text" name="nama"
                                           class="form-control"
                                           value="<?= htmlspecialchars($tahun['nama'] ?? ''); ?>"
                                           required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Tanggal Mulai</label>
                                    <input type="date" name="tanggal_mulai"
                                           class="form-control"
                                           value="<?= htmlspecialchars($tahun['tanggal_mulai'] ?? ''); ?>"
                                           required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Tanggal Selesai</label>
                                    <input type="date" name="tanggal_selesai"
                                           class="form-control"
                                           value="<?= htmlspecialchars($tahun['tanggal_selesai'] ?? ''); ?>"
                                           required>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label">Status</label>
                                    <select name="status_aktif" class="form-select" required>
                                        <option value="1"
                                            <?= (string)$tahun['status_aktif'] === '1' ? 'selected' : ''; ?>>
                                            Aktif
                                        </option>
                                        <option value="0"
                                            <?= (string)$tahun['status_aktif'] === '0' ? 'selected' : ''; ?>>
                                            Tidak Aktif
                                        </option>
                                    </select>
                                </div>

                                <button type="submit" name="simpan"
                                        class="btn btn-primary">
                                    <i class="bi bi-check-circle me-1"></i>
                                    Simpan Perubahan
                                </button>

                                <a href="tahun_ajaran.php" class="btn btn-secondary">
                                    Kembali
                                </a>

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
