
<?php
session_start();
require_once __DIR__ . "/config/koneksi.php";

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    exit("Anda tidak memiliki akses");
}

function aman($nilai) {
    return htmlspecialchars((string)($nilai ?? ''), ENT_QUOTES, 'UTF-8');
}

$nama = aman($_SESSION['name'] ?? 'Pengguna');

// ======================================
// AMBIL DAFTAR KELAS
// ======================================

$daftar_kelas = mysqli_query(
    $koneksi,
    "SELECT id, nama
     FROM t_kelas
     ORDER BY nama ASC"
);

if (!$daftar_kelas) {
    die("Gagal mengambil daftar kelas: " . mysqli_error($koneksi));
}

// ======================================
// FILTER KELAS DAN SISWA
// ======================================

$kelas_id = isset($_GET['kelas_id']) ? (int) $_GET['kelas_id'] : 0;
$siswa_dipilih = isset($_GET['siswa']) ? trim($_GET['siswa']) : '';

$dataNamaSiswa = [];
$dataLaporan = [];

// ======================================
// AMBIL NAMA SISWA BERDASARKAN KELAS
// ======================================

if ($kelas_id > 0) {

    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT DISTINCT s.nama
         FROM t_kelas_siswa ks
         JOIN t_siswa s ON s.id = ks.siswa_id
         WHERE ks.kelas_id = ?
           AND ks.status_aktif = 1
           AND ks.tanggal_mulai <= CURDATE()
           AND (
               ks.tanggal_selesai IS NULL
               OR ks.tanggal_selesai >= CURDATE()
           )
           AND s.status_aktif = 1
           AND s.nama IS NOT NULL
           AND s.nama != ''
         ORDER BY s.nama ASC"
    );

    if (!$stmt) {
        die("Gagal menyiapkan daftar siswa: " . mysqli_error($koneksi));
    }

    mysqli_stmt_bind_param($stmt, "i", $kelas_id);
    mysqli_stmt_execute($stmt);

    $hasilNama = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($hasilNama)) {
        $dataNamaSiswa[] = $row['nama'];
    }

    mysqli_stmt_close($stmt);
}

// ======================================
// AMBIL NAMA KELAS YANG DIPILIH
// ======================================

$namaKelasDipilih = '';

if ($kelas_id > 0) {

    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT nama FROM t_kelas WHERE id = ? LIMIT 1"
    );

    mysqli_stmt_bind_param($stmt, "i", $kelas_id);
    mysqli_stmt_execute($stmt);

    $hasilKelas = mysqli_stmt_get_result($stmt);
    $dataKelas = mysqli_fetch_assoc($hasilKelas);

    if ($dataKelas) {
        $namaKelasDipilih = $dataKelas['nama'];
    }

    mysqli_stmt_close($stmt);
}

// ======================================
// AMBIL DATA LAPORAN BERDASARKAN KELAS
// DAN NAMA SISWA
// ======================================

if ($kelas_id > 0 && $siswa_dipilih !== '') {

    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT
            ps.*,
            k.nama AS nama_kelas_resmi
         FROM t_pelanggaran_siswa ps
         LEFT JOIN t_kelas k ON ps.kelas_id = k.id
         WHERE ps.kelas_id = ?
           AND ps.nama_siswa = ?
         ORDER BY ps.tanggal DESC"
    );

    if (!$stmt) {
        die("Gagal menyiapkan pencarian: " . mysqli_error($koneksi));
    }

    mysqli_stmt_bind_param(
        $stmt,
        "is",
        $kelas_id,
        $siswa_dipilih
    );

    if (!mysqli_stmt_execute($stmt)) {
        die("Gagal mengambil laporan: " . mysqli_stmt_error($stmt));
    }

    $hasil = mysqli_stmt_get_result($stmt);

    if (!$hasil) {
        die("Gagal membaca hasil laporan: " . mysqli_error($koneksi));
    }

    while ($data = mysqli_fetch_assoc($hasil)) {
        $dataLaporan[] = $data;
    }

    mysqli_stmt_close($stmt);
}

