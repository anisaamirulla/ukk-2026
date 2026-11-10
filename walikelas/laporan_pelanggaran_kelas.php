
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

// Cari guru berdasarkan akun login
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
$nama = $guru['nama'];

/* =================================
   RINGKASAN PELANGGARAN PER SISWA
================================= */

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT
        nama_siswa,
        COUNT(*) AS jumlah_pelanggaran,
        SUM(poin) AS total_poin
     FROM t_pelanggaran_siswa
     WHERE kelas_id = ?
       AND tahun_ajaran_id = ?
     GROUP BY nama_siswa
     ORDER BY total_poin DESC"
);

mysqli_stmt_bind_param($stmt, "ii", $kelas_id, $tahun_ajaran_id);
mysqli_stmt_execute($stmt);
$resultSiswa = mysqli_stmt_get_result($stmt);

$dataSiswa = [];

while ($row = mysqli_fetch_assoc($resultSiswa)) {
    $dataSiswa[] = $row;
}

mysqli_stmt_close($stmt);

/* =================================
   RINGKASAN JENIS PELANGGARAN
================================= */

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT
        nama_pelanggaran,
        COUNT(*) AS jumlah,
        SUM(poin) AS total_poin
     FROM t_pelanggaran_siswa
     WHERE kelas_id = ?
       AND tahun_ajaran_id = ?
     GROUP BY nama_pelanggaran
     ORDER BY jumlah DESC"
);

mysqli_stmt_bind_param($stmt, "ii", $kelas_id, $tahun_ajaran_id);
mysqli_stmt_execute($stmt);
$resultJenis = mysqli_stmt_get_result($stmt);

$dataJenis = [];

while ($row = mysqli_fetch_assoc($resultJenis)) {
    $dataJenis[] = $row;
}

mysqli_stmt_close($stmt);

/* =================================
   TOTAL KESELURUHAN
================================= */

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT
        COUNT(*) AS jumlah_kejadian,
        COALESCE(SUM(poin), 0) AS total_poin,
        COUNT(DISTINCT nama_siswa) AS jumlah_siswa
     FROM t_pelanggaran_siswa
     WHERE kelas_id = ?
       AND tahun_ajaran_id = ?"
);

mysqli_stmt_bind_param($stmt, "ii", $kelas_id, $tahun_ajaran_id);
mysqli_stmt_execute($stmt);
$ringkasan = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

$jumlahSiswa = (int) $ringkasan['jumlah_siswa'];
$jumlahKejadian = (int) $ringkasan['jumlah_kejadian'];
$totalPoin = (int) $ringkasan['total_poin'];

$jenisTerbanyak = !empty($dataJenis)
    ? $dataJenis[0]['nama_pelanggaran']
    : '-';

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Laporan Pelanggaran Kelas - Sistem Informasi Pelanggaran Siswa</title>

    <!-- BOOTSTRAP -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- BOOTSTRAP ICONS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- DATATABLES -->
    <link href="https://cdn.datatables.net/2.3.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">

</head>

<body class="bg-light overflow-hidden">

