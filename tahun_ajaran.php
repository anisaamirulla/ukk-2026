<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit;
}

require_once 'config/koneksi.php';

if (($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

$query = mysqli_query(
    $koneksi,
    "SELECT * FROM t_tahun_ajaran ORDER BY tanggal_mulai DESC"
);

if (!$query) {
    die("Gagal mengambil data tahun ajaran: " . mysqli_error($koneksi));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Tahun Ajaran</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f4f7fb; }
        .container { margin-top: 35px; }
        thead th { background: #0d6efd !important; color: white !important; }
    </style>
</head>
<body>
<div class="container">
    <div class="d-flex justify-content-between mb-4">
        <h2>Data Tahun Ajaran</h2>
        <a href="dashboard.php" class="btn btn-secondary">Kembali</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tahun Ajaran</th>
                        <th>Tanggal Mulai</th>
                        <th>Tanggal Selesai</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php $no = 1; ?>
                <?php while ($data = mysqli_fetch_assoc($query)) { ?>
                    <tr>
                        <td><?= $no++; ?></td>
                        <td><?= htmlspecialchars($data['nama'] ?? '') ?></td>
                        <td><?= htmlspecialchars($data['tanggal_mulai'] ?? '') ?></td>
                        <td><?= htmlspecialchars($data['tanggal_selesai'] ?? '') ?></td>
                        <td><?= htmlspecialchars($data['status_aktif'] ?? '') ?></td>
                    </tr>
                <?php } ?>

                <?php if (mysqli_num_rows($query) == 0) { ?>
                    <tr><td colspan="5" class="text-center">Belum ada data tahun ajaran.</td></tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>