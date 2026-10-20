
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
    return htmlspecialchars((string)$nilai, ENT_QUOTES, 'UTF-8');
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
            'nama_tahun_ajaran' => 'Tahun Ajaran 2011/2020',
            'tanggal_mulai' => '2026-10-20',
            'tanggal_selesai' => '2026-12-23',
            'status_aktif' => 1
        ]
    ];
}

if (!isset($_SESSION['penempatan_id_berikutnya'])) {
    $ids = array_column($_SESSION['data_penempatan'], 'id');
    $_SESSION['penempatan_id_berikutnya'] = count($ids) > 0
        ? max($ids) + 1
        : 1;
}

$data_penempatan = &$_SESSION['data_penempatan'];
$pesan_error = '';

/* DATA REFERENSI */
$query_siswa = mysqli_query(
    $koneksi,
    "SELECT id, nisn, nama FROM t_siswa ORDER BY nama ASC"
);

$query_kelas = mysqli_query(
    $koneksi,
    "SELECT id, nama FROM t_kelas ORDER BY nama ASC"
);

$query_tahun = mysqli_query(
    $koneksi,
    "SELECT id, nama FROM t_tahun_ajaran ORDER BY id DESC"
);

if (!$query_siswa || !$query_kelas || !$query_tahun) {
    die("Gagal mengambil data: " . mysqli_error($koneksi));
}

$siswa_list = [];
$siswa_map = [];

while ($row = mysqli_fetch_assoc($query_siswa)) {
    $siswa_list[] = $row;
    $siswa_map[(int)$row['id']] = $row;
}

$kelas_list = [];
$kelas_map = [];

while ($row = mysqli_fetch_assoc($query_kelas)) {
    $kelas_list[] = $row;
    $kelas_map[(int)$row['id']] = $row['nama'];
}

$tahun_list = [];
$tahun_map = [];

while ($row = mysqli_fetch_assoc($query_tahun)) {
    $tahun_list[] = $row;
    $tahun_map[(int)$row['id']] = $row['nama'];
}

/* HAPUS DATA */
if (isset($_GET['hapus'])) {
    $id_hapus = (int)$_GET['hapus'];

    foreach ($data_penempatan as $index => $row) {
        if ((int)$row['id'] === $id_hapus) {
            unset($data_penempatan[$index]);
            break;
        }
    }

    $data_penempatan = array_values($data_penempatan);

    header("Location: penempatan_siswa.php?pesan=hapus");
    exit;
}

/* SIMPAN EDIT */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $siswa_id = (int)($_POST['siswa_id'] ?? 0);
    $kelas_id = (int)($_POST['kelas_id'] ?? 0);
    $tahun_id = (int)($_POST['tahun_ajaran_id'] ?? 0);
    $tanggal_mulai = $_POST['tanggal_mulai'] ?? '';
    $tanggal_selesai = $_POST['tanggal_selesai'] ?? '';
    $status = (int)($_POST['status_aktif'] ?? 1);

    $valid_mulai = DateTime::createFromFormat('!Y-m-d', $tanggal_mulai);
    $valid_selesai = DateTime::createFromFormat('!Y-m-d', $tanggal_selesai);

    $tanggal_valid = $valid_mulai &&
        $valid_mulai->format('Y-m-d') === $tanggal_mulai &&
        $valid_selesai &&
        $valid_selesai->format('Y-m-d') === $tanggal_selesai &&
        $tanggal_selesai >= $tanggal_mulai;

    if (
        $id <= 0 ||
        !isset($siswa_map[$siswa_id]) ||
        !isset($kelas_map[$kelas_id]) ||
        !isset($tahun_map[$tahun_id]) ||
        !$tanggal_valid ||
        !in_array($status, [0, 1], true)
    ) {
        $pesan_error = "Periksa kembali data dan tanggal yang kamu masukkan.";
    } else {
        $ditemukan = false;

        foreach ($data_penempatan as $index => $row) {
            if ((int)$row['id'] === $id) {
                $data_penempatan[$index] = [
                    'id' => $id,
                    'siswa_id' => $siswa_id,
                    'nisn' => $siswa_map[$siswa_id]['nisn'],
                    'nama_siswa' => $siswa_map[$siswa_id]['nama'],
                    'kelas_id' => $kelas_id,
                    'nama_kelas' => $kelas_map[$kelas_id],
                    'tahun_ajaran_id' => $tahun_id,
                    'nama_tahun_ajaran' => $tahun_map[$tahun_id],
                    'tanggal_mulai' => $tanggal_mulai,
                    'tanggal_selesai' => $tanggal_selesai,
                    'status_aktif' => $status
                ];

                $ditemukan = true;
                break;
            }
        }

        if ($ditemukan) {
            header("Location: penempatan_siswa.php?pesan=edit");
            exit;
        } else {
            $pesan_error = "Data yang ingin diedit tidak ditemukan.";
        }
    }
}

