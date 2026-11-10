
<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: ../login.php");
    exit;
}

if (strtolower(trim($_SESSION['role'] ?? '')) !== 'guru') {
    exit("Anda tidak memiliki akses");
}

require_once __DIR__ . '/../config/koneksi.php';

function aman($data) {
    return htmlspecialchars((string) $data, ENT_QUOTES, 'UTF-8');
}

// Cari guru
$stmt = mysqli_prepare(
    $koneksi,
    "SELECT id, nama FROM t_guru WHERE user_id = ? LIMIT 1"
);
mysqli_stmt_bind_param($stmt, "i", $_SESSION['id']);
mysqli_stmt_execute($stmt);
$guru = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$guru) {
    exit("Data guru tidak ditemukan.");
}

// Cari penugasan Wali Kelas
$stmt = mysqli_prepare(
    $koneksi,
    "SELECT wk.kelas_id, wk.tahun_ajaran_id,
            k.nama AS nama_kelas,
            g.nama AS nama_wali_kelas
     FROM t_wali_kelas wk
     JOIN t_kelas k ON k.id = wk.kelas_id
     JOIN t_guru g ON g.id = wk.guru_id
     WHERE wk.guru_id = ?
       AND wk.status_aktif = 1
       AND wk.tanggal_mulai <= CURDATE()
       AND (wk.tanggal_selesai IS NULL OR wk.tanggal_selesai >= CURDATE())
     LIMIT 1"
);
mysqli_stmt_bind_param($stmt, "i", $guru['id']);
mysqli_stmt_execute($stmt);
$wali = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$wali) {
    exit("Anda belum memiliki penugasan Wali Kelas yang aktif.");
}

$kelas_id = (int) $wali['kelas_id'];
$tahun_ajaran_id = (int) $wali['tahun_ajaran_id'];

// Ambil rekap pelanggaran
$stmt = mysqli_prepare(
    $koneksi,
    "SELECT
        ps.nama_siswa,
        ps.nama_pelanggaran,
        pk.nama AS nama_kategori,
        ps.nama_guru,
        ps.tanggal,
        ps.keterangan,
        ps.poin,
        ps.tindakan,
        ps.status
     FROM t_pelanggaran_siswa ps
     LEFT JOIN t_pelanggaran_kategori pk
        ON pk.id = ps.pelanggaran_kategori_id
     WHERE ps.kelas_id = ?
       AND ps.tahun_ajaran_id = ?
     ORDER BY ps.tanggal DESC, ps.id DESC"
);

mysqli_stmt_bind_param($stmt, "ii", $kelas_id, $tahun_ajaran_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$data = [];
$totalPoin = 0;

while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
    $totalPoin += (int) $row['poin'];
}

$jumlah = count($data);
$jumlahSiswa = count(array_unique(array_column($data, 'nama_siswa')));
$nama = $guru['nama'];

$halamanAktif = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Rekap Pelanggaran - Sistem Informasi Pelanggaran Siswa</title>

    <!-- BOOTSTRAP 5.3.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- BOOTSTRAP ICONS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- DATATABLES -->
    <link href="https://cdn.datatables.net/2.3.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">

</head>

<body class="bg-light overflow-hidden">

