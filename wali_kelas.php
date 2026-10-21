
<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_SESSION['role']) || strtolower(trim($_SESSION['role'])) !== 'admin') {
    echo "Anda tidak memiliki akses";
    exit;
}

require_once __DIR__ . "/config/koneksi.php";

/* FUNGSI AMAN */
function aman($nilai) {
    return htmlspecialchars((string)($nilai ?? ''), ENT_QUOTES, 'UTF-8');
}

/* HAPUS DATA */
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];

    $stmt = mysqli_prepare(
        $koneksi,
        "DELETE FROM t_wali_kelas WHERE id = ?"
    );

    if (!$stmt) {
        die("Gagal menyiapkan penghapusan: " . mysqli_error($koneksi));
    }

    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);

    $berhasil = mysqli_stmt_affected_rows($stmt) > 0;

    mysqli_stmt_close($stmt);

    if ($berhasil) {
        header("Location: wali_kelas.php?pesan=hapus");
    } else {
        header("Location: wali_kelas.php?pesan=gagal_hapus");
    }

    exit;
}

/* AMBIL DATA WALI KELAS */
$data = mysqli_query($koneksi, "
    SELECT
        wk.id,
        ta.nama AS tahun_ajaran,
        k.nama AS nama_kelas,
        g.nip,
        g.nama AS nama_guru,
        wk.tanggal_mulai,
        wk.tanggal_selesai,
        wk.status_aktif
    FROM t_wali_kelas wk
    LEFT JOIN t_tahun_ajaran ta ON wk.tahun_ajaran_id = ta.id
    LEFT JOIN t_kelas k ON wk.kelas_id = k.id
    LEFT JOIN t_guru g ON wk.guru_id = g.id
    ORDER BY wk.id DESC
");

if (!$data) {
    die("Gagal mengambil data wali kelas: " . mysqli_error($koneksi));
}

/* PESAN PEMBERITAHUAN */
$pesan_url = $_GET['pesan'] ?? '';

$pesan_sukses = [
    'berhasil' => 'Data wali kelas berhasil disimpan.',
    'edit' => 'Data wali kelas berhasil diperbarui.',
    'hapus' => 'Data wali kelas berhasil dihapus.'
];

$pesan_gagal = [
    'gagal_hapus' => 'Data wali kelas gagal dihapus atau tidak ditemukan.'
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Data Wali Kelas</title>

    <!-- BOOTSTRAP 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- BOOTSTRAP ICONS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- DATATABLES BOOTSTRAP 5 -->
    <link href="https://cdn.datatables.net/2.1.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">
</head>

<body>

<div class="container-fluid">
    <div class="row">

        <!-- SIDEBAR -->
        <aside class="col-12 col-md-3 col-lg-2 bg-dark text-white min-vh-100 p-3">

            <div class="mb-4">
                <h5 class="mb-1">
                    <i class="bi bi-shield-check me-2"></i>
                    Sistem Pelanggaran
                </h5>
                <small class="text-secondary">Panel Admin</small>
            </div>

            <div class="d-flex align-items-center bg-secondary bg-opacity-25 rounded p-3 mb-4">
                <i class="bi bi-person-circle fs-3 me-2"></i>

                <div>
                    <small class="text-secondary d-block">Login sebagai</small>
                    <?= aman($_SESSION['name'] ?? 'Admin') ?>
                </div>
            </div>

            <p class="text-secondary small fw-bold">MENU UTAMA</p>

            <ul class="nav nav-pills flex-column gap-1">

                <li class="nav-item">
                    <a href="dashboard.php" class="nav-link text-white">
                        Dashboard
                    </a>
                </li>

                <li class="nav-item">
                    <a href="siswa.php" class="nav-link text-white">
                        Data Siswa
                    </a>
                </li>

                <li class="nav-item">
                    <a href="guru.php" class="nav-link text-white">
                        Data Guru
                    </a>
                </li>

                <li class="nav-item">
                    <a href="kelas.php" class="nav-link text-white">
                        Kelas
                    </a>
                </li>

                <li class="nav-item">
                    <a href="tahun_ajaran.php" class="nav-link text-white">
                        Tahun Ajaran
                    </a>
                </li>

                <li class="nav-item">
                    <a href="penempatan_siswa.php" class="nav-link text-white">
                        Penempatan Siswa
                    </a>
                </li>

                <li class="nav-item">
                    <a href="wali_kelas.php" class="nav-link active" aria-current="page">
                        Wali Kelas
                    </a>
                </li>

                <li class="nav-item">
                    <a href="kategori_pelanggaran.php" class="nav-link text-white">
                        Kategori Pelanggaran
                    </a>
                </li>

                <li class="nav-item">
                    <a href="jenis_pelanggaran.php" class="nav-link text-white">
                        Jenis Pelanggaran
                    </a>
                </li>

                <li class="nav-item">
                    <a href="laporan.php" class="nav-link text-white">
                        Laporan
                    </a>
                </li>

                <li class="nav-item">
                    <a href="abautme.php" class="nav-link text-white">
                        About Me
                    </a>
                </li>

            </ul>

            <hr>

            <a href="logout.php" class="nav-link text-danger">
                <i class="bi bi-box-arrow-left me-2"></i>
                Logout
            </a>

        </aside>

        <!-- KONTEN UTAMA -->
        <main class="col-12 col-md-9 col-lg-10 p-3 p-lg-4">

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">

                <div>
                    <h2 class="fw-bold mb-1">Data Wali Kelas</h2>

                    <p class="text-secondary mb-0">
                        Daftar data wali kelas sekolah
                    </p>
                </div>

                <a href="tambah_wali_kelas.php" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i>
                    Tambah Wali Kelas
                </a>

            </div>

            <!-- NOTIFIKASI BERHASIL -->
            <?php if (isset($pesan_sukses[$pesan_url])): ?>

                <div class="alert alert-success alert-dismissible fade show" role="alert">

                    <i class="bi bi-check-circle-fill me-2"></i>
                    <?= aman($pesan_sukses[$pesan_url]) ?>

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="alert"
                            aria-label="Tutup"></button>

                </div>

            <?php endif; ?>

            <!-- NOTIFIKASI GAGAL -->
            <?php if (isset($pesan_gagal[$pesan_url])): ?>

                <div class="alert alert-danger alert-dismissible fade show" role="alert">

                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <?= aman($pesan_gagal[$pesan_url]) ?>

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="alert"
                            aria-label="Tutup"></button>

                </div>

            <?php endif; ?>

            <!-- TABEL WALI KELAS -->
            <div class="card border-0 shadow-sm">

                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Data Wali Kelas</h5>
                </div>

                <div class="card-body">

                    <div class="table-responsive">

                        <table id="tabelWaliKelas"
                               class="table table-bordered table-hover align-middle w-100">

                            <thead>
                                <tr>
                                    <th class="table-primary">No</th>
                                    <th class="table-primary">Tahun Ajaran</th>
                                    <th class="table-primary">Kelas</th>
                                    <th class="table-primary">NIP</th>
                                    <th class="table-primary">Nama Guru</th>
                                    <th class="table-primary">Tanggal Mulai</th>
                                    <th class="table-primary">Tanggal Selesai</th>
                                    <th class="table-primary">Status</th>
                                    <th class="table-primary">Aksi</th>
                                </tr>
                            </thead>

                            <tbody>

                                <?php
                                $no = 1;

                                if (mysqli_num_rows($data) > 0):
                                    while ($row = mysqli_fetch_assoc($data)):
                                ?>

                                    <tr>
                                        <td><?= $no++ ?></td>

                                        <td>
                                            <?= aman($row['tahun_ajaran'] ?? '-') ?>
                                        </td>

                                        <td>
                                            <?= aman($row['nama_kelas'] ?? '-') ?>
                                        </td>

                                        <td>
                                            <?= aman($row['nip'] ?? '-') ?>
                                        </td>

                                        <td>
                                            <strong>
                                                <?= aman($row['nama_guru'] ?? '-') ?>
                                            </strong>
                                        </td>

                                        <td>
                                            <?= aman($row['tanggal_mulai'] ?? '-') ?>
                                        </td>

                                        <td>
                                            <?= aman($row['tanggal_selesai'] ?? '-') ?>
                                        </td>

                                        <td>
                                            <?php if ((int)$row['status_aktif'] === 1): ?>

                                                <span class="badge bg-success">
                                                    Aktif
                                                </span>

                                            <?php else: ?>

                                                <span class="badge bg-secondary">
                                                    Tidak Aktif
                                                </span>

                                            <?php endif; ?>
                                        </td>

                                        <!-- TOMBOL EDIT DAN HAPUS -->
                                        <td class="text-nowrap">

                                            <a href="tambah_wali_kelas.php?edit=<?= (int)$row['id'] ?>"
                                               class="btn btn-warning btn-sm">

                                                <i class="bi bi-pencil-square"></i>
                                                Edit
                                            </a>

                                            <a href="wali_kelas.php?hapus=<?= (int)$row['id'] ?>"
                                               class="btn btn-danger btn-sm"
                                               onclick="return confirm('Yakin ingin menghapus data wali kelas ini?')">

                                                <i class="bi bi-trash"></i>
                                                Hapus
                                            </a>

                                        </td>
                                    </tr>

                                <?php
                                    endwhile;
                                endif;
                                ?>

                            </tbody>

                        </table>

                    </div>
                </div>
            </div>

        </main>
    </div>
</div>

<!-- JAVASCRIPT BOOTSTRAP -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- JQUERY -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- DATATABLES -->
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function () {

    $('#tabelWaliKelas').DataTable({

        language: {
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            infoEmpty: "Tidak ada data yang ditampilkan",
            infoFiltered: "(difilter dari _MAX_ total data)",
            zeroRecords: "Data tidak ditemukan",
            emptyTable: "Belum ada data wali kelas.",

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