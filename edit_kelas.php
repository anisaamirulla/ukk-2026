
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

            if (mysqli_num_rows($hasilCek) > 0) {
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
                        header("Location: kelas.php?pesan=edit_berhasil");
                        exit;
                    } else {
                        $pesan = "Gagal mengubah data kelas.";
                    }
                }
            }
        }
    }
}

// AMBIL DATA KELAS
$stmt = mysqli_prepare($koneksi, "SELECT * FROM t_kelas WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$query = mysqli_stmt_get_result($stmt);

if (!$query || mysqli_num_rows($query) == 0) {
    exit("Data kelas tidak ditemukan.");
}

$kelas = mysqli_fetch_assoc($query);

// Pertahankan isian jika validasi gagal
if (isset($_POST['update'])) {
    $kelas['nama'] = $_POST['nama'] ?? '';
    $kelas['tingkat'] = $_POST['tingkat'] ?? '';
    $kelas['jurusan'] = $_POST['jurusan'] ?? '';
    $kelas['status_aktif'] = $_POST['status_aktif'] ?? '1';
}

function amanKelas($nilai) {
    return htmlspecialchars((string)($nilai ?? ''), ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Data Kelas</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>

<body class="bg-white">
<div class="container-fluid">
    <div class="row">

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
                            <?= amanKelas($_SESSION['name'] ?? 'Pengguna') ?>
                        </div>
                        <small class="text-secondary">ADMIN</small>
                    </div>
                </div>
            </div>

            <div class="text-secondary small fw-semibold mb-2">MENU UTAMA</div>

            <ul class="nav nav-pills flex-column gap-1">
                <li class="nav-item"><a href="dashboard.php" class="nav-link text-white">Dashboard</a></li>
                <li class="nav-item"><a href="siswa.php" class="nav-link text-white">Data Siswa</a></li>
                <li class="nav-item"><a href="guru.php" class="nav-link text-white">Data Guru</a></li>
                <li class="nav-item"><a href="kelas.php" class="nav-link active">Kelas</a></li>
                <li class="nav-item"><a href="tahun_ajaran.php" class="nav-link text-white">Tahun Ajaran</a></li>
                <li class="nav-item"><a href="penempatan_siswa.php" class="nav-link text-white">Penempatan Siswa</a></li>
                <li class="nav-item"><a href="wali_kelas.php" class="nav-link text-white">Wali Kelas</a></li>
                <li class="nav-item"><a href="kategori_pelanggaran.php" class="nav-link text-white">Kategori Pelanggaran</a></li>
                <li class="nav-item"><a href="jenis_pelanggaran.php" class="nav-link text-white">Jenis Pelanggaran</a></li>
                <li class="nav-item"><a href="laporan.php" class="nav-link text-white">Laporan</a></li>
                <li class="nav-item"><a href="abautme.php" class="nav-link text-white">About Me</a></li>
            </ul>

            <hr class="border-secondary">

            <a href="logout.php" class="nav-link text-danger">
                <i class="bi bi-box-arrow-left me-2"></i>Logout
            </a>
        </aside>

        <main class="col-12 col-md-9 col-lg-10 p-3 p-lg-4">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold mb-1">Edit Data Kelas</h2>
                    <p class="text-secondary mb-2">Perbarui data kelas.</p>
                    <a href="kelas.php" class="btn btn-secondary btn-sm">Kembali ke Data Kelas</a>
                </div>
            </div>

            <div class="row justify-content-center">
                <div class="col-12 col-xl-8">
                    <div class="card border-0 shadow-sm">

                        <div class="card-header bg-primary text-white">
                            <h4 class="mb-0">Edit Data Kelas</h4>
                        </div>

                        <div class="card-body">

                            <?php if ($pesan !== ''): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                    <strong>Gagal!</strong> <?= amanKelas($pesan) ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
                                </div>
                            <?php endif; ?>

                            <form method="POST" action="edit_kelas.php?id=<?= $id ?>">
                                <input type="hidden" name="id" value="<?= $id ?>">

                                <div class="mb-3">
                                    <label for="nama" class="form-label">Nama Kelas</label>
                                    <input type="text" name="nama" id="nama" class="form-control"
                                           value="<?= amanKelas($kelas['nama']) ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label for="tingkat" class="form-label">Tingkat</label>
                                    <select name="tingkat" id="tingkat" class="form-select" required>
                                        <?php foreach (['X', 'XI', 'XII'] as $t): ?>
                                            <option value="<?= $t ?>"
                                                <?= $kelas['tingkat'] === $t ? 'selected' : '' ?>>
                                                <?= $t ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="jurusan" class="form-label">Jurusan</label>
                                    <input type="text" name="jurusan" id="jurusan" class="form-control"
                                           value="<?= amanKelas($kelas['jurusan']) ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label for="status_aktif" class="form-label">Status Kelas</label>
                                    <select name="status_aktif" id="status_aktif" class="form-select" required>
                                        <option value="1" <?= (string)$kelas['status_aktif'] === '1' ? 'selected' : '' ?>>Aktif</option>
                                        <option value="0" <?= (string)$kelas['status_aktif'] === '0' ? 'selected' : '' ?>>Tidak Aktif</option>
                                    </select>
                                </div>

                                <button type="submit" name="update" class="btn btn-primary">
                                    Simpan Perubahan
                                </button>

                                <a href="kelas.php" class="btn btn-secondary">Kembali</a>
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
