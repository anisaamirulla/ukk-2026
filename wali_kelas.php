
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

    if ($id <= 0) {
        header("Location: wali_kelas.php?pesan=gagal_hapus");
        exit;
    }

    $stmt = mysqli_prepare(
        $koneksi,
        "DELETE FROM t_wali_kelas WHERE id = ?"
    );

    if (!$stmt) {
        header("Location: wali_kelas.php?pesan=gagal_hapus");
        exit;
    }

    mysqli_stmt_bind_param($stmt, "i", $id);

    if (mysqli_stmt_execute($stmt)) {
        $berhasil = mysqli_stmt_affected_rows($stmt) > 0;
    } else {
        $berhasil = false;
    }

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

    <title>Data Wali Kelas - Sistem Informasi Pelanggaran Siswa</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <link href="https://cdn.datatables.net/2.3.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
</head>

<body class="bg-light overflow-hidden">

<div class="container-fluid vh-100 overflow-hidden">
    <div class="row h-100 flex-nowrap">

        <!-- SIDEBAR -->
        <aside class="col-3 col-md-3 col-lg-2 bg-dark vh-100 overflow-auto p-3 flex-shrink-0">

            <div class="text-white mb-4 pt-3 pb-3 border-bottom border-secondary">

                <h5 class="fw-bold mb-2">
                    <i class="bi bi-shield-check me-2"></i>
                    Sistem Pelanggaran
                </h5>

                <small class="text-secondary">Panel Admin</small>

            </div>

            <!-- PROFIL ADMIN -->
            <div class="bg-secondary bg-opacity-25 rounded-3 p-3 mb-4">

                <div class="d-flex align-items-center gap-3">

                    <img
                        src="logo.png"
                        alt="Logo Aplikasi"
                        width="44"
                        height="44"
                        class="rounded-circle bg-white p-1"
                    >

                    <div class="text-white text-break">

                        <div class="fw-semibold">
                            <?= aman($_SESSION['name'] ?? 'Pengguna'); ?>
                        </div>

                        <small class="text-secondary">ADMIN</small>

                    </div>

                </div>

            </div>

            <div class="text-secondary small fw-semibold mb-3">
                MENU UTAMA
            </div>

            <ul class="nav nav-pills flex-column gap-2">

                <li class="nav-item">
                    <a href="dashboard.php" class="nav-link text-white rounded-2 py-2 px-3">
                        Dashboard
                    </a>
                </li>

                <li class="nav-item">
                    <a href="siswa.php" class="nav-link text-white rounded-2 py-2 px-3">
                        Data Siswa
                    </a>
                </li>

                <li class="nav-item">
                    <a href="guru.php" class="nav-link text-white rounded-2 py-2 px-3">
                        Data Guru
                    </a>
                </li>

                <li class="nav-item">
                    <a href="kelas.php" class="nav-link text-white rounded-2 py-2 px-3">
                        Kelas
                    </a>
                </li>

                <li class="nav-item">
                    <a href="tahun_ajaran.php" class="nav-link text-white rounded-2 py-2 px-3">
                        Tahun Ajaran
                    </a>
                </li>

                <li class="nav-item">
                    <a href="penempatan_siswa.php" class="nav-link text-white rounded-2 py-2 px-3">
                        Penempatan Siswa
                    </a>
                </li>

                <li class="nav-item">
                    <a href="wali_kelas.php" class="nav-link active rounded-2 py-2 px-3" aria-current="page">
                        Wali Kelas
                    </a>
                </li>

                <li class="nav-item">
                    <a href="kategori_pelanggaran.php" class="nav-link text-white rounded-2 py-2 px-3">
                        Kategori Pelanggaran
                    </a>
                </li>

                <li class="nav-item">
                    <a href="jenis_pelanggaran.php" class="nav-link text-white rounded-2 py-2 px-3">
                        Jenis Pelanggaran
                    </a>
                </li>

                <li class="nav-item">
                    <a href="laporan.php" class="nav-link text-white rounded-2 py-2 px-3">
                        Laporan
                    </a>
                </li>

                <li class="nav-item">
                    <a href="abautme.php" class="nav-link text-white rounded-2 py-2 px-3">
                        About Me
                    </a>
                </li>

            </ul>

            <hr class="border-secondary my-4">

            <a href="logout.php" class="nav-link text-danger rounded-2 py-2 px-3">
                <i class="bi bi-box-arrow-left me-2"></i>
                Logout
            </a>

        </aside>

        <!-- KONTEN DATA WALI KELAS -->
        <main class="col-9 col-md-9 col-lg-10 vh-100 overflow-auto p-3 p-lg-5">

            <!-- JUDUL HALAMAN -->
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-4 border-bottom">

                <div>

                    <h2 class="fw-bold mb-2">
                        Data Wali Kelas
                    </h2>

                    <p class="text-secondary mb-3">
                        Kelola data wali kelas pada Sistem Informasi Pelanggaran Siswa.
                    </p>

                    <!-- TOMBOL KEMBALI KE DASHBOARD -->
                    <a href="dashboard.php" class="btn btn-secondary btn-sm">
                        Kembali ke Dashboard
                    </a>

                </div>

                <!-- TOMBOL TAMBAH -->
                <a href="tambah_wali_kelas.php" class="btn btn-primary rounded-2 px-4">
                    + Tambah Wali Kelas
                </a>

            </div>

            <!-- NOTIFIKASI BERHASIL -->
            <?php if (isset($pesan_sukses[$pesan_url])): ?>

                <div class="alert alert-success alert-dismissible fade show" role="alert">

                    <i class="bi bi-check-circle-fill me-2"></i>

                    <?= aman($pesan_sukses[$pesan_url]); ?>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"
                        aria-label="Tutup">
                    </button>

                </div>

            <?php endif; ?>

            <!-- NOTIFIKASI GAGAL -->
            <?php if (isset($pesan_gagal[$pesan_url])): ?>

                <div class="alert alert-danger alert-dismissible fade show" role="alert">

                    <i class="bi bi-exclamation-triangle-fill me-2"></i>

                    <?= aman($pesan_gagal[$pesan_url]); ?>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"
                        aria-label="Tutup">
                    </button>

                </div>

            <?php endif; ?>

            <!-- TABEL WALI KELAS -->
            <div class="card border-0 shadow-sm rounded-3">

                <div class="card-body p-3 p-lg-4">

                    <div class="table-responsive">

                        <table id="tabelWaliKelas" class="table table-hover align-middle">

                            <thead>

                                <tr class="table-primary">
                                    <th>No</th>
                                    <th>Tahun Ajaran</th>
                                    <th>Kelas</th>
                                    <th>NIP</th>
                                    <th>Nama Guru</th>
                                    <th>Tanggal Mulai</th>
                                    <th>Tanggal Selesai</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>

                            </thead>

                            <tbody>

                                <?php
                                $no = 1;

                                if (mysqli_num_rows($data) > 0):
                                    while ($row = mysqli_fetch_assoc($data)):
                                ?>

                                    <tr>

                                        <td>
                                            <?= $no++; ?>
                                        </td>

                                        <td>
                                            <?= aman($row['tahun_ajaran'] ?? '-'); ?>
                                        </td>

                                        <td>
                                            <?= aman($row['nama_kelas'] ?? '-'); ?>
                                        </td>

                                        <td>
                                            <?= aman($row['nip'] ?? '-'); ?>
                                        </td>

                                        <td>
                                            <strong>
                                                <?= aman($row['nama_guru'] ?? '-'); ?>
                                            </strong>
                                        </td>

                                        <td>
                                            <?= aman($row['tanggal_mulai'] ?? '-'); ?>
                                        </td>

                                        <td>
                                            <?= aman($row['tanggal_selesai'] ?? '-'); ?>
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

                                        <!-- TOMBOL AKSI -->
                                        <td>

                                            <div class="d-flex gap-1">

                                                <a
                                                    href="tambah_wali_kelas.php?edit=<?= (int)$row['id']; ?>"
                                                    class="btn btn-warning btn-sm"
                                                >
                                                    Edit
                                                </a>

                                                <a
                                                    href="wali_kelas.php?hapus=<?= (int)$row['id']; ?>"
                                                    class="btn btn-danger btn-sm"
                                                    onclick="return confirm('Yakin ingin menghapus data wali kelas ini?')"
                                                >
                                                    Hapus
                                                </a>

                                            </div>

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

            <!-- FOOTER -->
            <div class="text-secondary small mt-5 pt-3 border-top">
                Sistem Informasi Pelanggaran Siswa
            </div>

        </main>

    </div>
</div>

<!-- JAVASCRIPT BOOTSTRAP -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- JQUERY -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- DATATABLES -->
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.min.js"></script>

<script src="https://cdn.datatables.net/2.3.4/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function () {

    if ($('#tabelWaliKelas').length) {

        $('#tabelWaliKelas').DataTable({

            pageLength: 10,

            lengthMenu: [5, 10, 25, 50, 100],

            ordering: true,

            searching: true,

            language: {
                search: "Cari:",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                infoEmpty: "Tidak ada data",
                infoFiltered: "(difilter dari _MAX_ total data)",
                zeroRecords: "Data tidak ditemukan",
                emptyTable: "Belum ada data wali kelas",

                paginate: {
                    first: "Pertama",
                    last: "Terakhir",
                    next: "Berikutnya",
                    previous: "Sebelumnya"
                }
            },

            columnDefs: [
                {
                    targets: [0, 8],
                    orderable: false,
                    searchable: false
                }
            ]

        });

    }

});
</script>

</body>
</html>