<div class="container-fluid vh-100 overflow-hidden">

    <div class="row h-100 flex-nowrap">

        <!-- SIDEBAR -->
        <aside class="col-3 col-md-3 col-lg-2 bg-dark vh-100 overflow-y-auto p-3 flex-shrink-0">

            <!-- IDENTITAS APLIKASI -->
            <div class="text-white mb-4 pt-3 pb-3 border-bottom border-secondary">

                <h5 class="fw-bold mb-2">
                    <i class="bi bi-shield-check me-2"></i>
                    Sistem Pelanggaran
                </h5>

                <small class="text-secondary">
                    Panel Wali Kelas
                </small>

            </div>

            <!-- IDENTITAS PENGGUNA -->
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

            <!-- MENU UTAMA -->
            <div class="text-secondary small fw-semibold mb-3">
                MENU UTAMA
            </div>

            <ul class="nav nav-pills flex-column gap-2">

                <li class="nav-item">

                    <a href="../dashboard.php"
                       class="nav-link text-white rounded-2 py-2 px-3">
                        Dashboard
                    </a>

                </li>

                <li class="nav-item">

                    <a href="data_siswa.php"
                       class="nav-link text-white rounded-2 py-2 px-3">
                        Data Siswa di Kelasnya
                    </a>

                </li>

                <li class="nav-item">

                    <a href="../catatan_pelanggaran.php"
                       class="nav-link text-white rounded-2 py-2 px-3">
                        Catat Pelanggaran
                    </a>

                </li>

                <li class="nav-item">

                    <a href="../riwayat.php"
                       class="nav-link text-white rounded-2 py-2 px-3">
                        Riwayat Pelanggaran Siswa
                    </a>

                </li>

                <li class="nav-item">

                    <a href="../rekap_poin.php"
                       class="nav-link text-white rounded-2 py-2 px-3">
                        Rekap Poin Siswa
                    </a>

                </li>

                <li class="nav-item">

                    <a href="laporan_pelanggaran_kelas.php"
                       class="nav-link active rounded-2 py-2 px-3"
                       aria-current="page">
                        Laporan Pelanggaran Kelas
                    </a>

                </li>

                <li class="nav-item">

                    <a href="../abautme.php"
                       class="nav-link text-white rounded-2 py-2 px-3">
                        About Me
                    </a>

                </li>

            </ul>

            <hr class="border-secondary my-4">

            <!-- LOGOUT -->
            <a href="../logout.php"
               class="nav-link text-danger rounded-2 py-2 px-3">

                <i class="bi bi-box-arrow-left me-2"></i>
                Logout

            </a>

        </aside>

        <!-- KONTEN UTAMA -->
        <main class="col-9 col-md-9 col-lg-10 vh-100 overflow-y-auto p-3 p-lg-5">

            <!-- JUDUL HALAMAN -->
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4 pb-4 border-bottom">

                <div>

                    <h2 class="fw-bold mb-2">
                        Laporan Pelanggaran Kelas
                    </h2>

                    <p class="text-secondary mb-2">
                        Ringkasan dan evaluasi pelanggaran siswa kelas
                        <?= aman($wali['nama_kelas']); ?>.
                    </p>

                    <a href="../dashboard.php"
                       class="btn btn-secondary btn-sm rounded-2">

                        <i class="bi bi-arrow-left me-1"></i>
                        Kembali ke Dashboard

                    </a>

                </div>

                <span class="badge text-bg-dark rounded-pill px-3 py-2">
                    Wali Kelas
                </span>

            </div>

            <!-- INFORMASI KELAS -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">

                <div class="card-body p-4">

                    <div class="row g-3">

                        <div class="col-md-6">

                            <small class="text-secondary">
                                Nama Kelas
                            </small>

                            <h5 class="fw-bold mb-0">
                                <?= aman($wali['nama_kelas']); ?>
                            </h5>

                        </div>

                        <div class="col-md-6">

                            <small class="text-secondary">
                                Wali Kelas
                            </small>

                            <h5 class="fw-bold mb-0">
                                <?= aman($wali['nama_wali_kelas']); ?>
                            </h5>

                        </div>

                    </div>

                </div>

            </div>

            <!-- KARTU RINGKASAN -->
            <div class="row g-3 mb-4">

                <div class="col-12 col-md-4">

                    <div class="card border-0 shadow-sm rounded-3 h-100">

                        <div class="card-body p-4">

                            <div class="text-secondary small mb-2">
                                Jumlah Siswa Melanggar
                            </div>

                            <h3 class="fw-bold text-primary mb-0">
                                <?= $jumlahSiswa; ?> Siswa
                            </h3>

                        </div>

                    </div>

                </div>

                <div class="col-12 col-md-4">

                    <div class="card border-0 shadow-sm rounded-3 h-100">

                        <div class="card-body p-4">

                            <div class="text-secondary small mb-2">
                                Jumlah Kejadian Pelanggaran
                            </div>

                            <h3 class="fw-bold text-warning mb-0">
                                <?= $jumlahKejadian; ?> Kejadian
                            </h3>

                        </div>

                    </div>

                </div>

                <div class="col-12 col-md-4">

                    <div class="card border-0 shadow-sm rounded-3 h-100">

                        <div class="card-body p-4">

                            <div class="text-secondary small mb-2">
                                Total Poin Pelanggaran
                            </div>

                            <h3 class="fw-bold text-danger mb-0">
                                <?= $totalPoin; ?> Poin
                            </h3>

                        </div>

                    </div>

                </div>

            </div>

            <!-- RINGKASAN JENIS PELANGGARAN -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">

                <div class="card-body p-3 p-lg-4">

                    <h5 class="fw-bold mb-3">
                        Ringkasan Jenis Pelanggaran
                    </h5>

                    <div class="table-responsive">

                        <table class="table table-hover align-middle">

                            <thead>

                                <tr class="table-primary">
                                    <th>No</th>
                                    <th>Jenis Pelanggaran</th>
                                    <th>Jumlah Kejadian</th>
                                    <th>Total Poin</th>
                                </tr>

                            </thead>

                            <tbody>

                                <?php if (empty($dataJenis)): ?>

                                    <tr>
                                        <td colspan="4" class="text-center text-secondary py-4">
                                            Belum ada data pelanggaran.
                                        </td>
                                    </tr>

                                <?php else: ?>

                                    <?php $no = 1; ?>

                                    <?php foreach ($dataJenis as $row): ?>

                                        <tr>

                                            <td>
                                                <?= $no++; ?>
                                            </td>

                                            <td class="fw-semibold">
                                                <?= aman($row['nama_pelanggaran']); ?>
                                            </td>

                                            <td>
                                                <?= (int) $row['jumlah']; ?> Kejadian
                                            </td>

                                            <td>
                                                <?= (int) $row['total_poin']; ?> Poin
                                            </td>

                                        </tr>

                                    <?php endforeach; ?>

                                <?php endif; ?>

                            </tbody>

                        </table>

                    </div>

                    <div class="mt-3">

                        <small class="text-secondary">
                            Jenis pelanggaran yang paling sering terjadi:
                        </small>

                        <div class="fw-bold">
                            <?= aman($jenisTerbanyak); ?>
                        </div>

                    </div>

                </div>

            </div>

            <!-- RINGKASAN PER SISWA -->
            <div class="card border-0 shadow-sm rounded-3">

                <div class="card-body p-3 p-lg-4">

                    <h5 class="fw-bold mb-3">
                        Ringkasan Pelanggaran Per Siswa
                    </h5>

                    <div class="table-responsive">

                        <table id="tabelLaporan" class="table table-hover align-middle">

                            <thead>

                                <tr class="table-primary">
                                    <th>No</th>
                                    <th>Nama Siswa</th>
                                    <th>Jumlah Pelanggaran</th>
                                    <th>Total Poin</th>
                                </tr>

                            </thead>

                            <tbody>

                                <?php if (empty($dataSiswa)): ?>

                                    <tr>
                                        <td colspan="4" class="text-center text-secondary py-4">
                                            Belum ada data pelanggaran.
                                        </td>
                                    </tr>

                                <?php else: ?>

                                    <?php $no = 1; ?>

                                    <?php foreach ($dataSiswa as $row): ?>

                                        <tr>

                                            <td>
                                                <?= $no++; ?>
                                            </td>

                                            <td class="fw-semibold">
                                                <?= aman($row['nama_siswa']); ?>
                                            </td>

                                            <td>
                                                <?= (int) $row['jumlah_pelanggaran']; ?> Kali
                                            </td>

                                            <td>
                                                <span class="badge bg-danger">
                                                    <?= (int) $row['total_poin']; ?> Poin
                                                </span>
                                            </td>

                                        </tr>

                                    <?php endforeach; ?>

                                <?php endif; ?>

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

<!-- JAVASCRIPT -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script src="https://cdn.datatables.net/2.3.4/js/dataTables.min.js"></script>

<script src="https://cdn.datatables.net/2.3.4/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function () {

    $('#tabelLaporan').DataTable({

        pageLength: 10,

        lengthMenu: [5, 10, 25, 50, 100],

        ordering: true,

        searching: true,

        language: {

            search: "Cari:",

            lengthMenu: "Tampilkan _MENU_ data",

            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",

            infoEmpty: "Tidak ada data",

            infoFiltered: "(disaring dari _MAX_ total data)",

            zeroRecords: "Data tidak ditemukan",

            emptyTable: "Belum ada data",

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

</body>
</html>
