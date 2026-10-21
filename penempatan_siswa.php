
<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

if (strtolower(trim($_SESSION['role'] ?? '')) !== 'admin') {
    exit("Anda tidak memiliki akses");
}

require_once __DIR__ . "/config/koneksi.php";

function aman($nilai) {
    return htmlspecialchars((string)($nilai ?? ''), ENT_QUOTES, 'UTF-8');
}

if (!isset($_SESSION['data_penempatan'])) {
    $_SESSION['data_penempatan'] = [
        [
            'id' => 1,
            'siswa_id' => 1,
            'nisn' => 'NISN0000000001',
            'nama_siswa' => 'Alya Putri',
            'kelas_id' => 39,
            'nama_kelas' => 'X BDP 2',
            'tahun_ajaran_id' => 34,
            'nama_tahun_ajaran' => 'Tahun Ajaran 2011/20',
            'tanggal_mulai' => '2026-10-20',
            'tanggal_selesai' => '2026-12-23',
            'status_aktif' => 1
        ]
    ];
}

$data_penempatan = &$_SESSION['data_penempatan'];

if (isset($_GET['hapus'])) {
    $id_hapus = (int) $_GET['hapus'];

    foreach ($data_penempatan as $index => $row) {
        if ((int)$row['id'] === $id_hapus) {
            unset($data_penempatan[$index]);
            $_SESSION['data_penempatan'] = array_values($data_penempatan);
            header("Location: penempatan_siswa.php?pesan=hapus");
            exit;
        }
    }

    header("Location: penempatan_siswa.php?pesan=gagal_hapus");
    exit;
}

$query_siswa = mysqli_query($koneksi, "SELECT id, nisn, nama FROM t_siswa ORDER BY nama ASC");
$query_kelas = mysqli_query($koneksi, "SELECT id, nama FROM t_kelas ORDER BY nama ASC");
$query_tahun = mysqli_query($koneksi, "SELECT id, nama FROM t_tahun_ajaran ORDER BY id DESC");

if (!$query_siswa || !$query_kelas || !$query_tahun) {
    die("Gagal mengambil data referensi: " . mysqli_error($koneksi));
}

$siswa_map = [];
while ($s = mysqli_fetch_assoc($query_siswa)) {
    $siswa_map[(int)$s['id']] = $s;
}

$kelas_map = [];
while ($k = mysqli_fetch_assoc($query_kelas)) {
    $kelas_map[(int)$k['id']] = $k['nama'];
}

$tahun_map = [];
while ($t = mysqli_fetch_assoc($query_tahun)) {
    $tahun_map[(int)$t['id']] = $t['nama'];
}

usort($data_penempatan, function ($a, $b) {
    return (int)$b['id'] <=> (int)$a['id'];
});

$pesan_url = $_GET['pesan'] ?? '';