// ======================================
// EXPORT EXCEL
// ======================================

if (isset($_GET['export']) && $_GET['export'] === 'excel') {

    if ($kelas_id <= 0 || $siswa_dipilih === '') {
        exit("Silakan pilih kelas dan nama siswa terlebih dahulu.");
    }

    if (!in_array($siswa_dipilih, $dataNamaSiswa, true)) {
        exit("Nama siswa tidak sesuai dengan kelas yang dipilih.");
    }

    header("Content-Type: text/csv; charset=UTF-8");
    header("Content-Disposition: attachment; filename=laporan_pelanggaran.csv");

    $output = fopen("php://output", "w");

    fwrite($output, "\xEF\xBB\xBF");

    fputcsv($output, [
        "No",
        "Nama Siswa",
        "Kelas",
        "Pelanggaran",
        "Nama Guru",
        "Tanggal",
        "Poin",
        "Status"
    ], ";");

    $no_export = 1;

    foreach ($dataLaporan as $data) {

        fputcsv($output, [
            $no_export++,
            $data['nama_siswa'] ?? '',
            $data['nama_kelas_resmi'] ?? '-',
            $data['nama_pelanggaran'] ?? '',
            $data['nama_guru'] ?? '',
            $data['tanggal'] ?? '',
            $data['poin'] ?? '',
            $data['status'] ?? ''
        ], ";");

    }

    fclose($output);
    exit;
}

// ======================================
// MENU ADMIN
// ======================================

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

// ======================================
// HITUNG TOTAL POIN
// ======================================

$totalPoinCetak = 0;

