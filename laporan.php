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

$nama = htmlspecialchars(
    $_SESSION['name'] ?? 'Pengguna',
    ENT_QUOTES,
    'UTF-8'
);

// MENGAMBIL DAFTAR NAMA SISWA
$daftar_nama = mysqli_query(
    $koneksi,
    "SELECT DISTINCT nama AS nama_siswa
     FROM t_siswa
     WHERE nama IS NOT NULL AND nama != ''
     ORDER BY nama ASC"
);

if (!$daftar_nama) {
    die("Gagal mengambil daftar nama siswa: " . mysqli_error($koneksi));
}

$cari = isset($_GET['cari']) ? trim($_GET['cari']) : '';

$dataLaporan = [];

// MENGAMBIL DATA LAPORAN SISWA
if ($cari !== '') {

    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT *
         FROM t_pelanggaran_siswa
         WHERE nama_siswa = ?
         ORDER BY tanggal DESC"
    );

    if (!$stmt) {
        die("Gagal menyiapkan pencarian: " . mysqli_error($koneksi));
    }

    mysqli_stmt_bind_param($stmt, "s", $cari);

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

// EXPORT KE EXCEL
if (isset($_GET['export']) && $_GET['export'] === 'excel') {

    if ($cari === '') {
        exit("Silakan pilih nama siswa terlebih dahulu.");
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
            $data['nama_kelas'] ?? '',
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

// MENU ADMIN
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

    <title>Laporan - Sistem Informasi Pelanggaran Siswa</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <link href="https://cdn.datatables.net/2.3.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">

    <style>
        @media print {

            @page {
                size: landscape;
                margin: 15mm;
            }

            body {
                background: white !important;
                font-size: 12px;
                overflow: visible !important;
                height: auto !important;
            }

            aside {
                display: none !important;
            }

            main {
                width: 100% !important;
                max-width: 100% !important;
                flex: 0 0 100% !important;
                padding: 0 !important;
                margin: 0 !important;
                height: auto !important;
                overflow: visible !important;
            }

            .container-fluid,
            .row,
            .card,
            .card-body {
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                border: none !important;
                box-shadow: none !important;
                height: auto !important;
                overflow: visible !important;
            }

            .tidak-cetak {
                display: none !important;
            }

            .judul-cetak {
                display: block !important;
                margin-bottom: 20px !important;
            }

            .table-responsive {
                overflow: visible !important;
            }

            table {
                width: 100% !important;
                border-collapse: collapse !important;
            }

            table th,
            table td {
                border: 1px solid #000 !important;
                padding: 7px !important;
                color: #000 !important;
            }

            table thead {
                display: table-header-group;
            }

            table tr {
                page-break-inside: avoid;
            }

            table thead th {
                background: #dbeafe !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
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
            .judul-cetak {
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

                <small class="text-secondary">Panel Admin</small>
            </div>

            <!-- PROFIL ADMIN -->
            <div class="bg-secondary bg-opacity-25 rounded-3 p-3 mb-4">
                <div class="d-flex align-items-center gap-3">

                    <img src="logo.png"
                         alt="Logo Aplikasi"
                         width="44"
                         height="44"
                         class="rounded-circle bg-white p-1">

                    <div class="text-white text-break">
                        <div class="fw-semibold"><?= $nama; ?></div>
                        <small class="text-secondary">ADMIN</small>
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
                            href="<?= htmlspecialchars($item[1], ENT_QUOTES, 'UTF-8'); ?>"
                            class="nav-link <?= $item[0] === 'Laporan' ? 'active' : 'text-white'; ?> rounded-2 py-2 px-3"
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

        <!-- KONTEN UTAMA -->
        <main class="col-9 col-md-9 col-lg-10 vh-100 overflow-auto p-3 p-lg-5">

            <!-- JUDUL HALAMAN -->
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4 pb-4 border-bottom tidak-cetak">

                <div>
                    <h2 class="fw-bold mb-2">
                        Laporan Pelanggaran Siswa
                    </h2>

                    <p class="text-secondary mb-2">
                        Lihat dan cetak laporan pelanggaran berdasarkan nama siswa.
                    </p>

                    <a href="dashboard.php" class="btn btn-secondary btn-sm rounded-2">
                        Kembali ke Dashboard
                    </a>
                </div>

            </div>

            <!-- CARD LAPORAN -->
            <div class="card border-0 shadow-sm rounded-3">

                <div class="card-body p-3 p-lg-4">

                    <!-- FORM PENCARIAN -->
                    <form method="GET" action="laporan.php" class="mb-4 tidak-cetak">

                        <label for="cari" class="form-label fw-semibold">
                            Pilih Nama Siswa
                        </label>

                        <div class="row g-2">

                            <div class="col-md-8">

                                <select name="cari"
                                        id="cari"
                                        class="form-select"
                                        required>

                                    <option value="">-- Pilih Nama Siswa --</option>

                                    <?php while ($siswa = mysqli_fetch_assoc($daftar_nama)): ?>

                                        <option
                                            value="<?= htmlspecialchars($siswa['nama_siswa'], ENT_QUOTES, 'UTF-8'); ?>"
                                            <?= $cari === $siswa['nama_siswa'] ? 'selected' : ''; ?>
                                        >
                                            <?= htmlspecialchars($siswa['nama_siswa'], ENT_QUOTES, 'UTF-8'); ?>
                                        </option>

                                    <?php endwhile; ?>

                                </select>

                            </div>

                            <div class="col-auto">

                                <button type="submit" class="btn btn-primary rounded-2">
                                    <i class="bi bi-search me-1"></i>
                                    Cari Laporan
                                </button>

                            </div>

                        </div>

                    </form>

                    <!-- HASIL LAPORAN -->
                    <?php if ($cari !== ''): ?>

                        <!-- JUDUL KHUSUS CETAK -->
                        <div class="judul-cetak">
                            <h2 class="fw-bold text-center">
                                LAPORAN PELANGGARAN SISWA
                            </h2>

                            <h5 class="text-center mb-4">
                                Nama Siswa:
                                <?= htmlspecialchars($cari, ENT_QUOTES, 'UTF-8'); ?>
                            </h5>
                        </div>

                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3 tidak-cetak">

                            <div>
                                <h5 class="fw-bold mb-1">
                                    Hasil Laporan Siswa
                                </h5>

                                <p class="text-secondary mb-0">
                                    Nama siswa:
                                    <strong><?= htmlspecialchars($cari, ENT_QUOTES, 'UTF-8'); ?></strong>
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
                                        href="laporan.php?cari=<?= urlencode($cari); ?>&export=excel"
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

                            <div class="alert alert-warning alert-dismissible fade show mt-3 tidak-cetak" role="alert">

                                <i class="bi bi-exclamation-triangle-fill me-2"></i>

                                <strong>Perhatian!</strong>

                                Data laporan pelanggaran untuk siswa
                                <strong><?= htmlspecialchars($cari, ENT_QUOTES, 'UTF-8'); ?></strong>
                                tidak ditemukan.

                                <button type="button"
                                        class="btn-close"
                                        data-bs-dismiss="alert"
                                        aria-label="Tutup">
                                </button>

                            </div>

                        <?php else: ?>

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
                                                    <?= htmlspecialchars($data['nama_siswa'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                                </td>

                                                <td>
                                                    <?= htmlspecialchars($data['nama_kelas'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                                </td>

                                                <td>
                                                    <?= htmlspecialchars($data['nama_pelanggaran'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                                </td>

                                                <td>
                                                    <?= htmlspecialchars($data['nama_guru'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                                </td>

                                                <td>
                                                    <?= htmlspecialchars($data['tanggal'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                                </td>

                                                <td>
                                                    <?= htmlspecialchars($data['poin'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                                </td>

                                                <td>
                                                    <?= htmlspecialchars($data['status'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                                </td>
                                            </tr>

                                        <?php endforeach; ?>

                                    </tbody>

                                </table>

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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script src="https://cdn.datatables.net/2.3.4/js/dataTables.min.js"></script>

<script src="https://cdn.datatables.net/2.3.4/js/dataTables.bootstrap5.min.js"></script>

<script>
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