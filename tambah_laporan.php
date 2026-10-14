```php
<?php
session_start();
require_once __DIR__ . "/config/koneksi.php";

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

if (($_SESSION['role'] ?? '') != 'admin') {
    exit("Anda tidak memiliki akses.");
}

$pesan = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tahun = (int)($_POST['tahun_ajaran_id'] ?? 0);
    $siswa = (int)($_POST['siswa_id'] ?? 0);
    $nama_siswa = trim($_POST['nama_siswa'] ?? '');
    $kelas = (int)($_POST['kelas_id'] ?? 0);
    $nama_kelas = trim($_POST['nama_kelas'] ?? '');
    $pelanggaran = (int)($_POST['pelanggaran_id'] ?? 0);
    $nama_pelanggaran = trim($_POST['nama_pelanggaran'] ?? '');
    $kategori = (int)($_POST['kategori_id'] ?? 0);
    $guru = (int)($_POST['guru_id'] ?? 0);
    $nama_guru = trim($_POST['nama_guru'] ?? '');
    $tanggal = $_POST['tanggal'] ?? '';
    $keterangan = trim($_POST['keterangan'] ?? '');
    $poin = (int)($_POST['poin'] ?? 0);
    $tindakan = trim($_POST['tindakan'] ?? '');
    $status = $_POST['status'] ?? 'Belum Diproses';

    $sql = "INSERT INTO t_pelanggaran_siswa
    (tahun_ajaran_id, siswa_id, nama_siswa, kelas_id, nama_kelas,
     pelanggaran_id, nama_pelanggaran, pelanggaran_kategori_id,
     guru_id, nama_guru, tanggal, keterangan, poin, tindakan, status)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($koneksi, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param(
            $stmt, "iisisiisiississ",
            $tahun, $siswa, $nama_siswa, $kelas, $nama_kelas,
            $pelanggaran, $nama_pelanggaran, $kategori,
            $guru, $nama_guru, $tanggal, $keterangan,
            $poin, $tindakan, $status
        );

        if (mysqli_stmt_execute($stmt)) {
            header("Location: laporan.php");
            exit;
        } else {
            $pesan = "Gagal menyimpan laporan: " . mysqli_stmt_error($stmt);
        }
    } else {
        $pesan = "Gagal menyiapkan query: " . mysqli_error($koneksi);
    }
}

function h($v) {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Tambah Laporan</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container-fluid">
<div class="row">
<aside class="col-md-3 col-lg-2 bg-dark text-white min-vh-100 p-3">
    <h5 class="mb-4">Admin Sekolah</h5>
    <a href="dashboard.php" class="btn btn-outline-light w-100 mb-2">Dashboard</a>
    <a href="siswa.php" class="btn btn-outline-light w-100 mb-2">Data Siswa</a>
    <a href="guru.php" class="btn btn-outline-light w-100 mb-2">Data Guru</a>
    <a href="kelas.php" class="btn btn-outline-light w-100 mb-2">Data Kelas</a>
    <a href="kategori_pelanggaran.php" class="btn btn-outline-light w-100 mb-2">Kategori Pelanggaran</a>
    <a href="jenis_pelanggaran.php" class="btn btn-outline-light w-100 mb-2">Jenis Pelanggaran</a>
    <a href="laporan.php" class="btn btn-outline-light w-100 mb-2">Laporan</a>
    <a href="logout.php" class="btn btn-danger w-100 mt-3">Logout</a>
</aside>

<main class="col-md-9 col-lg-10 p-4">
    <h3 class="mb-4">Tambah Laporan Pelanggaran</h3>

    <?php if ($pesan !== ''): ?>
        <div class="alert alert-danger"><?= h($pesan) ?></div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
    <div class="card-body">
    <form method="post" class="row g-3">

        <div class="col-md-6">
            <label class="form-label">ID Tahun Ajaran</label>
            <input type="number" name="tahun_ajaran_id" class="form-control" min="1" required>
        </div>

        <div class="col-md-6">
            <label class="form-label">ID Siswa</label>
            <input type="number" name="siswa_id" class="form-control" min="1" required>
        </div>

        <div class="col-md-6">
            <label class="form-label">Nama Siswa</label>
            <input type="text" name="nama_siswa" class="form-control" required>
        </div>

        <div class="col-md-6">
            <label class="form-label">ID Kelas</label>
            <input type="number" name="kelas_id" class="form-control" min="1" required>
        </div>

        <div class="col-md-6">
            <label class="form-label">Nama Kelas</label>
            <input type="text" name="nama_kelas" class="form-control" required>
        </div>

        <div class="col-md-6">
            <label class="form-label">ID Jenis Pelanggaran</label>
            <input type="number" name="pelanggaran_id" class="form-control" min="1" required>
        </div>

        <div class="col-md-6">
            <label class="form-label">Nama Pelanggaran</label>
            <input type="text" name="nama_pelanggaran" class="form-control" required>
        </div>

        <div class="col-md-6">
            <label class="form-label">ID Kategori Pelanggaran</label>
            <input type="number" name="kategori_id" class="form-control" min="1" required>
        </div>

        <div class="col-md-6">
            <label class="form-label">ID Guru</label>
            <input type="number" name="guru_id" class="form-control" min="1" required>
        </div>

        <div class="col-md-6">
            <label class="form-label">Nama Guru</label>
            <input type="text" name="nama_guru" class="form-control" required>
        </div>

        <div class="col-md-6">
            <label class="form-label">Tanggal Pelanggaran</label>
            <input type="date" name="tanggal" class="form-control" required>
        </div>

        <div class="col-md-6">
            <label class="form-label">Poin</label>
            <input type="number" name="poin" class="form-control" min="0" required>
        </div>

        <div class="col-12">
            <label class="form-label">Keterangan</label>
            <textarea name="keterangan" class="form-control" rows="3" required></textarea>
        </div>

        <div class="col-md-6">
            <label class="form-label">Tindakan</label>
            <input type="text" name="tindakan" class="form-control" required>
        </div>

        <div class="col-md-6">
            <label class="form-label">Status</label>
            <select name="status" class="form-select" required>
                <option value="Belum Diproses">Belum Diproses</option>
                <option value="Diproses">Diproses</option>
                <option value="Selesai">Selesai</option>
            </select>
        </div>

        <div class="col-12">
            <button type="submit" class="btn btn-primary">Simpan Laporan</button>
            <a href="laporan.php" class="btn btn-secondary">Kembali</a>
        </div>
    </form>
    </div>
    </div>

    <a href="dashboard.php" class="btn btn-outline-secondary mt-3">Kembali ke Dashboard</a>
</main>
</div>
</div>
</body>
</html>
```