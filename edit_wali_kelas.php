
<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

if (strtolower(trim($_SESSION['role'] ?? '')) !== 'admin') {
    exit("Anda tidak memiliki akses");
}

require_once __DIR__ . "/config/koneksi.php";

function aman($nilai) {
    return htmlspecialchars((string)($nilai ?? ''), ENT_QUOTES, 'UTF-8');
}

$id = (int)($_GET['id'] ?? 0);

$stmt = mysqli_prepare($koneksi, "
    SELECT id, tahun_ajaran_id, kelas_id, guru_id,
           tanggal_mulai, tanggal_selesai, status_aktif
    FROM t_wali_kelas
    WHERE id = ?
");

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$wali = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$wali) {
    header("Location: wali_kelas.php?pesan=data_tidak_ditemukan");
    exit;
}

$pesan_error = '';

$tahun_list = mysqli_query($koneksi, "SELECT id, nama FROM t_tahun_ajaran ORDER BY id DESC");
$kelas_list = mysqli_query($koneksi, "SELECT id, nama FROM t_kelas ORDER BY nama ASC");
$guru_list = mysqli_query($koneksi, "SELECT id, nip, nama FROM t_guru ORDER BY nama ASC");

if (!$tahun_list || !$kelas_list || !$guru_list) {
    die("Gagal mengambil data referensi: " . mysqli_error($koneksi));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tahun_id = (int)($_POST['tahun_ajaran_id'] ?? 0);
    $kelas_id = (int)($_POST['kelas_id'] ?? 0);
    $guru_id = (int)($_POST['guru_id'] ?? 0);
    $mulai = $_POST['tanggal_mulai'] ?? '';
    $selesai = $_POST['tanggal_selesai'] ?? '';
    $status = (int)($_POST['status_aktif'] ?? -1);

    $valid_mulai = DateTime::createFromFormat('!Y-m-d', $mulai);
    $valid_selesai = DateTime::createFromFormat('!Y-m-d', $selesai);

    $valid = $valid_mulai && $valid_mulai->format('Y-m-d') === $mulai
        && $valid_selesai && $valid_selesai->format('Y-m-d') === $selesai
        && $selesai >= $mulai
        && in_array($status, [0, 1], true);

    $cek_tahun = mysqli_prepare($koneksi, "SELECT id FROM t_tahun_ajaran WHERE id = ?");
    mysqli_stmt_bind_param($cek_tahun, "i", $tahun_id);
    mysqli_stmt_execute($cek_tahun);
    $tahun_ada = mysqli_stmt_get_result($cek_tahun)->num_rows > 0;
    mysqli_stmt_close($cek_tahun);

    $cek_kelas = mysqli_prepare($koneksi, "SELECT id FROM t_kelas WHERE id = ?");
    mysqli_stmt_bind_param($cek_kelas, "i", $kelas_id);
    mysqli_stmt_execute($cek_kelas);
    $kelas_ada = mysqli_stmt_get_result($cek_kelas)->num_rows > 0;
    mysqli_stmt_close($cek_kelas);

    $cek_guru = mysqli_prepare($koneksi, "SELECT id FROM t_guru WHERE id = ?");
    mysqli_stmt_bind_param($cek_guru, "i", $guru_id);
    mysqli_stmt_execute($cek_guru);
    $guru_ada = mysqli_stmt_get_result($cek_guru)->num_rows > 0;
    mysqli_stmt_close($cek_guru);

    if (!$valid || !$tahun_ada || !$kelas_ada || !$guru_ada) {
        $pesan_error = "Periksa kembali data dan tanggal yang kamu masukkan.";
    } else {
        $update = mysqli_prepare($koneksi, "
            UPDATE t_wali_kelas
            SET tahun_ajaran_id = ?, kelas_id = ?, guru_id = ?,
                tanggal_mulai = ?, tanggal_selesai = ?, status_aktif = ?
            WHERE id = ?
        ");

        mysqli_stmt_bind_param(
            $update, "iiissii",
            $tahun_id, $kelas_id, $guru_id,
            $mulai, $selesai, $status, $id
        );

        if (mysqli_stmt_execute($update)) {
            mysqli_stmt_close($update);
            header("Location: wali_kelas.php?pesan=edit");
            exit;
        } else {
            $pesan_error = "Data wali kelas gagal diperbarui.";
            mysqli_stmt_close($update);
        }
    }

    $wali['tahun_ajaran_id'] = $tahun_id;
    $wali['kelas_id'] = $kelas_id;
    $wali['guru_id'] = $guru_id;
    $wali['tanggal_mulai'] = $mulai;
    $wali['tanggal_selesai'] = $selesai;
    $wali['status_aktif'] = $status;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Wali Kelas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>

<body class="bg-light">
<div class="container-fluid">
    <div class="row">

        <aside class="col-12 col-md-3 col-lg-2 bg-dark min-vh-100 p-3">
            <div class="text-white mb-4">
                <h5><i class="bi bi-shield-check me-2"></i>Sistem Pelanggaran</h5>
                <small class="text-secondary">Panel Admin</small>
            </div>

            <div class="d-flex align-items-center bg-secondary bg-opacity-25 rounded p-3 mb-4">
                <i class="bi bi-person-circle fs-3 text-white me-2"></i>
                <div class="text-white">
                    <small class="text-secondary d-block">Login sebagai</small>
                    <?= aman($_SESSION['name'] ?? 'Admin') ?>
                </div>
            </div>

            <p class="text-secondary small fw-bold">MENU UTAMA</p>
            <ul class="nav nav-pills flex-column gap-1">
                <li><a href="dashboard.php" class="nav-link text-white">Dashboard</a></li>
                <li><a href="siswa.php" class="nav-link text-white">Data Siswa</a></li>
                <li><a href="guru.php" class="nav-link text-white">Data Guru</a></li>
                <li><a href="kelas.php" class="nav-link text-white">Kelas</a></li>
                <li><a href="tahun_ajaran.php" class="nav-link text-white">Tahun Ajaran</a></li>
                <li><a href="penempatan_siswa.php" class="nav-link text-white">Penempatan Siswa</a></li>
                <li><a href="wali_kelas.php" class="nav-link active">Wali Kelas</a></li>
                <li><a href="kategori_pelanggaran.php" class="nav-link text-white">Kategori Pelanggaran</a></li>
                <li><a href="jenis_pelanggaran.php" class="nav-link text-white">Jenis Pelanggaran</a></li>
                <li><a href="laporan.php" class="nav-link text-white">Laporan</a></li>
                <li><a href="abautme.php" class="nav-link text-white">About Me</a></li>
            </ul>

            <hr class="border-secondary">
            <a href="logout.php" class="nav-link text-danger">Logout</a>
        </aside>

        <main class="col-12 col-md-9 col-lg-10 p-3 p-lg-4">
            <div class="mb-4">
                <h2 class="fw-bold mb-1">Edit Wali Kelas</h2>
                <p class="text-secondary">Ubah data penugasan wali kelas</p>
            </div>

            <?php if ($pesan_error !== '') { ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?= aman($pesan_error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php } ?>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Form Edit Wali Kelas</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="row g-3">

                            <div class="col-md-6">
                                <label class="form-label">Tahun Ajaran</label>
                                <select name="tahun_ajaran_id" class="form-select" required>
                                    <option value="">-- Pilih Tahun Ajaran --</option>
                                    <?php while ($t = mysqli_fetch_assoc($tahun_list)) { ?>
                                        <option value="<?= (int)$t['id'] ?>"
                                            <?= (int)$wali['tahun_ajaran_id'] === (int)$t['id'] ? 'selected' : '' ?>>
                                            <?= aman($t['nama']) ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Kelas</label>
                                <select name="kelas_id" class="form-select" required>
                                    <option value="">-- Pilih Kelas --</option>
                                    <?php while ($k = mysqli_fetch_assoc($kelas_list)) { ?>
                                        <option value="<?= (int)$k['id'] ?>"
                                            <?= (int)$wali['kelas_id'] === (int)$k['id'] ? 'selected' : '' ?>>
                                            <?= aman($k['nama']) ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Guru</label>
                                <select name="guru_id" class="form-select" required>
                                    <option value="">-- Pilih Guru --</option>
                                    <?php while ($g = mysqli_fetch_assoc($guru_list)) { ?>
                                        <option value="<?= (int)$g['id'] ?>"
                                            <?= (int)$wali['guru_id'] === (int)$g['id'] ? 'selected' : '' ?>>
                                            <?= aman($g['nip']) ?> - <?= aman($g['nama']) ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Status</label>
                                <select name="status_aktif" class="form-select" required>
                                    <option value="1" <?= (int)$wali['status_aktif'] === 1 ? 'selected' : '' ?>>Aktif</option>
                                    <option value="0" <?= (int)$wali['status_aktif'] === 0 ? 'selected' : '' ?>>Tidak Aktif</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Tanggal Mulai</label>
                                <input type="date" name="tanggal_mulai" class="form-control"
                                    value="<?= aman($wali['tanggal_mulai']) ?>" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Tanggal Selesai</label>
                                <input type="date" name="tanggal_selesai" class="form-control"
                                    value="<?= aman($wali['tanggal_selesai']) ?>" required>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i>Simpan Perubahan
                            </button>
                            <a href="wali_kelas.php" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
