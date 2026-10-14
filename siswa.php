
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

$query = mysqli_query($koneksi, "SELECT * FROM t_siswa ORDER BY id ASC");

if (!$query) {
    die("Gagal mengambil data siswa: " . mysqli_error($koneksi));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Data Siswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container-fluid">

    <div class="row">

        <!-- SIDEBAR -->
        <div class="col-md-3 col-lg-2 bg-dark min-vh-100 p-3">

            <h4 class="text-white mb-4">
                Sistem Pelanggaran
            </h4>

            <ul class="nav nav-pills flex-column">

                <li class="nav-item mb-2">
                    <a href="dashboard.php"
                       class="nav-link text-white">
                        Dashboard
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a href="siswa.php"
                       class="nav-link active">
                        Data Siswa
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a href="guru.php"
                       class="nav-link text-white">
                        Data Guru
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a href="kelas.php"
                       class="nav-link text-white">
                        Kelas
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a href="tahun_ajaran.php"
                       class="nav-link text-white">
                        Tahun Ajaran
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a href="penempatan_siswa.php"
                       class="nav-link text-white">
                        Penempatan Siswa
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a href="wali_kelas.php"
                       class="nav-link text-white">
                        Wali Kelas
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a href="kategori_pelanggaran.php"
                       class="nav-link text-white">
                        Kategori Pelanggaran
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a href="jenis_pelanggaran.php"
                       class="nav-link text-white">
                        Jenis Pelanggaran
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a href="laporan.php"
                       class="nav-link text-white">
                        Laporan
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a href="abautme.php"
                       class="nav-link text-white">
                        Abaut Me
                    </a>
                </li>

                <hr class="text-secondary">

                <li class="nav-item">
                    <a href="logout.php"
                       class="nav-link text-danger">
                        Logout
                    </a>
                </li>

            </ul>

        </div>

        <!-- KONTEN -->
        <div class="col-md-9 col-lg-10 p-4">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold mb-1">Data Siswa</h2>
                    <p class="text-secondary mb-2">Daftar data siswa</p>
                    <a href="dashboard.php" class="btn btn-secondary btn-sm">
                        Kembali ke Dashboard
                    </a>
                </div>

                <a href="tambah_siswa.php" class="btn btn-primary">
                    + Tambah Siswa
                </a>
            </div>

            <?php if (isset($_GET['pesan']) && $_GET['pesan'] == 'berhasil') { ?>
                <div class="alert alert-success">
                    Data siswa berhasil ditambahkan.
                </div>
            <?php } ?>

            <!-- TABEL SISWA -->
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">

                        <table class="table align-middle">
                            <thead>
                                <tr class="table-primary">
                                    <th>No</th>
                                    <th>NISN</th>
                                    <th>NIP</th>
                                    <th>Nama Siswa</th>
                                    <th>Jenis Kelamin</th>
                                    <th>Tanggal Lahir</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php
                                $no = 1;

                                while ($siswa = mysqli_fetch_assoc($query)) {
                                ?>
                                    <tr>
                                        <td><?= $no++; ?></td>

                                        <td>
                                            <?= htmlspecialchars($siswa['nisn']); ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($siswa['nip']); ?>
                                        </td>

                                        <td>
                                            <strong>
                                                <?= htmlspecialchars($siswa['nama']); ?>
                                            </strong>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($siswa['jenis_kelamin']); ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($siswa['tanggal_lahir']); ?>
                                        </td>

                                        <td>
                                            <?php if ((int)$siswa['status_aktif'] === 1) { ?>
                                                <span class="badge bg-success">Aktif</span>
                                            <?php } else { ?>
                                                <span class="badge bg-secondary">Tidak Aktif</span>
                                            <?php } ?>
                                        </td>

                                        <td>
                                            <a
                                                href="hapus_siswa.php?id=<?= (int)$siswa['id']; ?>"
                                                class="btn btn-danger btn-sm"
                                                onclick="return confirm('Yakin ingin menghapus data siswa ini?')"
                                            >
                                                Hapus
                                            </a>
                                        </td>
                                    </tr>
                                <?php } ?>

                                <?php if (mysqli_num_rows($query) === 0) { ?>
                                    <tr>
                                        <td colspan="8" class="text-center">
                                            Belum ada data siswa.
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