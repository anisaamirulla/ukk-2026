
<?php
session_start();
require_once __DIR__ . "/config/koneksi.php";

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    exit("Anda tidak memiliki akses");
}

$query = mysqli_query(
    $koneksi,
    "SELECT * FROM t_pelanggaran_siswa ORDER BY tanggal DESC"
);

if (!$query) {
    die("Query gagal: " . mysqli_error($koneksi));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Laporan Pelanggaran</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>

<body class="bg-light">

<div class="container-fluid">
    <div class="row">

        <!-- SIDEBAR SAMA SEPERTI DASHBOARD -->
        <div class="col-md-3 col-lg-2 bg-dark min-vh-100 p-3">

            <h4 class="text-white mb-4">Sistem Pelanggaran</h4>

            <ul class="nav nav-pills flex-column">

                <li class="nav-item mb-2">
                    <a href="dashboard.php" class="nav-link text-white">
                        Dashboard
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a href="siswa.php" class="nav-link text-white">
                        Data Siswa
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a href="guru.php" class="nav-link text-white">
                        Data Guru
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a href="kelas.php" class="nav-link text-white">
                        Kelas
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a href="tahun_ajaran.php" class="nav-link text-white">
                        Tahun Ajaran
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a href="penempatan_siswa.php" class="nav-link text-white">
                        Penempatan Siswa
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a href="wali_kelas.php" class="nav-link text-white">
                        Wali Kelas
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a href="kategori_pelanggaran.php" class="nav-link text-white">
                        Kategori Pelanggaran
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a href="jenis_pelanggaran.php" class="nav-link text-white">
                        Jenis Pelanggaran
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a href="laporan.php" class="nav-link active">
                        Laporan
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a href="abautme.php" class="nav-link text-white">
                        Abaut Me
                    </a>
                </li>

                <hr class="text-secondary">

                <li class="nav-item">
                    <a href="logout.php" class="nav-link text-danger">
                        Logout
                    </a>
                </li>

            </ul>
        </div>

        <!-- KONTEN UTAMA -->
        <main class="col-md-9 col-lg-10 p-4">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="fw-bold mb-1">Laporan Pelanggaran Siswa</h3>

                    <p class="text-secondary mb-0">
                        Daftar laporan pelanggaran siswa
                    </p>
                </div>

                <a href="tambah_laporan.php" class="btn btn-primary">
                    + Tambah Laporan
                </a>
            </div>

            <div class="card border-0 shadow-sm">

                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Data Laporan Pelanggaran</h5>
                </div>

                <div class="card-body">

                    <div class="table-responsive">

                        <table class="table table-bordered table-hover align-middle">

                            <thead class="table-primary">
                                <tr>
                                    <th>No</th>
                                    <th>Nama Siswa</th>
                                    <th>Kelas</th>
                                    <th>Pelanggaran</th>
                                    <th>Nama Guru</th>
                                    <th>Tanggal</th>
                                    <th>Poin</th>
                                    <th>Status</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php
                                $no = 1;

                                if (mysqli_num_rows($query) > 0):
                                    while ($data = mysqli_fetch_assoc($query)):
                                ?>
                                    <tr>
                                        <td><?= $no++; ?></td>

                                        <td>
                                            <?= htmlspecialchars($data['nama_siswa'] ?? ''); ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($data['nama_kelas'] ?? ''); ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($data['nama_pelanggaran'] ?? ''); ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($data['nama_guru'] ?? ''); ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($data['tanggal'] ?? ''); ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($data['poin'] ?? ''); ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($data['status'] ?? ''); ?>
                                        </td>
                                    </tr>

                                <?php
                                    endwhile;
                                else:
                                ?>
                                    <tr>
                                        <td colspan="8" class="text-center text-secondary py-4">
                                            Belum ada data laporan.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>

                        </table>

                    </div>

                    <a href="dashboard.php" class="btn btn-secondary">
                        Kembali ke Dashboard
                    </a>

                </div>
            </div>

        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
```