/* DATA EDIT */
$edit_data = null;

if (isset($_GET['edit'])) {
    $id_edit = (int)$_GET['edit'];

    foreach ($data_penempatan as $row) {
        if ((int)$row['id'] === $id_edit) {
            $edit_data = $row;
            break;
        }
    }
}

/* URUTKAN DATA */
usort($data_penempatan, function ($a, $b) {
    return (int)$b['id'] <=> (int)$a['id'];
});
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Penempatan Siswa</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- DataTables Bootstrap 5 -->
    <link href="https://cdn.datatables.net/2.1.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container-fluid">
    <div class="row">

        <!-- SIDEBAR ADMIN -->
        <aside class="col-12 col-md-3 col-lg-2 bg-dark min-vh-100 p-3">

            <div class="text-white mb-4">
                <h5 class="mb-1">
                    <i class="bi bi-shield-check me-2"></i>Sistem Pelanggaran
                </h5>
                <small class="text-secondary">Panel Admin</small>
            </div>

            <div class="d-flex align-items-center bg-secondary bg-opacity-25 rounded p-3 mb-4">
                <i class="bi bi-person-circle fs-3 text-white me-2"></i>
                <div class="text-white">
                    <small class="text-secondary d-block">Login sebagai</small>
                    <span><?= aman($_SESSION['name'] ?? 'Admin') ?></span>
                </div>
            </div>

            <p class="text-secondary small fw-bold">MENU UTAMA</p>

            <ul class="nav nav-pills flex-column gap-1">
                <li class="nav-item">
                    <a href="dashboard.php" class="nav-link text-white">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a href="siswa.php" class="nav-link text-white">Data Siswa</a>
                </li>
                <li class="nav-item">
                    <a href="guru.php" class="nav-link text-white">Data Guru</a>
                </li>
                <li class="nav-item">
                    <a href="kelas.php" class="nav-link text-white">Kelas</a>
                </li>
                <li class="nav-item">
                    <a href="tahun_ajaran.php" class="nav-link text-white">Tahun Ajaran</a>
                </li>
                <li class="nav-item">
                    <a href="penempatan_siswa.php" class="nav-link active">Penempatan Siswa</a>
                </li>
                <li class="nav-item">
                    <a href="wali_kelas.php" class="nav-link text-white">Wali Kelas</a>
                </li>
                <li class="nav-item">
                    <a href="kategori_pelanggaran.php" class="nav-link text-white">Kategori Pelanggaran</a>
                </li>
                <li class="nav-item">
                    <a href="jenis_pelanggaran.php" class="nav-link text-white">Jenis Pelanggaran</a>
                </li>
                <li class="nav-item">
                    <a href="laporan.php" class="nav-link text-white">Laporan</a>
                </li>
                <li class="nav-item">
                    <a href="abautme.php" class="nav-link text-white">About Me</a>
                </li>
            </ul>

            <hr class="border-secondary">

            <a href="logout.php" class="nav-link text-danger">
                <i class="bi bi-box-arrow-left me-2"></i>Logout
            </a>

        </aside>

        <!-- KONTEN -->
        <main class="col-12 col-md-9 col-lg-10 p-3 p-lg-4">

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                <div>
                    <h2 class="fw-bold mb-1">Penempatan Siswa</h2>
                    <p class="text-secondary mb-0">Kelola penempatan siswa ke kelas</p>
                </div>

                <a href="tambah_penempatan_siswa.php" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i>
                    Tambah Penempatan Siswa
                </a>
            </div>

            <?php
            $pesan_url = $_GET['pesan'] ?? '';

            $pesan_sukses = [
                'berhasil' => 'Penempatan siswa berhasil ditambahkan.',
                'edit' => 'Penempatan siswa berhasil diperbarui.',
                'hapus' => 'Penempatan siswa berhasil dihapus.'
            ];
            ?>

            <?php if (isset($pesan_sukses[$pesan_url])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= aman($pesan_sukses[$pesan_url]) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
                </div>
            <?php endif; ?>

            <?php if ($pesan_error !== ''): ?>
                <div class="alert alert-danger" role="alert">
                    <?= aman($pesan_error) ?>
                </div>
            <?php endif; ?>

            <!-- FORM EDIT -->
            <?php if ($edit_data): ?>
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Edit Penempatan Siswa</h5>
                    </div>

                    <div class="card-body">
                        <form method="POST" action="penempatan_siswa.php?edit=<?= (int)$edit_data['id'] ?>">
                            <input type="hidden" name="id" value="<?= (int)$edit_data['id'] ?>">

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Siswa</label>
                                    <select name="siswa_id" class="form-select" required>
                                        <option value="">-- Pilih Siswa --</option>
                                        <?php foreach ($siswa_list as $s): ?>
                                            <option value="<?= (int)$s['id'] ?>"
                                                <?= (int)$edit_data['siswa_id'] === (int)$s['id'] ? 'selected' : '' ?>>
                                                <?= aman($s['nisn']) ?> - <?= aman($s['nama']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Kelas</label>
                                    <select name="kelas_id" class="form-select" required>
                                        <option value="">-- Pilih Kelas --</option>
                                        <?php foreach ($kelas_list as $k): ?>
                                            <option value="<?= (int)$k['id'] ?>"
                                                <?= (int)$edit_data['kelas_id'] === (int)$k['id'] ? 'selected' : '' ?>>
                                                <?= aman($k['nama']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Tahun Ajaran</label>
                                    <select name="tahun_ajaran_id" class="form-select" required>
                                        <option value="">-- Pilih Tahun Ajaran --</option>
                                        <?php foreach ($tahun_list as $t): ?>
                                            <option value="<?= (int)$t['id'] ?>"
                                                <?= (int)$edit_data['tahun_ajaran_id'] === (int)$t['id'] ? 'selected' : '' ?>>
                                                <?= aman($t['nama']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Status</label>
                                    <select name="status_aktif" class="form-select" required>
                                        <option value="1" <?= (int)$edit_data['status_aktif'] === 1 ? 'selected' : '' ?>>Aktif</option>
                                        <option value="0" <?= (int)$edit_data['status_aktif'] === 0 ? 'selected' : '' ?>>Tidak Aktif</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Tanggal Mulai</label>
                                    <input type="date" name="tanggal_mulai" class="form-control"
                                           value="<?= aman($edit_data['tanggal_mulai']) ?>" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Tanggal Selesai</label>
                                    <input type="date" name="tanggal_selesai" class="form-control"
                                           value="<?= aman($edit_data['tanggal_selesai']) ?>" required>
                                </div>
                            </div>

                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save me-1"></i>Simpan Perubahan
                                </button>
                                <a href="penempatan_siswa.php" class="btn btn-secondary">Batal</a>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>

            <!-- TABEL DATA -->
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
                                            <a href="penempatan_siswa.php?edit=<?= (int)$row['id'] ?>"
                                               class="btn btn-warning btn-sm mb-1">
                                                <i class="bi bi-pencil-square"></i> Edit
                                            </a>

                                            <a href="penempatan_siswa.php?hapus=<?= (int)$row['id'] ?>"
                                               class="btn btn-danger btn-sm mb-1"
                                               onclick="return confirm('Yakin ingin menghapus data penempatan ini?')">
                                                <i class="bi bi-trash"></i> Hapus
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

<!-- JAVASCRIPT -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function () {
    $('#tabelPenempatan').DataTable({
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
        pageLength: 10,
        lengthMenu: [5, 10, 25, 50],
        ordering: true,
        searching: true,
        columnDefs: [
            {
                targets: [0, 8],
                orderable: false,
                searchable: false
            }
        ]
    });
});
</script>

</body>
</html>
