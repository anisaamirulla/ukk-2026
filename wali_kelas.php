
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

$sql = "SELECT
            wk.id,
            ta.nama AS tahun_ajaran,
            k.nama AS nama_kelas,
            g.nama AS nama_guru,
            wk.tanggal_mulai,
            wk.tanggal_selesai,
            wk.status_aktif
        FROM t_wali_kelas wk
        LEFT JOIN t_tahun_ajaran ta ON wk.tahun_ajaran_id = ta.id
        LEFT JOIN t_kelas k ON wk.kelas_id = k.id
        LEFT JOIN t_guru g ON wk.guru_id = g.id
        ORDER BY ta.nama DESC, k.nama";

$query = mysqli_query($koneksi, $sql);

if (!$query) {
    die("Gagal mengambil data: " . mysqli_error($koneksi));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Data Wali Kelas</title>
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
        <h2>Data Wali Kelas</h2>
        <a href="dashboard.php" class="btn btn-secondary">Kembali</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tahun Ajaran</th>
                        <th>Kelas</th>
                        <th>Nama Wali Kelas</th>
                        <th>Tanggal Mulai</th>
                        <th>Tanggal Selesai</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php $no = 1; ?>
                <?php while ($data = mysqli_fetch_assoc($query)) { ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= htmlspecialchars($data['tahun_ajaran'] ?? '') ?></td>
                        <td><?= htmlspecialchars($data['nama_kelas'] ?? '') ?></td>
                        <td><?= htmlspecialchars($data['nama_guru'] ?? '') ?></td>
                        <td><?= htmlspecialchars($data['tanggal_mulai'] ?? '') ?></td>
                        <td><?= htmlspecialchars($data['tanggal_selesai'] ?? '') ?></td>
                        <td><?= htmlspecialchars($data['status_aktif'] ?? '') ?></td>
                    </tr>
                <?php } ?>

                <?php if (mysqli_num_rows($query) == 0) { ?>
                    <tr>
                        <td colspan="7" class="text-center">Belum ada data wali kelas.</td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>