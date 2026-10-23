
<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    exit("Anda tidak memiliki akses");
}

require_once __DIR__ . "/config/koneksi.php";

$query = mysqli_query($koneksi, "SELECT * FROM t_tahun_ajaran ORDER BY id ASC");

if (!$query) {
    die("Gagal mengambil data tahun ajaran: " . mysqli_error($koneksi));
}

$pesan = $_GET['pesan'] ?? '';

$notifikasi = [
    'berhasil' => ['success', 'Data tahun ajaran berhasil ditambahkan!'],
    'edit_berhasil' => ['success', 'Data tahun ajaran berhasil diubah!'],
    'hapus_berhasil' => ['success', 'Data tahun ajaran berhasil dihapus!'],
    'gagal_hapus' => ['danger', 'Data tahun ajaran gagal dihapus!'],
    'edit_gagal' => ['danger', 'Data tahun ajaran gagal diubah!']
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Data Tahun Ajaran</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/2.3.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container-fluid">
    <div class="row">

        <!-- SIDEBAR -->
        <aside class="col-12 col-md-3 col-lg-2 bg-dark min-vh-100 p-3">

            <div class="text-white mb-4 pt-3 pb-3 border-bottom border-secondary">
                <h5 class="fw-bold mb-2">
                    <i class="bi bi-shield-check me-2"></i>
                    Sistem Pelanggaran
                </h5>
                <small class="text-secondary">Panel Admin</small>
            </div>

            <div class="bg-secondary bg-opacity-25 rounded-3 p-3 mb-4">
                <div class="d-flex align-items-center gap-3">
                    <img src="logo.png" alt="Logo Aplikasi" width="44" height="44" class="rounded-circle bg-white p-1">

                    <div class="text-white text-break">
                        <div class="fw-semibold">
                            <?= htmlspecialchars($_SESSION['name'] ?? 'Pengguna'); ?>
                        </div>
                        <small class="text-secondary">ADMIN</small>
                    </div>
                </div>
            </div>

            <div class="text-secondary small fw-semibold mb-3">MENU UTAMA</div>

            <ul class="nav nav-pills flex-column gap-2">
                <li class="nav-item">
                    <a href="dashboard.php" class="nav-link text-white rounded-2 py-2 px-3">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a href="siswa.php" class="nav-link text-white rounded-2 py-2 px-3">Data Siswa</a>
                </li>
                <li class="nav-item">
                    <a href="guru.php" class="nav-link text-white rounded-2 py-2 px-3">Data Guru</a>
                </li>
                <li class="nav-item">
                    <a href="kelas.php" class="nav-link text-white rounded-2 py-2 px-3">Kelas</a>
                </li>
                <li class="nav-item">
                    <a href="tahun_ajaran.php" class="nav-link active rounded-2 py-2 px-3">Tahun Ajaran</a>
                </li>
                <li class="nav-item">
                    <a href="penempatan_siswa.php" class="nav-link text-white rounded-2 py-2 px-3">Penempatan Siswa</a>
                </li>
                <li class="nav-item">
                    <a href="wali_kelas.php" class="nav-link text-white rounded-2 py-2 px-3">Wali Kelas</a>
                </li>
                <li class="nav-item">
                    <a href="kategori_pelanggaran.php" class="nav-link text-white rounded-2 py-2 px-3">Kategori Pelanggaran</a>
                </li>
                <li class="nav-item">
                    <a href="jenis_pelanggaran.php" class="nav-link text-white rounded-2 py-2 px-3">Jenis Pelanggaran</a>
                </li>
                <li class="nav-item">
                    <a href="laporan.php" class="nav-link text-white rounded-2 py-2 px-3">Laporan</a>
                </li>
                <li class="nav-item">
                    <a href="abautme.php" class="nav-link text-white rounded-2 py-2 px-3">About Me</a>
                </li>
            </ul>

            <hr class="border-secondary my-4">

            <a href="logout.php" class="nav-link text-danger rounded-2 py-2 px-3">
                <i class="bi bi-box-arrow-left me-2"></i> Logout
            </a>

        </aside>

        <!-- KONTEN UTAMA -->
        <main class="col-12 col-md-9 col-lg-10 p-3 p-lg-5">

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-4 border-bottom">
                <div>
                    <h2 class="fw-bold mb-2">Data Tahun Ajaran</h2>
                    <p class="text-secondary mb-3">Daftar data tahun ajaran</p>
                    <a href="dashboard.php" class="btn btn-secondary btn-sm">Kembali ke Dashboard</a>
                </div>

                <!-- TOMBOL TAMBAH -->
                <a href="./tambah_tahun_ajaran.php" class="btn btn-primary rounded-2">
                    <i class="bi bi-plus-lg me-1"></i> Tambah Tahun Ajaran
                </a>
            </div>

            <!-- NOTIFIKASI -->
            <?php if (isset($notifikasi[$pesan])): ?>
                <div class="alert alert-<?= $notifikasi[$pesan][0]; ?> alert-dismissible fade show" role="alert">
                    <i class="bi bi-<?= $notifikasi[$pesan][0] === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill'; ?> me-2"></i>

                    <strong>
                        <?= $notifikasi[$pesan][0] === 'success' ? 'Berhasil!' : 'Gagal!'; ?>
                    </strong>

                    <?= htmlspecialchars($notifikasi[$pesan][1]); ?>

                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
                </div>
            <?php endif; ?>

            <!-- TABEL -->
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table id="tabelTahunAjaran" class="table table-hover align-middle">
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
                            <?php $no = 1; ?>

                            <?php while ($tahun = mysqli_fetch_assoc($query)): ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td class="fw-semibold"><?= htmlspecialchars($tahun['nama']); ?></td>
                                    <td><?= htmlspecialchars($tahun['tanggal_mulai']); ?></td>
                                    <td><?= htmlspecialchars($tahun['tanggal_selesai']); ?></td>

                                    <td>
                                        <?php if ((int)$tahun['status_aktif'] === 1): ?>
                                            <span class="badge bg-success">Aktif</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Tidak Aktif</span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="edit_tahun_ajaran.php?id=<?= (int)$tahun['id']; ?>" class="btn btn-warning btn-sm">Edit</a>

                                            <a href="hapus_tahun_ajaran.php?id=<?= (int)$tahun['id']; ?>"
                                               class="btn btn-danger btn-sm"
                                               onclick="return confirm('Yakin ingin menghapus data tahun ajaran ini?')">
                                                Hapus
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>

                            <?php if (mysqli_num_rows($query) === 0): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-secondary">Belum ada data tahun ajaran.</td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="text-secondary small mt-5 pt-3 border-top">
                Sistem Informasi Pelanggaran Siswa
            </div>

        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function () {
    $('#tabelTahunAjaran').DataTable({
        pageLength: 10,
        lengthMenu: [5, 10, 25, 50, 100],
        language: {
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data tahun ajaran",
            infoEmpty: "Tidak ada data tahun ajaran",
            infoFiltered: "(disaring dari _MAX_ total data)",
            zeroRecords: "Data tahun ajaran tidak ditemukan",
            emptyTable: "Belum ada data tahun ajaran",
            paginate: {
                first: "Pertama",
                last: "Terakhir",
                next: "Berikutnya",
                previous: "Sebelumnya"
            }
        },
        columnDefs: [
            { targets: [0, 5], orderable: false }
        ]
    });
});
</script>

</body>
</html>