<div class="container-fluid vh-100 overflow-hidden">

    <div class="row h-100 flex-nowrap">

        <!-- SIDEBAR SAMA DENGAN DASHBOARD -->
        <aside class="col-12 col-md-3 col-lg-2 bg-dark vh-100 overflow-auto position-sticky top-0 p-3">

            <!-- JUDUL SIDEBAR -->
            <div class="text-white mb-4 pt-3 pb-3 border-bottom border-secondary">

                <h5 class="fw-bold mb-2">
                    <i class="bi bi-shield-check me-2"></i>
                    Sistem Pelanggaran
                </h5>

                <small class="text-secondary">
                    Panel Wali Kelas
                </small>

            </div>

            <!-- PROFIL PENGGUNA -->
            <div class="bg-secondary bg-opacity-25 rounded-3 p-3 mb-4">

                <div class="d-flex align-items-center gap-3">

                    <img
                        src="../logo.png"
                        alt="Logo Aplikasi"
                        width="44"
                        height="44"
                        class="rounded-circle bg-white p-1"
                    >

                    <div class="text-white text-break">

                        <div class="fw-semibold">
                            <?= aman($nama); ?>
                        </div>

                        <small class="text-secondary">
                            WALI KELAS
                        </small>

                    </div>

                </div>

            </div>

            <!-- MENU SIDEBAR -->
            <div class="text-secondary small fw-semibold mb-3">
                MENU UTAMA
            </div>

            <ul class="nav nav-pills flex-column gap-2">

                <li class="nav-item">
                    <a
                        href="../dashboard.php"
                        class="nav-link text-white rounded-2 py-2 px-3"
                    >
                        Dashboard
                    </a>
                </li>

                <li class="nav-item">
                    <a
                        href="data_siswa.php"
                        class="nav-link text-white rounded-2 py-2 px-3"
                    >
                        Data Siswa di Kelasnya
                    </a>
                </li>

                <li class="nav-item">
                    <a
                        href="../catatan_pelanggaran.php"
                        class="nav-link text-white rounded-2 py-2 px-3"
                    >
                        Catat Pelanggaran
                    </a>
                </li>

                <li class="nav-item">
                    <a
                        href="../riwayat.php"
                        class="nav-link text-white rounded-2 py-2 px-3"
                    >
                        Riwayat Pelanggaran Siswa
                    </a>
                </li>

                <li class="nav-item">
                    <a
                        href="../rekap_poin.php"
                        class="nav-link text-white rounded-2 py-2 px-3"
                    >
                        Rekap Poin Siswa
                    </a>
                </li>

                <li class="nav-item">
                    <a
                        href="rekap_pelanggaran.php"
                        class="nav-link active rounded-2 py-2 px-3"
                        aria-current="page"
                    >
                        Laporan Pelanggaran Kelas
                    </a>
                </li>

                <li class="nav-item">
                    <a
                        href="../abautme.php"
                        class="nav-link text-white rounded-2 py-2 px-3"
                    >
                        About Me
                    </a>
                </li>

            </ul>

            <hr class="border-secondary my-4">

            <!-- LOGOUT -->
            <a
                href="../logout.php"
                class="nav-link text-danger rounded-2 py-2 px-3"
            >
                <i class="bi bi-box-arrow-left me-2"></i>
                Logout
            </a>

        </aside>

        <!-- KONTEN UTAMA -->
        <main class="col-12 col-md-9 col-lg-10 vh-100 overflow-auto p-3 p-lg-5">

            <!-- JUDUL HALAMAN -->
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4 pb-4 border-bottom">

                <div>

                    <h2 class="fw-bold mb-2">
                        Rekap Pelanggaran
                    </h2>

                    <p class="text-secondary mb-2">
                        Rekap pelanggaran kelas <?= aman($wali['nama_kelas']); ?>.
                    </p>

                    <a
                        href="../dashboard.php"
                        class="btn btn-secondary btn-sm rounded-2"
                    >
                        <i class="bi bi-arrow-left me-1"></i>
                        Kembali ke Dashboard
                    </a>

                </div>

                <span class="badge text-bg-dark rounded-pill px-3 py-2">
                    Wali Kelas
                </span>

            </div>

            <!-- KARTU INFORMASI -->
            <div class="row g-3 mb-4">

                <div class="col-12 col-md-4">

                    <div class="card border-0 shadow-sm rounded-3 h-100">

                        <div class="card-body p-4">

                            <div class="text-secondary small mb-2">
                                <i class="bi bi-building me-1"></i>
                                Kelas
                            </div>

                            <h5 class="fw-bold mb-0">
                                <?= aman($wali['nama_kelas']); ?>
                            </h5>

                        </div>

                    </div>

                </div>

                <div class="col-12 col-md-4">

                    <div class="card border-0 shadow-sm rounded-3 h-100">

                        <div class="card-body p-4">

                            <div class="text-secondary small mb-2">
                                <i class="bi bi-people-fill me-1"></i>
                                Siswa Terlibat
                            </div>

                            <h4 class="fw-bold text-primary mb-0">
                                <?= $jumlahSiswa; ?> Siswa
                            </h4>

                        </div>

                    </div>

                </div>

                <div class="col-12 col-md-4">

                    <div class="card border-0 shadow-sm rounded-3 h-100">

                        <div class="card-body p-4">

                            <div class="text-secondary small mb-2">
                                <i class="bi bi-award-fill me-1"></i>
                                Total Poin
                            </div>

                            <h4 class="fw-bold text-danger mb-0">
                                <?= $totalPoin; ?> Poin
                            </h4>

                        </div>

                    </div>

                </div>

            </div>

            <!-- TABEL REKAP PELANGGARAN -->
            <div class="card border-0 shadow-sm rounded-3">

                <div class="card-body p-3 p-lg-4">

                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">

                        <h5 class="fw-bold mb-0">
                            <i class="bi bi-clipboard-data-fill me-2"></i>
                            Daftar Pelanggaran Siswa
                        </h5>

                    </div>

                    <div class="table-responsive">

                        <table id="tabelRekap" class="table table-hover align-middle">

                            <thead>

                                <tr class="table-primary">

                                    <th>No</th>
                                    <th>Nama Siswa</th>
                                    <th>Pelanggaran</th>
                                    <th>Kategori</th>
                                    <th>Tanggal</th>
                                    <th>Poin</th>
                                    <th>Guru Pencatat</th>
                                    <th>Keterangan</th>
                                    <th>Tindakan</th>
                                    <th>Status</th>

                                </tr>

                            </thead>

                            <tbody>

                                <?php $no = 1; ?>

                                <?php foreach ($data as $row): ?>

                                    <tr>

                                        <td>
                                            <?= $no++; ?>
                                        </td>

                                        <td class="fw-semibold">
                                            <?= aman($row['nama_siswa']); ?>
                                        </td>

                                        <td>
                                            <?= aman($row['nama_pelanggaran']); ?>
                                        </td>

                                        <td>
                                            <?= aman($row['nama_kategori'] ?? '-'); ?>
                                        </td>

                                        <td>
                                            <?= aman($row['tanggal']); ?>
                                        </td>

                                        <td>
                                            <span class="badge bg-danger">
                                                <?= (int) $row['poin']; ?>
                                            </span>
                                        </td>

                                        <td>
                                            <?= aman($row['nama_guru']); ?>
                                        </td>

                                        <td>
                                            <?= aman($row['keterangan']); ?>
                                        </td>

                                        <td>
                                            <?= aman($row['tindakan']); ?>
                                        </td>

                                        <td>
                                            <?= aman($row['status']); ?>
                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            </tbody>

                        </table>

                    </div>

                    <!-- TOMBOL CETAK -->
                    <div class="mt-3">

                        <a
                            href="cetak_rekap.php"
                            class="btn btn-primary rounded-2 px-4"
                        >
                            <i class="bi bi-printer me-2"></i>
                            Cetak Rekap
                        </a>

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

<!-- JAVASCRIPT -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script src="https://cdn.datatables.net/2.3.4/js/dataTables.min.js"></script>

<script src="https://cdn.datatables.net/2.3.4/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function () {

    $('#tabelRekap').DataTable({

        pageLength: 10,

        lengthMenu: [5, 10, 25, 50, 100],

        ordering: true,

        searching: true,

        language: {

            search: "Cari:",

            lengthMenu: "Tampilkan _MENU_ data",

            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data pelanggaran",

            infoEmpty: "Tidak ada data pelanggaran",

            infoFiltered: "(disaring dari _MAX_ total data)",

            zeroRecords: "Data pelanggaran tidak ditemukan",

            emptyTable: "Belum ada data pelanggaran",

            paginate: {

                first: "Pertama",

                last: "Terakhir",

                next: "Berikutnya",

                previous: "Sebelumnya"

            }

        },

        columnDefs: [
            {
                targets: 0,
                orderable: false
            }
        ]

    });

});
</script>

<?php mysqli_stmt_close($stmt); ?>

</body>
</html>
