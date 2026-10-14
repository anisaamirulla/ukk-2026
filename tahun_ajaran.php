
<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    echo "Anda tidak memiliki akses";
    exit;
}

require_once __DIR__ . "/config/koneksi.php";

$query = mysqli_query($koneksi, "SELECT * FROM t_tahun_ajaran ORDER BY id ASC");

if (!$query) {
    die("Gagal mengambil data tahun ajaran: " . mysqli_error($koneksi));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Data Tahun Ajaran</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container-fluid">
    <div class="row">

        <!-- SIDEBAR -->
        <div class="col-md-3 col-lg-2 bg-dark min-vh-100 p-3">
            <h4 class="text-white mb-4">Admin UKK</h4>

            <ul class="nav flex-column gap-1">
                <li class="nav-item">
                    <a href="dashboard.php" class="nav-link text-white rounded">
                        Dashboard
                    </a>
                </li>

                <li class="nav-item">
                    <a href="siswa.php" class="nav-link text-white rounded">
                        Data Siswa
                    </a>
                </li>

                <li class="nav-item">
                    <a href="guru.php" class="nav-link text-white rounded">
                        Data Guru
                    </a>
                </li>

                <li class="nav-item">
                    <a href="kelas.php" class="nav-link text-white rounded">
                        Data Kelas
                    </a>
                </li>

                <li class="nav-item">
                    <a href="tahun_ajaran.php" class="nav-link active bg-primary text-white rounded">
                        Tahun Ajaran
                    </a>
                </li>

                <li class="nav-item">
                    <a href="penempatan_siswa.php" class="nav-link text-white rounded">
                        Penempatan Siswa
                    </a>
                </li>

                <li class="nav-item">
                    <a href="wali_kelas.php" class="nav-link text-white rounded">
                        Wali Kelas
                    </a>
                </li>

                <li class="nav-item">
                    <a href="kategori_pelanggaran.php" class="nav-link text-white rounded">
                        Kategori Pelanggaran
                    </a>
                </li>

                <li class="nav-item">
                    <a href="jenis_pelanggaran.php" class="nav-link text-white rounded">
                        Jenis Pelanggaran
                    </a>
                </li>

                <li class="nav-item">
                    <a href="cetak_export.php" class="nav-link text-white rounded">
                        Cetak / Export
                    </a>
                </li>

                <li class="nav-item">
                    <a href="logout.php" class="nav-link text-danger rounded">
                        Logout
                    </a>
                </li>
            </ul>
        </div>

        <!-- KONTEN -->
        <div class="col-md-9 col-lg-10 p-4">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold mb-1">Data Tahun Ajaran</h2>
                    <p class="text-secondary mb-2">Daftar data tahun ajaran</p>
                    <a href="dashboard.php" class="btn btn-secondary btn-sm">
                        Kembali ke Dashboard
                    </a>
                </div>

                <a href="tambah_tahun_ajaran.php" class="btn btn-primary">
                    + Tambah Tahun Ajaran
                </a>
            </div>

            <?php if (isset($_GET['pesan']) && $_GET['pesan'] == 'berhasil') { ?>
                <div class="alert alert-success">
                    Data tahun ajaran berhasil ditambahkan.
                </div>
            <?php } ?>

            <!-- TABEL TAHUN AJARAN -->
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">

                        <table class="table align-middle">
                            <thead>
                                <tr class="table-primary">
                                    <th>No</th>
                                    <th>Nama Tahun Ajaran</th>
                                    <th>Tanggal Mulai</th>
                                    <th>Tanggal Selesai</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php
                                $no = 1;

                                while ($tahun = mysqli_fetch_assoc($query)) {
                                ?>
                                    <tr>
                                        <td><?= $no++; ?></td>

                                        <td>
                                            <strong>
                                                <?= htmlspecialchars($tahun['nama']); ?>
                                            </strong>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($tahun['tanggal_mulai']); ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($tahun['tanggal_selesai']); ?>
                                        </td>

                                        <td>
                                            <?php if ((int)$tahun['status_aktif'] === 1) { ?>
                                                <span class="badge bg-success">Aktif</span>
                                            <?php } else { ?>
                                                <span class="badge bg-secondary">Tidak Aktif</span>
                                            <?php } ?>
                                        </td>

                                        <td>
                                            <a
                                                href="edit_tahun_ajaran.php?id=<?= (int)$tahun['id']; ?>"
                                                class="btn btn-warning btn-sm"
                                            >
                                                Edit
                                            </a>

                                            <a
                                                href="hapus_tahun_ajaran.php?id=<?= (int)$tahun['id']; ?>"
                                                class="btn btn-danger btn-sm"
                                                onclick="return confirm('Yakin ingin menghapus data tahun ajaran ini?')"
                                            >
                                                Hapus
                                            </a>
                                        </td>
                                    </tr>
                                <?php } ?>

                                <?php if (mysqli_num_rows($query) === 0) { ?>
                                    <tr>
                                        <td colspan="6" class="text-center">
                                            Belum ada data tahun ajaran.
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
</div>

</body>
</html>
```