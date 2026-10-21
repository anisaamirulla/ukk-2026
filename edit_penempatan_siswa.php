
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

if (!isset($_SESSION['data_penempatan'])) {
    header("Location: penempatan_siswa.php?pesan=gagal_hapus");
    exit;
}

$id = (int)($_GET['id'] ?? 0);
$edit_data = null;

foreach ($_SESSION['data_penempatan'] as $row) {
    if ((int)$row['id'] === $id) {
        $edit_data = $row;
        break;
    }
}

if (!$edit_data) {
    header("Location: penempatan_siswa.php");
    exit;
}

$query_siswa = mysqli_query($koneksi, "SELECT id, nisn, nama FROM t_siswa ORDER BY nama ASC");
$query_kelas = mysqli_query($koneksi, "SELECT id, nama FROM t_kelas ORDER BY nama ASC");
$query_tahun = mysqli_query($koneksi, "SELECT id, nama FROM t_tahun_ajaran ORDER BY id DESC");

if (!$query_siswa || !$query_kelas || !$query_tahun) {
    die("Gagal mengambil data referensi: " . mysqli_error($koneksi));
}

$siswa_list = mysqli_fetch_all($query_siswa, MYSQLI_ASSOC);
$kelas_list = mysqli_fetch_all($query_kelas, MYSQLI_ASSOC);
$tahun_list = mysqli_fetch_all($query_tahun, MYSQLI_ASSOC);

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $siswa_id = (int)($_POST['siswa_id'] ?? 0);
    $kelas_id = (int)($_POST['kelas_id'] ?? 0);
    $tahun_id = (int)($_POST['tahun_ajaran_id'] ?? 0);
    $tanggal_mulai = $_POST['tanggal_mulai'] ?? '';
    $tanggal_selesai = $_POST['tanggal_selesai'] ?? '';
    $status = (int)($_POST['status_aktif'] ?? 1);

    $siswa_map = [];
    foreach ($siswa_list as $s) {
        $siswa_map[(int)$s['id']] = $s;
    }

    $kelas_map = [];
    foreach ($kelas_list as $k) {
        $kelas_map[(int)$k['id']] = $k['nama'];
    }

    $tahun_map = [];
    foreach ($tahun_list as $t) {
        $tahun_map[(int)$t['id']] = $t['nama'];
    }

    $mulai = DateTime::createFromFormat('!Y-m-d', $tanggal_mulai);
    $selesai = DateTime::createFromFormat('!Y-m-d', $tanggal_selesai);

    if (
        !isset($siswa_map[$siswa_id]) ||
        !isset($kelas_map[$kelas_id]) ||
        !isset($tahun_map[$tahun_id]) ||
        !$mulai || $mulai->format('Y-m-d') !== $tanggal_mulai ||
        !$selesai || $selesai->format('Y-m-d') !== $tanggal_selesai ||
        $tanggal_selesai < $tanggal_mulai ||
        !in_array($status, [0, 1], true)
    ) {
        $error = "Periksa kembali data dan tanggal yang kamu masukkan.";
    } else {
        foreach ($_SESSION['data_penempatan'] as &$row) {
            if ((int)$row['id'] === $id) {
                $row['siswa_id'] = $siswa_id;
                $row['nisn'] = $siswa_map[$siswa_id]['nisn'];
                $row['nama_siswa'] = $siswa_map[$siswa_id]['nama'];
                $row['kelas_id'] = $kelas_id;
                $row['nama_kelas'] = $kelas_map[$kelas_id];
                $row['tahun_ajaran_id'] = $tahun_id;
                $row['nama_tahun_ajaran'] = $tahun_map[$tahun_id];
                $row['tanggal_mulai'] = $tanggal_mulai;
                $row['tanggal_selesai'] = $tanggal_selesai;
                $row['status_aktif'] = $status;
                break;
            }
        }
        unset($row);

        header("Location: penempatan_siswa.php?pesan=edit");
        exit;
    }

    $edit_data['siswa_id'] = $siswa_id;
    $edit_data['kelas_id'] = $kelas_id;
    $edit_data['tahun_ajaran_id'] = $tahun_id;
    $edit_data['tanggal_mulai'] = $tanggal_mulai;
    $edit_data['tanggal_selesai'] = $tanggal_selesai;
    $edit_data['status_aktif'] = $status;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Penempatan Siswa</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        body { background: #f5f7fa; }
        .sidebar { background: #212529; min-height: 100vh; }
        .sidebar .nav-link {
            color: white;
            padding: 10px 14px;
            border-radius: 6px;
        }
        .sidebar .nav-link:hover { background: #343a40; }
        .sidebar .nav-link.active { background: #0d6efd; color: white; }
        .card { border-radius: 8px; }
    </style>
</head>

<body>
<div class="container-fluid">
    <div class="row">

        <aside class="col-12 col-md-3 col-lg-2 sidebar p-3">
            <div class="text-white mb-4">
                <h5 class="fw-bold mb-1">
                    <i class="bi bi-shield-check me-2"></i>Sistem Pelanggaran
                </h5>
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
                <li><a href="dashboard.php" class="nav-link">Dashboard</a></li>
                <li><a href="siswa.php" class="nav-link">Data Siswa</a></li>
                <li><a href="guru.php" class="nav-link">Data Guru</a></li>
                <li><a href="kelas.php" class="nav-link">Kelas</a></li>
                <li><a href="tahun_ajaran.php" class="nav-link">Tahun Ajaran</a></li>
                <li><a href="penempatan_siswa.php" class="nav-link active">Penempatan Siswa</a></li>
                <li><a href="wali_kelas.php" class="nav-link">Wali Kelas</a></li>
                <li><a href="kategori_pelanggaran.php" class="nav-link">Kategori Pelanggaran</a></li>
                <li><a href="jenis_pelanggaran.php" class="nav-link">Jenis Pelanggaran</a></li>
                <li><a href="laporan.php" class="nav-link">Laporan</a></li>
                <li><a href="abautme.php" class="nav-link">About Me</a></li>
            </ul>

            <hr class="border-secondary">
            <a href="logout.php" class="nav-link text-danger">
                <i class="bi bi-box-arrow-left me-2"></i>Logout
            </a>
        </aside>

        <main class="col-12 col-md-9 col-lg-10 p-3 p-lg-4">
            <div class="mb-4">
                <h2 class="fw-bold mb-1">Edit Penempatan Siswa</h2>
                <p class="text-secondary mb-0">Ubah informasi penempatan siswa</p>
            </div>

            <?php if ($error !== ''): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <?= aman($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Form Edit Penempatan Siswa</h5>
                </div>

                <div class="card-body">
                    <form method="POST" action="edit_penempatan_siswa.php?id=<?= $id ?>">
                        <div class="row g-3">

                            <div class="col-md-6">
                                <label class="form-label">Siswa</label>
                                <select name="siswa_id" class="form-select" required>
                                    <option value="">-- Pilih Siswa --</option>
                                    <?php foreach ($siswa_list as $s): ?>
                                        <option value="<?= (int)$s['id'] ?>"
                                            <?= (int)$edit_data['siswa_id'] === (int)$s['id'] ? 'selected' : '' ?>>
                                            <?= aman($s['nisn']) ?> - <?= aman($s['nama']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Kelas</label>
                                <select name="kelas_id" class="form-select" required>
                                    <option value="">-- Pilih Kelas --</option>
                                    <?php foreach ($kelas_list as $k): ?>
                                        <option value="<?= (int)$k['id'] ?>"
                                            <?= (int)$edit_data['kelas_id'] === (int)$k['id'] ? 'selected' : '' ?>>
                                            <?= aman($k['nama']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Tahun Ajaran</label>
                                <select name="tahun_ajaran_id" class="form-select" required>
                                    <option value="">-- Pilih Tahun Ajaran --</option>
                                    <?php foreach ($tahun_list as $t): ?>
                                        <option value="<?= (int)$t['id'] ?>"
                                            <?= (int)$edit_data['tahun_ajaran_id'] === (int)$t['id'] ? 'selected' : '' ?>>
                                            <?= aman($t['nama']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Status</label>
                                <select name="status_aktif" class="form-select" required>
                                    <option value="1" <?= (int)$edit_data['status_aktif'] === 1 ? 'selected' : '' ?>>Aktif</option>
                                    <option value="0" <?= (int)$edit_data['status_aktif'] === 0 ? 'selected' : '' ?>>Tidak Aktif</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Tanggal Mulai</label>
                                <input type="date" name="tanggal_mulai" class="form-control"
                                    value="<?= aman($edit_data['tanggal_mulai']) ?>" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Tanggal Selesai</label>
                                <input type="date" name="tanggal_selesai" class="form-control"
                                    value="<?= aman($edit_data['tanggal_selesai']) ?>" required>
                            </div>

                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i>Simpan Perubahan
                            </button>
                            <a href="penempatan_siswa.php" class="btn btn-secondary">Batal</a>
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
