
<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

$role = strtolower(trim($_SESSION['role'] ?? ''));

if ($role !== 'admin') {
    exit("Anda tidak memiliki akses");
}

require_once __DIR__ . "/config/koneksi.php";

$nama = htmlspecialchars(
    $_SESSION['name'] ?? 'Pengguna',
    ENT_QUOTES,
    'UTF-8'
);

$query = mysqli_query($koneksi, "SELECT * FROM t_guru ORDER BY id DESC");

if (!$query) {
    die("Gagal mengambil data guru: " . mysqli_error($koneksi));
}

$menuAdmin = [
    ['Dashboard', 'dashboard.php'],
    ['Data Siswa', 'siswa.php'],
    ['Data Guru', 'guru.php'],
    ['Kelas', 'kelas.php'],
    ['Tahun Ajaran', 'tahun_ajaran.php'],
    ['Penempatan Siswa', 'penempatan_siswa.php'],
    ['Wali Kelas', 'wali_kelas.php'],
    ['Kategori Pelanggaran', 'kategori_pelanggaran.php'],
    ['Jenis Pelanggaran', 'jenis_pelanggaran.php'],
    ['Laporan', 'laporan.php'],
    ['About Me', 'abautme.php']
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Data Guru - Sistem Informasi Pelanggaran Siswa</title>

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
                    <img src="logo.png" alt="Logo Aplikasi" width="44" height="44"
                         class="rounded-circle bg-white p-1">

                    <div class="text-white text-break">
                        <div class="fw-semibold"><?= $nama; ?></div>
                        <small class="text-secondary">ADMIN</small>
                    </div>
                </div>
            </div>

            <div class="text-secondary small fw-semibold mb-3">MENU UTAMA</div>

            <ul class="nav nav-pills flex-column gap-2">
                <?php foreach ($menuAdmin as $item): ?>
                    <li class="nav-item">
                        <a
                            href="<?= htmlspecialchars($item[1], ENT_QUOTES, 'UTF-8'); ?>"
                            class="nav-link <?= $item[0] === 'Data Guru' ? 'active' : 'text-white'; ?> rounded-2 py-2 px-3"
                            <?= $item[0] === 'Data Guru' ? 'aria-current="page"' : ''; ?>
                        >
                            <?= htmlspecialchars($item[0], ENT_QUOTES, 'UTF-8'); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>

            <hr class="border-secondary my-4">

            <a href="logout.php" class="nav-link text-danger rounded-2 py-2 px-3">
                <i class="bi bi-box-arrow-left me-2"></i>
                Logout
            </a>

        </aside>

        <!-- KONTEN DATA GURU -->
        <main class="col-12 col-md-9 col-lg-10 p-3 p-lg-5">

            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4 pb-4 border-bottom">
                <div>
                    <h2 class="fw-bold mb-2">Data Guru</h2>
                    <p class="text-secondary mb-2">
                        Kelola data guru pada Sistem Informasi Pelanggaran Siswa.
                    </p>

                    <a href="dashboard.php" class="btn btn-secondary btn-sm rounded-2">
                        Kembali ke Dashboard
                    </a>
                </div>

                <a href="tambah_guru.php" class="btn btn-primary rounded-2 px-4">
                    + Tambah Guru
                </a>
            </div>

            <!-- NOTIFIKASI -->
            <?php if (isset($_GET['pesan'])): ?>
                <?php
                $pesan = $_GET['pesan'];
                $pesanSukses = [
                    'berhasil' => 'Data guru berhasil ditambahkan.',
                    'edit_berhasil' => 'Data guru berhasil diperbarui.',
                    'hapus' => 'Data guru berhasil dihapus.'
                ];

                $pesanGagal = [
                    'gagal_hapus' => 'Data guru gagal dihapus.',
                    'gagal_edit' => 'Data guru gagal diperbarui.',
                    'gagal_tambah' => 'Data guru gagal ditambahkan.'
                ];
                ?>

                <?php if (isset($pesanSukses[$pesan])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        <strong>Berhasil!</strong>
                        <?= htmlspecialchars($pesanSukses[$pesan], ENT_QUOTES, 'UTF-8'); ?>

                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
                    </div>

                <?php elseif (isset($pesanGagal[$pesan])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <strong>Gagal!</strong>
                        <?= htmlspecialchars($pesanGagal[$pesan], ENT_QUOTES, 'UTF-8'); ?>

                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <!-- TABEL GURU -->
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-3 p-lg-4">

                    <div class="table-responsive">
                        <table id="tabelGuru" class="table table-hover align-middle">
                            <thead>
                                <tr class="table-primary">
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

                                <?php while ($guru = mysqli_fetch_assoc($query)): ?>
                                    <tr>
                                        <td><?= $no++; ?></td>

                                        <td>
                                            <?= htmlspecialchars($guru['nip'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                        </td>

                                        <td class="fw-semibold">
                                            <?= htmlspecialchars($guru['nama'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($guru['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                        </td>

                                        <td>
                                            <?php if ((int) ($guru['status_aktif'] ?? 0) === 1): ?>
                                                <span class="badge bg-success">Aktif</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Tidak Aktif</span>
                                            <?php endif; ?>
                                        </td>

                                        <td>
                                            <div class="d-flex flex-wrap gap-1">
                                                <a
                                                    href="edit_guru.php?id=<?= (int) $guru['id']; ?>"
                                                    class="btn btn-warning btn-sm"
                                                >Edit</a>

                                                <a
                                                    href="hapus_guru.php?id=<?= (int) $guru['id']; ?>"
                                                    class="btn btn-danger btn-sm"
                                                    onclick="return confirm('Yakin ingin menghapus data guru ini?')"
                                                >Hapus</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
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
    $('#tabelGuru').DataTable({
        pageLength: 10,
        lengthMenu: [5, 10, 25, 50, 100],
        ordering: true,
        searching: true,
        language: {
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data guru",
            infoEmpty: "Tidak ada data guru",
            infoFiltered: "(disaring dari _MAX_ total data)",
            zeroRecords: "Data guru tidak ditemukan",
            emptyTable: "Belum ada data guru",
            paginate: {
                first: "Pertama",
                last: "Terakhir",
                next: "Berikutnya",
                previous: "Sebelumnya"
            }
        },
        columnDefs: [
            {
                targets: [0, 5],
                orderable: false
            }
        ]
    });
});
</script>

</body>
</html>
