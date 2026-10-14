
<?php
session_start();
include "config/koneksi.php";

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    echo "Anda tidak memiliki akses";
    exit;
}

$query = mysqli_query($koneksi, "SELECT * FROM t_guru ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Data Guru</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container-fluid">
    <div class="row min-vh-100">

        <!-- SIDEBAR -->
        <div class="col-md-2 bg-dark text-white p-3">
            <h4 class="mb-4">Admin UKK</h4>

            <div class="nav nav-pills flex-column gap-2">
                <a href="dashboard.php" class="nav-link text-white">Dashboard</a>
                <a href="siswa.php" class="nav-link text-white">Data Siswa</a>
                <a href="guru.php" class="nav-link active bg-primary text-white">Data Guru</a>
                <a href="kelas.php" class="nav-link text-white">Data Kelas</a>
                <a href="tahun_ajaran.php" class="nav-link text-white">Tahun Ajaran</a>
                <a href="penempatan_siswa.php" class="nav-link text-white">Penempatan Siswa</a>
                <a href="wali_kelas.php" class="nav-link text-white">Wali Kelas</a>
                <a href="kategori_pelanggaran.php" class="nav-link text-white">Kategori Pelanggaran</a>
                <a href="jenis_pelanggaran.php" class="nav-link text-white">Jenis Pelanggaran</a>
                <a href="cetak_export.php" class="nav-link text-white">Cetak / Export</a>
                <a href="logout.php" class="nav-link text-danger">Logout</a>
            </div>
        </div>

        <!-- KONTEN UTAMA -->
        <div class="col-md-10 p-4 bg-light">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold mb-1">Data Guru</h2>
                    <p class="text-secondary mb-2">Daftar data guru</p>
                    <a href="dashboard.php" class="btn btn-secondary btn-sm">
                        Kembali ke Dashboard
                    </a>
                </div>

                <a href="tambah_guru.php" class="btn btn-primary">
                    + Tambah Guru
                </a>
            </div>

            <!-- TABEL GURU -->
            <div class="bg-white p-3 rounded">
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead class="table-primary">
                            <tr>
                                <th>No</th>
                                <th>NIP</th>
                                <th>Nama Guru</th>
                                <th>Jenis Kelamin</th>
                                <th>No. Telepon</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php
                            $no = 1;

                            if ($query && mysqli_num_rows($query) > 0) {
                                while ($data = mysqli_fetch_assoc($query)) {
                            ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><?= htmlspecialchars($data['nip'] ?? ''); ?></td>
                                    <td class="fw-bold">
                                        <?= htmlspecialchars($data['nama'] ?? ''); ?>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($data['jenis_kelamin'] ?? ''); ?>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($data['no_telepon'] ?? ''); ?>
                                    </td>
                                    <td>
                                        <?php
                                        $status = $data['status_aktif'] ?? '';

                                        if ($status == 'Aktif' || $status == '1') {
                                        ?>
                                            <span class="badge bg-success">Aktif</span>
                                        <?php } else { ?>
                                            <span class="badge bg-danger">
                                                <?= htmlspecialchars($status == '0' ? 'Tidak Aktif' : ($status ?: 'Tidak Aktif')); ?>
                                            </span>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <a href="hapus_guru.php?id=<?= urlencode($data['id']); ?>"
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('Yakin ingin menghapus data guru ini?')">
                                            Hapus
                                        </a>
                                    </td>
                                </tr>
                            <?php
                                }
                            } else {
                            ?>
                                <tr>
                                    <td colspan="7" class="text-center text-secondary py-4">
                                        Belum ada data guru.
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
```