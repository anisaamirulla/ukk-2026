
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

$query = mysqli_query($koneksi, "SELECT * FROM t_guru ORDER BY id DESC");

if (!$query) {
    die("Gagal mengambil data guru: " . mysqli_error($koneksi));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Data Guru</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
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
                       class="nav-link text-white">
                        Data Siswa
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a href="guru.php"
                       class="nav-link active">
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


        <!-- KONTEN UTAMA -->
        <main class="col-md-9 col-lg-10 p-4">

            <div class="d-flex justify-content-between align-items-start mb-3">
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
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">

                        <table class="table table-hover align-middle">
                            <thead class="table-primary">
                                <tr>
                                    <th>No</th>
                                    <th>NIP</th>
                                    <th>Nama Guru</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>

                            <tbody>
                            <?php $no = 1; ?>

                            <?php while ($guru = mysqli_fetch_assoc($query)) : ?>
                                <tr>
                                    <td><?= $no++ ?></td>

                                    <td>
                                        <?= htmlspecialchars($guru['nip']) ?>
                                    </td>

                                    <td class="fw-semibold">
                                        <?= htmlspecialchars($guru['nama']) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($guru['email']) ?>
                                    </td>

                                    <td>
                                        <?php if ((int)$guru['status_aktif'] === 1) : ?>
                                            <span class="badge bg-success">Aktif</span>
                                        <?php else : ?>
                                            <span class="badge bg-secondary">Tidak Aktif</span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <a href="hapus_guru.php?id=<?= (int)$guru['id'] ?>"
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('Yakin ingin menghapus data guru ini?')">
                                            Hapus
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>

                            <?php if (mysqli_num_rows($query) === 0) : ?>
                                <tr>
                                    <td colspan="6" class="text-center text-secondary">
                                        Belum ada data guru.
                                    </td>
                                </tr>
                            <?php endif; ?>

                            </tbody>
                        </table>

                    </div>
                </div>
            </div>

        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
```