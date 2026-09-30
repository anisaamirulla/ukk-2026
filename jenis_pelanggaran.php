
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
            p.id,
            p.kode,
            p.nama,
            k.nama AS kategori,
            p.poin,
            p.deksripsi,
            p.status_aktif
        FROM t_pelanggaran p
        LEFT JOIN t_pelanggaran_kategori k
            ON p.pelanggaran_kategori_id = k.id
        ORDER BY p.nama ASC";

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
    <title>Jenis Pelanggaran</title>
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
        <h2>Jenis Pelanggaran</h2>
        <a href="dashboard.php" class="btn btn-secondary">Kembali</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode</th>
                        <th>Nama Pelanggaran</th>
                        <th>Kategori</th>
                        <th>Poin</th>
                        <th>Deskripsi</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php $no = 1; ?>
                <?php while ($data = mysqli_fetch_assoc($query)) { ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= htmlspecialchars($data['kode'] ?? '') ?></td>
                        <td><?= htmlspecialchars($data['nama'] ?? '') ?></td>
                        <td><?= htmlspecialchars($data['kategori'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($data['poin'] ?? '0') ?></td>
                        <td><?= htmlspecialchars($data['deksripsi'] ?? '') ?></td>
                        <td><?= htmlspecialchars($data['status_aktif'] ?? '') ?></td>
                    </tr>
                <?php } ?>

                <?php if (mysqli_num_rows($query) == 0) { ?>
                    <tr>
                        <td colspan="7" class="text-center">
                            Belum ada jenis pelanggaran.
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>