foreach ($dataLaporan as $itemPoin) {
    $totalPoinCetak += (int)($itemPoin['poin'] ?? 0);
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Laporan - Sistem Informasi Pelanggaran Siswa</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <link href="https://cdn.datatables.net/2.3.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">

    <style>

        @media print {

            @page {
                size: A4 landscape;
                margin: 12mm;
            }

            html,
            body {
                width: 100% !important;
                height: auto !important;
                margin: 0 !important;
                padding: 0 !important;
                background: white !important;
                color: black !important;
                font-family: "Times New Roman", serif !important;
                font-size: 12px !important;
                overflow: visible !important;
            }

            aside {
                display: none !important;
            }

            body .container-fluid {
                width: 100% !important;
                height: auto !important;
                margin: 0 !important;
                padding: 0 !important;
                overflow: visible !important;
            }

            body .container-fluid > .row {
                display: block !important;
                width: 100% !important;
                height: auto !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            main {
                display: block !important;
                width: 100% !important;
                max-width: 100% !important;
                height: auto !important;
                margin: 0 !important;
                padding: 0 !important;
                overflow: visible !important;
            }

            .tidak-cetak {
                display: none !important;
            }

            .card,
            .card-body {
                width: 100% !important;
                height: auto !important;
                margin: 0 !important;
                padding: 0 !important;
                border: none !important;
                box-shadow: none !important;
                overflow: visible !important;
            }

            .judul-cetak {
                display: block !important;
                width: 100% !important;
                margin-bottom: 12px !important;
                color: black !important;
            }

            .kop-cetak {
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                gap: 20px !important;
                width: 100% !important;
                padding-bottom: 10px !important;
                border-bottom: 4px double black !important;
                text-align: center !important;
            }

            .logo-cetak {
                display: block !important;
                width: 85px !important;
                height: 85px !important;
                object-fit: contain !important;
            }

            .identitas-sekolah {
                flex: 1 !important;
                text-align: center !important;
            }

            .identitas-sekolah h2 {
                font-size: 22px !important;
                font-weight: bold !important;
                margin: 0 0 7px 0 !important;
                color: black !important;
            }

            .identitas-sekolah p {
                font-size: 12px !important;
                margin: 0 !important;
                line-height: 1.5 !important;
                color: black !important;
            }

            .judul-laporan-cetak {
                text-align: center !important;
                margin: 15px 0 12px 0 !important;
            }

            .judul-laporan-cetak h3 {
                font-size: 20px !important;
                font-weight: bold !important;
                margin: 0 !important;
                color: black !important;
                text-decoration: underline !important;
            }

            .identitas-siswa-cetak {
                width: 100% !important;
                margin-bottom: 15px !important;
                font-size: 13px !important;
                line-height: 1.8 !important;
            }

            .identitas-siswa-cetak table {
                width: auto !important;
                border: none !important;
                margin: 0 !important;
            }

            .identitas-siswa-cetak td {
                border: none !important;
                padding: 1px 5px !important;
                color: black !important;
                text-align: left !important;
                background: white !important;
            }

            .table-responsive,
            #tabelLaporan_wrapper {
                width: 100% !important;
                max-width: 100% !important;
                overflow: visible !important;
            }

            #tabelLaporan {
                width: 100% !important;
                margin: 0 !important;
                border-collapse: collapse !important;
                font-family: "Times New Roman", serif !important;
                font-size: 11px !important;
            }

            #tabelLaporan th,
            #tabelLaporan td {
                border: 1px solid black !important;
                padding: 8px 6px !important;
                color: black !important;
                background: white !important;
                vertical-align: middle !important;
            }

            #tabelLaporan th {
                text-align: center !important;
                font-weight: bold !important;
            }

            #tabelLaporan td:nth-child(4) {
                text-align: left !important;
            }

            #tabelLaporan th:nth-child(2),
            #tabelLaporan td:nth-child(2),
            #tabelLaporan th:nth-child(3),
            #tabelLaporan td:nth-child(3) {
                display: none !important;
            }

            #tabelLaporan thead {
                display: table-header-group !important;
            }

            #tabelLaporan tr {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }

            .ringkasan-cetak {
                display: flex !important;
                justify-content: space-around !important;
                align-items: center !important;
                width: 100% !important;
                margin-top: 15px !important;
                padding: 10px !important;
                border-top: 2px solid black !important;
                border-bottom: 2px solid black !important;
                font-size: 14px !important;
                font-weight: bold !important;
            }

            .bagian-pengesahan {
                display: block !important;
                width: 100% !important;
                margin-top: 15px !important;
                page-break-inside: avoid !important;
            }

            .tanggal-cetak {
                text-align: right !important;
                font-size: 12px !important;
                margin-bottom: 12px !important;
            }

            .kolom-ttd {
                display: flex !important;
                justify-content: space-between !important;
                align-items: flex-start !important;
                width: 100% !important;
                text-align: center !important;
            }

            .ttd-item {
                width: 32% !important;
                font-size: 12px !important;
            }

            .ttd-jabatan {
                font-weight: bold !important;
                min-height: 35px !important;
            }

            .ttd-ruang {
                height: 65px !important;
            }

            .ttd-nama {
                font-weight: bold !important;
                text-decoration: underline !important;
            }

            .dt-layout-row,
            .dt-search,
            .dt-length,
            .dt-info,
            .dt-paging {
                display: none !important;
            }

        }

        @media screen {

            .judul-cetak,
            .ringkasan-cetak,
            .bagian-pengesahan {
                display: none;
            }

        }

    </style>

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

                <small class="text-secondary">
                    Panel Admin
                </small>

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
                            <?= $nama; ?>
                        </div>

                        <small class="text-secondary">
                            ADMIN
                        </small>

                    </div>

                </div>

            </div>

            <div class="text-secondary small fw-semibold mb-3">
                MENU UTAMA
            </div>

            <ul class="nav nav-pills flex-column gap-2">

                <?php foreach ($menuAdmin as $item): ?>

                    <li class="nav-item">

                        <a
                            href="<?= aman($item[1]); ?>"
                            class="nav-link <?= $item[0] === 'Laporan' ? 'active' : 'text-white'; ?> rounded-2 py-2 px-3"
                        >
                            <?= aman($item[0]); ?>
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

        <!-- KONTEN UTAMA -->
        <main class="col-9 col-md-9 col-lg-10 vh-100 overflow-auto p-3 p-lg-5">

            <!-- JUDUL HALAMAN -->
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4 pb-4 border-bottom tidak-cetak">

                <div>

                    <h2 class="fw-bold mb-2">
                        Laporan Pelanggaran Siswa
                    </h2>

                    <p class="text-secondary mb-2">
                        Lihat dan cetak laporan pelanggaran berdasarkan kelas dan nama siswa.
                    </p>

                    <a href="dashboard.php" class="btn btn-secondary btn-sm rounded-2">
                        Kembali ke Dashboard
                    </a>

                </div>

            </div>

            <!-- CARD LAPORAN -->
            <div class="card border-0 shadow-sm rounded-3">

                <div class="card-body p-3 p-lg-4">

                    <!-- FORM PENCARIAN BERTAHAP -->
                    <form method="GET" action="laporan.php" class="mb-4 tidak-cetak">

                        <div class="row g-3">

                            <!-- PILIH KELAS -->
                            <div class="col-12 col-md-5">

                                <label for="kelas_id" class="form-label fw-semibold">
                                    1. Pilih Kelas
                                </label>

                                <select
                                    name="kelas_id"
                                    id="kelas_id"
                                    class="form-select"
                                    required
                                    onchange="document.getElementById('formLaporan').submit()"
                                >

                                    <option value="">
                                        -- Pilih Kelas Terlebih Dahulu --
                                    </option>

                                    <?php while ($kelas = mysqli_fetch_assoc($daftar_kelas)): ?>

                                        <option
                                            value="<?= (int)$kelas['id']; ?>"
                                            <?= $kelas_id === (int)$kelas['id'] ? 'selected' : ''; ?>
                                        >
                                            <?= aman($kelas['nama']); ?>
                                        </option>

                                    <?php endwhile; ?>

                                </select>

                            </div>

                            <!-- PILIH NAMA SISWA -->
                            <div class="col-12 col-md-5">

                                <label for="siswa" class="form-label fw-semibold">
                                    2. Pilih Nama Siswa
                                </label>

                                <select
                                    name="siswa"
                                    id="siswa"
                                    class="form-select"
                                    <?= $kelas_id <= 0 ? 'disabled' : ''; ?>
                                    required
                                >

                                    <option value="">
                                        <?= $kelas_id > 0
                                            ? '-- Pilih Nama Siswa --'
                                            : '-- Pilih Kelas Terlebih Dahulu --'; ?>
                                    </option>

                                    <?php foreach ($dataNamaSiswa as $namaSiswa): ?>

                                        <option
                                            value="<?= aman($namaSiswa); ?>"
                                            <?= $siswa_dipilih === $namaSiswa ? 'selected' : ''; ?>
                                        >
                                            <?= aman($namaSiswa); ?>
                                        </option>

                                    <?php endforeach; ?>

                                </select>

                            </div>

                            <!-- TOMBOL CARI -->
                            <div class="col-12 col-md-2 d-flex align-items-end">

                                <button
                                    type="submit"
                                    class="btn btn-primary w-100 rounded-2"
                                    <?= $kelas_id <= 0 ? 'disabled' : ''; ?>
                                >
                                    <i class="bi bi-search me-1"></i>
                                    Cari
                                </button>

                            </div>

                        </div>

                    </form>

                    <!-- HASIL LAPORAN -->
                    <?php if ($kelas_id > 0 && $siswa_dipilih !== ''): ?>

                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3 tidak-cetak">

                            <div>

                                <h5 class="fw-bold mb-1">
                                    Hasil Laporan Siswa
                                </h5>

                                <p class="text-secondary mb-0">
                                    Kelas:
                                    <strong><?= aman($namaKelasDipilih); ?></strong>
                                    |
                                    Nama:
                                    <strong><?= aman($siswa_dipilih); ?></strong>
                                </p>

                            </div>

                            <?php if (count($dataLaporan) > 0): ?>

                                <div class="d-flex flex-wrap gap-2">

                                    <button
                                        type="button"
                                        onclick="cetakLaporan()"
                                        class="btn btn-success rounded-2"
                                    >
                                        <i class="bi bi-printer me-1"></i>
                                        Cetak
                                    </button>

                                    <a
                                        href="laporan.php?kelas_id=<?= $kelas_id; ?>&siswa=<?= urlencode($siswa_dipilih); ?>&export=excel"
                                        class="btn btn-primary rounded-2"
                                    >
                                        <i class="bi bi-file-earmark-excel me-1"></i>
                                        Export Excel
                                    </a>

                                </div>

                            <?php endif; ?>

                        </div>

                        <!-- JIKA DATA TIDAK DITEMUKAN -->
                        <?php if (count($dataLaporan) === 0): ?>

                            <div class="alert alert-warning tidak-cetak" role="alert">

                                <i class="bi bi-exclamation-triangle-fill me-2"></i>

                                Data laporan pelanggaran untuk
                                <strong><?= aman($siswa_dipilih); ?></strong>
                                pada kelas
                                <strong><?= aman($namaKelasDipilih); ?></strong>
                                tidak ditemukan.

                            </div>

                        <?php else: ?>

                            <!-- KOP DAN IDENTITAS CETAK -->
                            <div class="judul-cetak">

                                <div class="kop-cetak">

                                    <img
                                        src="logo.png"
                                        alt="Logo Sekolah"
                                        class="logo-cetak"
                                    >

                                    <div class="identitas-sekolah">

                                        <h2>
                                            SMK MUHAMMADIYAH KOTA TASIKMALAYA
                                        </h2>

                                        <p>
                                            Jl. Rumah Sakit No. 29, Empangsari,
                                            Kecamatan Tawang, Kota Tasikmalaya,
                                            Jawa Barat 46113
                                        </p>

                                    </div>

                                </div>

                                <div class="judul-laporan-cetak">

                                    <h3>
                                        LAPORAN PELANGGARAN SISWA
                                    </h3>

                                </div>

                                <div class="identitas-siswa-cetak">

                                    <table>

                                        <tr>
                                            <td><strong>Nama Siswa</strong></td>
                                            <td>:</td>
                                            <td><?= aman($siswa_dipilih); ?></td>
                                        </tr>

                                        <tr>
                                            <td><strong>Kelas</strong></td>
                                            <td>:</td>
                                            <td><?= aman($namaKelasDipilih); ?></td>
                                        </tr>

                                        <tr>
                                            <td><strong>Tahun Ajaran</strong></td>
                                            <td>:</td>
                                            <td>
                                                <?= aman(
                                                    $dataLaporan[0]['nama_tahun_ajaran']
                                                    ?? $dataLaporan[0]['tahun_ajaran']
                                                    ?? '-'
                                                ); ?>
                                            </td>
                                        </tr>

                                    </table>

                                </div>

                            </div>

                            <!-- TABEL LAPORAN -->
                            <div class="table-responsive">

                                <table id="tabelLaporan" class="table table-hover align-middle">

                                    <thead>
                                        <tr class="table-primary">
                                            <th>No</th>
                                            <th>Nama Siswa</th>
                                            <th>Kelas</th>
                                            <th>Pelanggaran</th>
                                            <th>Nama Guru</th>
                                            <th>Tanggal</th>
                                            <th>Poin</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>

                                    <tbody>

                                        <?php $no = 1; ?>

                                        <?php foreach ($dataLaporan as $data): ?>

                                            <tr>

                                                <td><?= $no++; ?></td>

                                                <td class="fw-semibold">
                                                    <?= aman($data['nama_siswa'] ?? ''); ?>
                                                </td>

                                                <td>
                                                    <?= aman($data['nama_kelas_resmi'] ?? '-'); ?>
                                                </td>

                                                <td>
                                                    <?= aman($data['nama_pelanggaran'] ?? ''); ?>
                                                </td>

                                                <td>
                                                    <?= aman($data['nama_guru'] ?? ''); ?>
                                                </td>

                                                <td>
                                                    <?= aman($data['tanggal'] ?? ''); ?>
                                                </td>

                                                <td>
                                                    <?= aman($data['poin'] ?? ''); ?>
                                                </td>

                                                <td>
                                                    <?= aman($data['status'] ?? ''); ?>
                                                </td>

                                            </tr>

                                        <?php endforeach; ?>

                                    </tbody>

                                </table>

                            </div>

                            <!-- RINGKASAN CETAK -->
                            <div class="ringkasan-cetak">

                                <div>
                                    Total Pelanggaran:
                                    <?= count($dataLaporan); ?> Kali
                                </div>

                                <div>
                                    Total Poin:
                                    <?= $totalPoinCetak; ?> Poin
                                </div>

                            </div>

                            <!-- TANDA TANGAN -->
                            <div class="bagian-pengesahan">

                                <div class="tanggal-cetak">
                                    Tasikmalaya, <?= date('d-m-Y'); ?>
                                </div>

                                <div class="kolom-ttd">

                                    <div class="ttd-item">

                                        <div class="ttd-jabatan">
                                            Mengetahui,<br>
                                            Kepala Sekolah
                                        </div>

                                        <div class="ttd-ruang"></div>

                                        <div class="ttd-nama">
                                            Wawan Kurniawan, S.Pd.I
                                        </div>

                                    </div>

                                    <div class="ttd-item">

                                        <div class="ttd-jabatan">
                                            Guru Bimbingan Konseling (BP)
                                        </div>

                                        <div class="ttd-ruang"></div>

                                        <div class="ttd-nama">
                                            Yusi Luthfiani, S.Sos.I
                                        </div>

                                    </div>

                                    <div class="ttd-item">

                                        <div class="ttd-jabatan">
                                            Orang Tua / Wali
                                        </div>

                                        <div class="ttd-ruang"></div>

                                        <div class="ttd-nama">
                                            (........................................)
                                        </div>

                                    </div>

                                </div>

                            </div>

                        <?php endif; ?>

                    <?php endif; ?>

                </div>

            </div>

            <!-- FOOTER -->
            <div class="text-secondary small mt-5 pt-3 border-top tidak-cetak">
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