$notifikasi = [
    'berhasil' => ['success', 'Penempatan siswa berhasil ditambahkan.'],
    'edit' => ['success', 'Penempatan siswa berhasil diperbarui.'],
    'hapus' => ['success', 'Penempatan siswa berhasil dihapus.'],
    'gagal_hapus' => ['danger', 'Data penempatan siswa gagal dihapus atau tidak ditemukan.']
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Penempatan Siswa</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/2.1.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">

    <style>
        body {
            background: #f5f7fa;
        }

        .sidebar {
            background: #212529;
            min-height: 100vh;
        }

        .sidebar .nav-link {
            color: white;
            padding: 10px 14px;
            border-radius: 6px;
        }

        .sidebar .nav-link:hover {
            background: #343a40;
        }

        .sidebar .nav-link.active {
            background: #0d6efd;
            color: white;
        }

        .card {
            border-radius: 8px;
        }
    </style>
</head>

<body>

<div class="container-fluid">
    <div class="row">

        <aside class="col-12 col-md-3 col-lg-2 sidebar p-3">
            <div class="text-white mb-4">
                <h5 class="fw-bold mb-1">
                    <i class="bi bi-shield-check me-2"></i>Sistem Pelanggaran
                </h5>
                <small class="text-secondary">Panel Admin</small>
            </div>

            <div class="d-flex align-items-center bg-secondary bg-opacity-25 rounded p-3 mb-4">
                <i class="bi bi-person-circle fs-3 text-white me-2"></i>
                <div class="text-white">
                    <small class="text-secondary d-block">Login sebagai</small>
                    <?= aman($_SESSION['name'] ?? 'Admin') ?>
                </div>
            </div>

            <p class="text-secondary small fw-bold">MENU UTAMA</p>

            <ul class="nav nav-pills flex-column gap-1">
                <li><a href="dashboard.php" class="nav-link">Dashboard</a></li>
                <li><a href="siswa.php" class="nav-link">Data Siswa</a></li>
                <li><a href="guru.php" class="nav-link">Data Guru</a></li>
                <li><a href="kelas.php" class="nav-link">Kelas</a></li>
                <li><a href="tahun_ajaran.php" class="nav-link">Tahun Ajaran</a></li>
                <li><a href="penempatan_siswa.php" class="nav-link active">Penempatan Siswa</a></li>
                <li><a href="wali_kelas.php" class="nav-link">Wali Kelas</a></li>
                <li><a href="kategori_pelanggaran.php" class="nav-link">Kategori Pelanggaran</a></li>
                <li><a href="jenis_pelanggaran.php" class="nav-link">Jenis Pelanggaran</a></li>
                <li><a href="laporan.php" class="nav-link">Laporan</a></li>
                <li><a href="abautme.php" class="nav-link">About Me</a></li>
            </ul>

            <hr class="border-secondary">

            <a href="logout.php" class="nav-link text-danger">
                <i class="bi bi-box-arrow-left me-2"></i>Logout
            </a>
        </aside>

        <main class="col-12 col-md-9 col-lg-10 p-3 p-lg-4">

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                <div>
                    <h2 class="fw-bold mb-1">Penempatan Siswa</h2>
                    <p class="text-secondary mb-0">Kelola penempatan siswa ke kelas</p>
                </div>

                <a href="tambah_penempatan_siswa.php" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i>Tambah Penempatan Siswa
                </a>
            </div>

            <?php if (isset($notifikasi[$pesan_url])): ?>
                <div class="alert alert-<?= aman($notifikasi[$pesan_url][0]) ?> alert-dismissible fade show" role="alert">
                    <i class="bi bi-<?= $pesan_url === 'hapus' || $pesan_url === 'edit' || $pesan_url === 'berhasil' ? 'check-circle-fill' : 'exclamation-triangle-fill' ?> me-2"></i>
                    <?= aman($notifikasi[$pesan_url][1]) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
                </div>
            <?php endif; ?>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Data Penempatan Siswa</h5>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table id="tabelPenempatan" class="table table-bordered table-hover align-middle w-100">
                            <thead>
                                <tr>
                                    <th class="table-primary">No</th>
                                    <th class="table-primary">NISN</th>
                                    <th class="table-primary">Nama Siswa</th>
                                    <th class="table-primary">Kelas</th>
                                    <th class="table-primary">Tahun Ajaran</th>
                                    <th class="table-primary">Tanggal Mulai</th>
                                    <th class="table-primary">Tanggal Selesai</th>
                                    <th class="table-primary">Status</th>
                                    <th class="table-primary">Aksi</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php foreach ($data_penempatan as $row): ?>
                                    <?php
                                    $sid = (int)($row['siswa_id'] ?? 0);
                                    $kid = (int)($row['kelas_id'] ?? 0);
                                    $tid = (int)($row['tahun_ajaran_id'] ?? 0);
                                    $s = $siswa_map[$sid] ?? null;
                                    ?>

                                    <tr>
                                        <td><?= (int)$row['id'] ?></td>
                                        <td><?= aman($s['nisn'] ?? $row['nisn'] ?? '-') ?></td>
                                        <td><?= aman($s['nama'] ?? $row['nama_siswa'] ?? '-') ?></td>
                                        <td><?= aman($kelas_map[$kid] ?? $row['nama_kelas'] ?? '-') ?></td>
                                        <td><?= aman($tahun_map[$tid] ?? $row['nama_tahun_ajaran'] ?? '-') ?></td>
                                        <td><?= aman($row['tanggal_mulai'] ?? '-') ?></td>
                                        <td><?= aman($row['tanggal_selesai'] ?? '-') ?></td>
                                        <td>
                                            <?php if ((int)($row['status_aktif'] ?? 1) === 1): ?>
                                                <span class="badge bg-success">Aktif</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Tidak Aktif</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-nowrap">
                                            <a href="edit_penempatan_siswa.php?id=<?= (int)$row['id'] ?>"
                                               class="btn btn-warning btn-sm">
                                                Edit
                                            </a>

                                            <a href="hapus_penempatan_siswa.php?id=<?= (int)$row['id'] ?>"
                                               class="btn btn-danger btn-sm"
                                               onclick="return confirm('Yakin ingin menghapus data penempatan siswa ini?')">
                                                Hapus
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </main>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function () {
    $('#tabelPenempatan').DataTable({
        pageLength: 10,
        lengthMenu: [5, 10, 25, 50],
        language: {
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            infoEmpty: "Tidak ada data yang ditampilkan",
            infoFiltered: "(difilter dari _MAX_ total data)",
            zeroRecords: "Data tidak ditemukan",
            emptyTable: "Belum ada data penempatan siswa.",
            paginate: {
                first: "Pertama",
                last: "Terakhir",
                next: "Berikutnya",
                previous: "Sebelumnya"
            }
        },
        columnDefs: [{
            targets: [0, 8],
            orderable: false,
            searchable: false
        }]
    });
});
</script>

</body>
</html>
