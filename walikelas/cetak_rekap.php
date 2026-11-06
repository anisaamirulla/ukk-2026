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

// Ambil data laporan
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
     ORDER BY ps.tanggal ASC, ps.nama_siswa ASC"
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
$tanggalCetak = date('d-m-Y');
?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Cetak Rekap Pelanggaran</title>

    <!-- BOOTSTRAP 5.3.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- BOOTSTRAP ICONS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

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
                        Data Siswa
                    </a>

                </li>

                <li class="nav-item">

                    <a
                        href="rekap_pelanggaran.php"
                        class="nav-link text-white rounded-2 py-2 px-3"
                    >
                        Rekap Pelanggaran
                    </a>

                </li>

                <li class="nav-item">

                    <a
                        href="cetak_rekap.php"
                        class="nav-link active rounded-2 py-2 px-3"
                        aria-current="page"
                    >
                        Cetak Rekap
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
        <main class="col-9 col-md-9 col-lg-10 vh-100 overflow-y-auto p-3 p-lg-5">

            <!-- JUDUL HALAMAN -->
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4 pb-4 border-bottom">

                <div>

                    <h2 class="fw-bold mb-2">
                        Cetak Rekap Pelanggaran
                    </h2>

                    <p class="text-secondary mb-2">
                        Laporan pelanggaran kelas <?= aman($wali['nama_kelas']); ?>.
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

            <!-- TOMBOL CETAK -->
            <div class="d-flex flex-wrap gap-2 mb-4 bagian-tombol">

                <button
                    onclick="window.print()"
                    class="btn btn-primary rounded-2 px-4"
                >
                    <i class="bi bi-printer-fill me-2"></i>
                    Cetak Laporan
                </button>

                <a
                    href="rekap_pelanggaran.php"
                    class="btn btn-secondary rounded-2"
                >
                    <i class="bi bi-arrow-left me-2"></i>
                    Kembali
                </a>

            </div>

            <!-- KARTU LAPORAN -->
            <div class="card border-0 shadow-sm rounded-3">

                <div class="card-body p-3 p-lg-4">

                    <!-- JUDUL LAPORAN -->
                    <div class="text-center mb-4">

                        <h4 class="fw-bold">
                            LAPORAN REKAP PELANGGARAN SISWA
                        </h4>

                        <h5 class="fw-bold">
                            KELAS <?= aman($wali['nama_kelas']); ?>
                        </h5>

                        <p class="mb-0">
                            Sistem Informasi Pelanggaran Siswa
                        </p>

                    </div>

                    <hr>

                    <!-- INFORMASI LAPORAN -->
                    <div class="row g-3 mb-4">

                        <div class="col-md-6">

                            <div class="text-secondary small">
                                Nama Wali Kelas
                            </div>

                            <div class="fw-bold">
                                <?= aman($wali['nama_wali_kelas']); ?>
                            </div>

                        </div>

                        <div class="col-md-6">

                            <div class="text-secondary small">
                                Kelas
                            </div>

                            <div class="fw-bold">
                                <?= aman($wali['nama_kelas']); ?>
                            </div>

                        </div>

                        <div class="col-md-6">

                            <div class="text-secondary small">
                                Tanggal Cetak
                            </div>

                            <div class="fw-bold">
                                <?= aman($tanggalCetak); ?>
                            </div>

                        </div>

                        <div class="col-md-6">

                            <div class="text-secondary small">
                                Jumlah Catatan
                            </div>

                            <div class="fw-bold">
                                <?= $jumlah; ?> Catatan
                            </div>

                        </div>

                    </div>

                    <!-- RINGKASAN -->
                    <div class="row g-3 mb-4">

                        <div class="col-md-6">

                            <div class="border rounded-3 p-3">

                                <div class="text-secondary small">
                                    Siswa Terlibat
                                </div>

                                <h4 class="fw-bold mb-0">
                                    <?= $jumlahSiswa; ?> Siswa
                                </h4>

                            </div>

                        </div>

                        <div class="col-md-6">

                            <div class="border rounded-3 p-3">

                                <div class="text-secondary small">
                                    Total Poin
                                </div>

                                <h4 class="fw-bold text-danger mb-0">
                                    <?= $totalPoin; ?> Poin
                                </h4>

                            </div>

                        </div>

                    </div>

                    <!-- TABEL DATA PELANGGARAN -->
                    <div class="table-responsive">

                        <table class="table table-bordered table-striped align-middle">

                            <thead class="table-primary">

                                <tr>

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

                                <?php if ($jumlah > 0): ?>

                                    <?php $no = 1; ?>

                                    <?php foreach ($data as $row): ?>

                                        <tr>

                                            <td>
                                                <?= $no++; ?>
                                            </td>

                                            <td>
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
                                                <?= (int) $row['poin']; ?>
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

                                <?php else: ?>

                                    <tr>

                                        <td colspan="10" class="text-center py-4">
                                            Belum ada data pelanggaran.
                                        </td>

                                    </tr>

                                <?php endif; ?>

                            </tbody>

                            <tfoot class="table-light">

                                <tr>

                                    <th colspan="5" class="text-end">
                                        Total Poin
                                    </th>

                                    <th colspan="5">
                                        <?= $totalPoin; ?> Poin
                                    </th>

                                </tr>

                            </tfoot>

                        </table>

                    </div>

                    <!-- TANDA TANGAN -->
                    <div class="row mt-5">

                        <div class="col-md-7"></div>

                        <div class="col-md-5 text-center">

                            <p>Mengetahui,</p>

                            <p>
                                Wali Kelas <?= aman($wali['nama_kelas']); ?>
                            </p>

                            <br><br><br>

                            <p class="fw-bold text-decoration-underline mb-0">
                                <?= aman($wali['nama_wali_kelas']); ?>
                            </p>

                            <p>Wali Kelas</p>

                        </div>

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

<style>
@media print {

    body {
        background: white !important;
        overflow: visible !important;
    }

    .container-fluid {
        height: auto !important;
        overflow: visible !important;
    }

    .row {
        display: block !important;
    }

    aside,
    .bagian-tombol {
        display: none !important;
    }

    main {
        width: 100% !important;
        height: auto !important;
        overflow: visible !important;
        padding: 0 !important;
    }

    .card {
        box-shadow: none !important;
        border: none !important;
    }

    .card-body {
        padding: 0 !important;
    }

    .table-responsive {
        overflow: visible !important;
    }

    table {
        font-size: 10px;
        width: 100% !important;
    }

    @page {
        size: A4 landscape;
        margin: 12mm;
    }

}
</style>

<?php mysqli_stmt_close($stmt); ?>

</body>
</html>