// FORM PENCARIAN
document.addEventListener('DOMContentLoaded', function () {

    const form = document.querySelector('form[action="laporan.php"]');

    const pilihanKelas = document.getElementById('kelas_id');

    if (form && pilihanKelas) {

        form.id = 'formLaporan';

        pilihanKelas.addEventListener('change', function () {

            // Ganti kelas, nama siswa harus dipilih ulang
            const pilihanSiswa = document.getElementById('siswa');

            if (pilihanSiswa) {
                pilihanSiswa.value = '';
            }

            form.submit();

        });

    }

});

// DATATABLES
let tabelLaporan = null;

$(document).ready(function () {

    if ($('#tabelLaporan').length) {

        tabelLaporan = $('#tabelLaporan').DataTable({

            pageLength: 10,

            lengthMenu: [5, 10, 25, 50, 100],

            ordering: true,

            searching: true,

            language: {

                search: "Cari:",

                lengthMenu: "Tampilkan _MENU_ data",

                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data laporan",

                infoEmpty: "Tidak ada data laporan",

                infoFiltered: "(disaring dari _MAX_ total data)",

                zeroRecords: "Data laporan tidak ditemukan",

                emptyTable: "Belum ada laporan",

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

    }

});

// CETAK LAPORAN
function cetakLaporan() {

    let halamanAwal = 0;
    let jumlahData = 10;

    if (tabelLaporan !== null) {

        halamanAwal = tabelLaporan.page();
        jumlahData = tabelLaporan.page.len();

        tabelLaporan.page.len(-1).draw();

    }

    setTimeout(function () {

        window.print();

        if (tabelLaporan !== null) {

            tabelLaporan.page.len(jumlahData).draw();

            tabelLaporan.page(halamanAwal).draw('page');

        }

    }, 300);

}

</script>

</body>
</html>
