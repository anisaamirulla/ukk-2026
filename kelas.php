
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

$query = mysqli_query($koneksi, "SELECT * FROM t_kelas ORDER BY id ASC");

if (!$query) {
    die("Gagal mengambil data kelas: " . mysqli_error($koneksi));
}

$pesan = $_GET['pesan'] ?? '';
$notifikasi = [
    'berhasil'       => ['success', 'Berhasil!', 'Data kelas berhasil ditambahkan.', 'check-circle-fill'],
    'edit_berhasil'  => ['success', 'Berhasil!', 'Data kelas berhasil diubah.', 'check-circle-fill'],
    'hapus_berhasil' => ['success', 'Berhasil!', 'Data kelas berhasil dihapus.', 'check-circle-fill'],
    'gagal_hapus'    => ['danger', 'Gagal!', 'Data kelas gagal dihapus.', 'exclamation-triangle-fill'],
    'gagal_edit'     => ['danger', 'Gagal!', 'Data kelas gagal diubah.', 'exclamation-triangle-fill']
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Data Kelas</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/2.3.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
</head>

<body class="bg-white">
<div class="container-fluid">
    <div class="row">

        <aside class="col-12 col-md-3 col-lg-2 bg-dark min-vh-100 p-3">
            <div class="text-white mb-4">
                <h4 class="fw-bold mb-0">
                    <i class="bi bi-shield-check me-2"></i>
                    Sistem Pelanggaran
                </h4>
                <small class="text-secondary">Panel Admin</small>
            </div>

            <div class="bg-secondary bg-opacity-25 rounded-3 p-3 mb-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-white text-dark rounded-circle p-2">
                        <i class="bi bi-person-fill fs-4"></i>
                    </div>
                    <div class="text-white">
                        <div class="fw-semibold">
                            <?= htmlspecialchars($_SESSION['name'] ?? 'Pengguna'); ?>
                        </div>
                        <small class="text-secondary">ADMIN</small>
                    </div>
                </div>
            </div>

            <div class="text-secondary small fw-semibold mb-2">MENU UTAMA</div>

            <ul class="nav nav-pills flex-column gap-1">
                <li class="nav-item"><a href="dashboard.php" class="nav-link text-white">Dashboard</a></li>
                <li class="nav-item"><a href="siswa.php" class="nav-link text-white">Data Siswa</a></li>
                <li class="nav-item"><a href="guru.php" class="nav-link text-white">Data Guru</a></li>
                <li class="nav-item"><a href="kelas.php" class="nav-link active">Kelas</a></li>
                <li class="nav-item"><a href="tahun_ajaran.php" class="nav-link text-white">Tahun Ajaran</a></li>
                <li class="nav-item"><a href="penempatan_siswa.php" class="nav-link text-white">Penempatan Siswa</a></li>
                <li class="nav-item"><a href="wali_kelas.php" class="nav-link text-white">Wali Kelas</a></li>
                <li class="nav-item"><a href="kategori_pelanggaran.php" class="nav-link text-white">Kategori Pelanggaran</a></li>
                <li class="nav-item"><a href="jenis_pelanggaran.php" class="nav-link text-white">Jenis Pelanggaran</a></li>
                <li class="nav-item"><a href="laporan.php" class="nav-link text-white">Laporan</a></li>
                <li class="nav-item"><a href="abautme.php" class="nav-link text-white">About Me</a></li>
            </ul>

            <hr class="border-secondary">

            <a href="logout.php" class="nav-link text-danger">
                <i class="bi bi-box-arrow-left me-2"></i>Logout
            </a>
        </aside>

        <main class="col-12 col-md-9 col-lg-10 p-3 p-lg-4">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold mb-1">Data Kelas</h2>
                    <p class="text-secondary mb-2">Daftar data kelas</p>
                    <a href="dashboard.php" class="btn btn-secondary btn-sm">Kembali ke Dashboard</a>
                </div>

                <a href="tambah_kelas.php" class="btn btn-primary">+ Tambah Kelas</a>
            </div>

            <?php if (isset($notifikasi[$pesan])): ?>
                <div class="alert alert-<?= $notifikasi[$pesan][0] ?> alert-dismissible fade show" role="alert">
                    <i class="bi bi-<?= $notifikasi[$pesan][3] ?> me-2"></i>
                    <strong><?= $notifikasi[$pesan][1] ?></strong>
                    <?= $notifikasi[$pesan][2] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
                </div>
            <?php endif; ?>

            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="tabelKelas" class="table table-hover align-middle">
                            <thead>
                                <tr class="table-primary">
                                    <th>No</th>
                                    <th>Nama Kelas</th>
                                    <th>Tingkat</th>
                                    <th>Jurusan</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>

                            <tbody>
                            <?php $no = 1; ?>
                            <?php while ($kelas = mysqli_fetch_assoc($query)): ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><strong><?= htmlspecialchars($kelas['nama']); ?></strong></td>
                                    <td><?= htmlspecialchars($kelas['tingkat']); ?></td>
                                    <td><?= htmlspecialchars($kelas['jurusan']); ?></td>
                                    <td>
                                        <?php if ((int)$kelas['status_aktif'] === 1): ?>
                                            <span class="badge bg-success">Aktif</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Tidak Aktif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="edit_kelas.php?id=<?= (int)$kelas['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                            <a href="hapus_kelas.php?id=<?= (int)$kelas['id']; ?>"
                                               class="btn btn-danger btn-sm"
                                               onclick="return confirm('Yakin ingin menghapus data kelas ini?')">Hapus</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>

                            <?php if (mysqli_num_rows($query) === 0): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-secondary">Belum ada data kelas.</td>
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
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function () {
    $('#tabelKelas').DataTable({
        pageLength: 10,
        lengthMenu: [5, 10, 25, 50, 100],
        language: {
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data kelas",
            infoEmpty: "Tidak ada data kelas",
            infoFiltered: "(disaring dari _MAX_ total data)",
            zeroRecords: "Data kelas tidak ditemukan",
            emptyTable: "Belum ada data kelas",
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
