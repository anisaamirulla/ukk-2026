
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

$query = mysqli_query($koneksi, "
    SELECT p.*, k.nama AS nama_kategori
    FROM t_pelanggaran p
    LEFT JOIN t_pelanggaran_kategori k
        ON p.pelanggaran_kategori_id = k.id
    ORDER BY p.id DESC
");

if (!$query) {
    die("Query gagal: " . mysqli_error($koneksi));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jenis Pelanggaran</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>

<body class="bg-light">

<div class="container-fluid">
    <div class="row">

        <!-- SIDEBAR -->
        <div class="col-md-3 col-lg-2 bg-dark min-vh-100 p-3">

            <h4 class="text-white mb-4">Sistem Pelanggaran</h4>

            <ul class="nav nav-pills flex-column">

                <li class="nav-item mb-2">
                    <a href="dashboard.php" class="nav-link text-white">Dashboard</a>
                </li>

                <li class="nav-item mb-2">
                    <a href="siswa.php" class="nav-link text-white">Data Siswa</a>
                </li>

                <li class="nav-item mb-2">
                    <a href="guru.php" class="nav-link text-white">Data Guru</a>
                </li>

                <li class="nav-item mb-2">
                    <a href="kelas.php" class="nav-link text-white">Kelas</a>
                </li>

                <li class="nav-item mb-2">
                    <a href="tahun_ajaran.php" class="nav-link text-white">Tahun Ajaran</a>
                </li>

                <li class="nav-item mb-2">
                    <a href="penempatan_siswa.php" class="nav-link text-white">Penempatan Siswa</a>
                </li>

                <li class="nav-item mb-2">
                    <a href="wali_kelas.php" class="nav-link text-white">Wali Kelas</a>
                </li>

                <li class="nav-item mb-2">
                    <a href="kategori_pelanggaran.php" class="nav-link text-white">Kategori Pelanggaran</a>
                </li>

                <li class="nav-item mb-2">
                    <a href="jenis_pelanggaran.php" class="nav-link active">Jenis Pelanggaran</a>
                </li>

                <li class="nav-item mb-2">
                    <a href="laporan.php" class="nav-link text-white">Laporan</a>
                </li>

                <li class="nav-item mb-2">
                    <a href="abautme.php" class="nav-link text-white">About Me</a>
                </li>

                <hr class="text-secondary">

                <li class="nav-item">
                    <a href="logout.php" class="nav-link text-danger">Logout</a>
                </li>

            </ul>
        </div>

        <!-- KONTEN UTAMA -->
        <main class="col-md-9 col-lg-10 p-4">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="fw-bold mb-1">Jenis Pelanggaran</h3>
                    <p class="text-secondary mb-0">
                        Daftar jenis pelanggaran siswa
                    </p>
                </div>

                <a href="tambah_jenis_pelanggaran.php" class="btn btn-primary">
                    + Tambah Jenis
                </a>
            </div>

            <div class="mb-3">
                <a href="dashboard.php" class="btn btn-secondary">
                    Kembali ke Dashboard
                </a>
            </div>

            <?php if (isset($_GET['pesan'])): ?>
                <?php if ($_GET['pesan'] == 'edit'): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        Data jenis pelanggaran berhasil diperbarui.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php elseif ($_GET['pesan'] == 'hapus'): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        Data jenis pelanggaran berhasil dihapus.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php elseif ($_GET['pesan'] == 'gagal'): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        Data gagal diproses. Periksa apakah data masih digunakan pada catatan pelanggaran siswa.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <!-- TABEL JENIS PELANGGARAN -->
            <div class="card border-0 shadow-sm">

                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Data Jenis Pelanggaran</h5>
                </div>

                <div class="card-body">
                    <div class="table-responsive">

                        <table class="table align-middle table-hover">

                            <thead class="table-primary">
                                <tr>
                                    <th>No</th>
                                    <th>Kode</th>
                                    <th>Nama Pelanggaran</th>
                                    <th>Kategori</th>
                                    <th>Poin</th>
                                    <th>Deskripsi</th>
                                    <th>Status</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>

                            <tbody>
                            <?php if (mysqli_num_rows($query) > 0): ?>
                                <?php $no = 1; ?>

                                <?php while ($data = mysqli_fetch_assoc($query)): ?>
                                    <tr>
                                        <td><?= $no++; ?></td>

                                        <td><?= htmlspecialchars($data['kode'] ?? ''); ?></td>

                                        <td><?= htmlspecialchars($data['nama'] ?? ''); ?></td>

                                        <td><?= htmlspecialchars($data['nama_kategori'] ?? '-'); ?></td>

                                        <td><?= (int)($data['poin'] ?? 0); ?></td>

                                        <td><?= htmlspecialchars($data['deksripsi'] ?? ''); ?></td>

                                        <td>
                                            <?php if (($data['status_aktif'] ?? 0) == 1): ?>
                                                <span class="badge bg-success">Aktif</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Tidak Aktif</span>
                                            <?php endif; ?>
                                        </td>

                                        <td class="text-center">
                                            <a href="edit_jenis_pelanggaran.php?id=<?= (int)$data['id']; ?>"
                                               class="btn btn-warning btn-sm">
                                                Edit
                                            </a>

                                            <form action="hapus_jenis_pelanggaran.php"
                                                  method="POST"
                                                  class="d-inline"
                                                  onsubmit="return confirm('Yakin ingin menghapus jenis pelanggaran ini?');">

                                                <input type="hidden" name="id"
                                                       value="<?= (int)$data['id']; ?>">

                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    Hapus
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>

                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center text-secondary py-4">
                                        Belum ada data jenis pelanggaran.
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