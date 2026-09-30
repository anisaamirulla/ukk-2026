
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

if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $sql = "SELECT tanggal, nama_siswa, nama_kelas,
                   nama_pelanggaran, nama_guru, poin,
                   tindakan, status, keterangan
            FROM t_pelanggaran_siswa
            ORDER BY tanggal DESC";

    $query = mysqli_query($koneksi, $sql);

    if (!$query) {
        die("Gagal mengambil data ekspor.");
    }

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="data_pelanggaran.csv"');

    $output = fopen('php://output', 'w');

    // BOM agar karakter UTF-8 terbaca baik di Excel
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

    fputcsv($output, [
        'Tanggal', 'Nama Siswa', 'Kelas',
        'Pelanggaran', 'Guru', 'Poin',
        'Tindakan', 'Status', 'Keterangan'
    ], ';');

    while ($data = mysqli_fetch_assoc($query)) {
        fputcsv($output, $data, ';');
    }

    fclose($output);
    exit;
}

$sql = "SELECT tanggal, nama_siswa, nama_kelas,
               nama_pelanggaran, nama_guru, poin,
               tindakan, status, keterangan
        FROM t_pelanggaran_siswa
        ORDER BY tanggal DESC";

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
    <title>Cetak dan Export Data</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f4f7fb; }
        .container { margin-top: 35px; }
        thead th { background: #0d6efd !important; color: white !important; }
        @media print {
            .no-print { display: none !important; }
            body { background: white; }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Cetak dan Export Data</h2>
        <a href="dashboard.php" class="btn btn-secondary no-print">Kembali</a>
    </div>

    <div class="mb-3 d-flex gap-2 no-print">
        <a href="cetak_export.php?export=csv" class="btn btn-success">
            Export CSV
        </a>
        <button onclick="window.print()" class="btn btn-primary">
            Cetak / Simpan PDF
        </button>
    </div>

    <div class="card shadow-sm">
        <div class="card-body table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Nama Siswa</th>
                        <th>Kelas</th>
                        <th>Pelanggaran</th>
                        <th>Guru</th>
                        <th>Poin</th>
                        <th>Tindakan</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php $no = 1; ?>
                <?php while ($data = mysqli_fetch_assoc($query)) { ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= htmlspecialchars($data['tanggal'] ?? '') ?></td>
                        <td><?= htmlspecialchars($data['nama_siswa'] ?? '') ?></td>
                        <td><?= htmlspecialchars($data['nama_kelas'] ?? '') ?></td>
                        <td><?= htmlspecialchars($data['nama_pelanggaran'] ?? '') ?></td>
                        <td><?= htmlspecialchars($data['nama_guru'] ?? '') ?></td>
                        <td><?= htmlspecialchars($data['poin'] ?? '0') ?></td>
                        <td><?= htmlspecialchars($data['tindakan'] ?? '') ?></td>
                        <td><?= htmlspecialchars($data['status'] ?? '') ?></td>
                    </tr>
                <?php } ?>

                <?php if (mysqli_num_rows($query) == 0) { ?>
                    <tr>
                        <td colspan="9" class="text-center">
                            Belum ada data pelanggaran.
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