
<?php

session_start();

if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit;
}

require_once 'config/koneksi.php';

// Menghitung jumlah data
function hitungData($koneksi, $tabel) {
    $tabelDiizinkan = [
        't_siswa',
        't_guru',
        't_pelanggaran_siswa'
    ];

    if (!in_array($tabel, $tabelDiizinkan, true)) {
        return 0;
    }

    $hasil = $koneksi->query(
        "SELECT COUNT(*) AS total FROM `$tabel`"
    );

    return $hasil ? (int)$hasil->fetch_assoc()['total'] : 0;
}

$totalSiswa = hitungData($koneksi, 't_siswa');
$totalGuru = hitungData($koneksi, 't_guru');
$totalPelanggaran = hitungData($koneksi, 't_pelanggaran_siswa');

$qCatatan = $koneksi->query(
    "SELECT COUNT(*) AS total
     FROM t_pelanggaran_siswa
     WHERE MONTH(tanggal) = MONTH(CURDATE())
       AND YEAR(tanggal) = YEAR(CURDATE())"
);

$totalCatatan = $qCatatan
    ? (int)$qCatatan->fetch_assoc()['total']
    : 0;

// Mengambil catatan pelanggaran bulan ini
$catatanBulanan = $koneksi->query(
    "SELECT nama_siswa, nama_kelas, nama_pelanggaran, tanggal
     FROM t_pelanggaran_siswa
     WHERE MONTH(tanggal) = MONTH(CURDATE())
       AND YEAR(tanggal) = YEAR(CURDATE())
     ORDER BY tanggal DESC
     LIMIT 10"
);

function aman($nilai) {
    return htmlspecialchars(
        (string)($nilai ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Dashboard</title>

    <!-- Bootstrap bawaan -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <!-- Ikon menu -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
          rel="stylesheet">

    <!-- Tambahan CSS tampilan dashboard -->
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #fff;
            font-family: Arial, Helvetica, sans-serif;
            color: #222;
        }

        .container-fluid {
            padding: 0;
        }

        .container-fluid > .row {
            margin: 0;
        }

        /* SIDEBAR */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            width: 216px;
            min-height: 100vh;
            overflow-y: auto;
            padding: 0 !important;
            background: #5b422f !important;
            z-index: 1000;
        }

        .brand {
            height: 120px;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 15px;
            background: #5b422f;
            border-bottom: 1px solid rgba(255,255,255,.12);
        }

        .brand-logo {
            width: 54px;
            height: 54px;
            flex-shrink: 0;
            border-radius: 50%;
            background: #fff;
            color: #5b422f;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
        }

        .brand-name {
            color: #fff;
            font-size: 16px;
            font-weight: 600;
            white-space: nowrap;
        }

        .sidebar .nav {
            padding: 8px 10px;
        }

        .sidebar .nav-link {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #fff !important;
            padding: 11px 12px;
            border-radius: 0;
            font-size: 14px;
            transition: background .2s;
        }

        .sidebar .nav-link i {
            width: 17px;
            text-align: center;
            font-size: 16px;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: #735843 !important;
            color: #fff !important;
        }

        .sidebar hr {
            border-color: rgba(255,255,255,.35);
        }

        .sidebar .nav-link.text-danger {
            color: #ffd0d0 !important;
        }

        /* KONTEN UTAMA */
        .main-content {
            margin-left: 216px;
            width: calc(100% - 216px);
            min-height: 100vh;
            padding: 26px 40px !important;
            background: #fff;
        }

        .welcome h2 {
            margin-bottom: 8px;
            font-size: 25px;
            font-weight: 600;
        }

        .welcome p {
            margin-bottom: 7px;
            font-size: 14px;
            color: #777;
        }

        .garis-pemisah {
            margin: 24px 0 20px;
            border-color: #ddd;
        }

        /* TIGA KARTU RINGKASAN */
        .kartu-ringkasan {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
            margin-bottom: 90px;
        }

        .kartu-info {
            min-height: 101px;
            display: flex;
            align-items: center;
            gap: 24px;
            padding: 20px;
            border-radius: 13px;
            color: #fff;
        }

        .kartu-info:nth-child(1) {
            background: #e5d9c8;
        }

        .kartu-info:nth-child(2) {
            background: #c6beb3;
        }

        .kartu-info:nth-child(3) {
            background: #a49d93;
        }

        .kartu-info .ikon {
            min-width: 44px;
            font-size: 43px;
            line-height: 1;
        }

        .kartu-info .label {
            margin-bottom: 2px;
            font-size: 14px;
            font-weight: 600;
        }

        .kartu-info .angka {
            font-size: 16px;
            font-weight: 700;
        }

        /* TABEL CATATAN BULANAN */
        .judul-catatan {
            margin: 0 0 10px;
            font-size: 20px;
            font-weight: 700;
        }

        .panel-catatan {
            min-height: 295px;
            padding: 45px 38px 24px;
            background: #e5d9c8;
            border-radius: 32px;
        }

        .panel-catatan .table {
            margin-bottom: 0;
            border-collapse: collapse;
        }

        .panel-catatan .table th,
        .panel-catatan .table td {
            padding: 11px 12px;
            border: 1px solid rgba(255,255,255,.8);
            background: transparent;
            color: #44382e;
            font-size: 14px;
        }

        .panel-catatan .table th {
            text-align: center;
            font-weight: 700;
        }

        /* TAMPILAN RESPONSIF */
        @media (max-width: 900px) {
            .main-content {
                padding: 24px 20px !important;
            }

            .kartu-ringkasan {
                margin-bottom: 55px;
            }

            .kartu-info {
                gap: 12px;
                padding: 15px;
            }
        }

        @media (max-width: 600px) {
            .sidebar {
                width: 160px;
            }

            .brand {
                gap: 7px;
                padding: 10px;
            }

            .brand-logo {
                width: 40px;
                height: 40px;
                font-size: 23px;
            }

            .brand-name {
                font-size: 13px;
            }

            .sidebar .nav {
                padding: 8px 5px;
            }

            .sidebar .nav-link {
                gap: 7px;
                padding: 10px 7px;
                font-size: 12px;
            }

            .main-content {
                margin-left: 160px;
                width: calc(100% - 160px);
                padding: 20px 12px !important;
            }

            .kartu-ringkasan {
                grid-template-columns: 1fr;
                margin-bottom: 35px;
            }

            .panel-catatan {
                padding: 20px 12px;
                border-radius: 18px;
            }

            .panel-catatan .table th,
            .panel-catatan .table td {
                padding: 8px 5px;
                font-size: 12px;
            }
        }
    </style>
</head>

<body>

<div class="container-fluid">
    <div class="row">

        <!-- SIDEBAR: MENU BAWAAN TETAP DIPERTAHANKAN -->
        <div class="col-md-3 col-lg-2 sidebar">

            <div class="brand">
                <div class="brand-logo">
                    <i class="bi bi-bank"></i>
                </div>

                <div class="brand-name">
                    JusticeAdmin
                </div>
            </div>

            <ul class="nav nav-pills flex-column">

                <li class="nav-item mb-2">
                    <a href="dashboard.php" class="nav-link active">
                        <i class="bi bi-speedometer2"></i>
                        Dashboard
                    </a>
                </li>

                <?php if ($_SESSION['role'] == 'admin') { ?>

                <li class="nav-item mb-2">
                    <a href="siswa.php" class="nav-link">
                        <i class="bi bi-people-fill"></i>
                        Data Siswa
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a href="guru.php" class="nav-link">
                        <i class="bi bi-person-badge-fill"></i>
                        Data Guru
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a href="kelas.php" class="nav-link">
                        <i class="bi bi-building"></i>
                        Kelas
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a href="tahun_ajaran.php" class="nav-link">
                        <i class="bi bi-calendar3"></i>
                        Tahun Ajaran
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a href="penempatan_siswa.php" class="nav-link">
                        <i class="bi bi-person-lines-fill"></i>
                        Penempatan Siswa
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a href="wali_kelas.php" class="nav-link">
                        <i class="bi bi-person-workspace"></i>
                        Wali Kelas
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a href="kategori_pelanggaran.php" class="nav-link">
                        <i class="bi bi-list-check"></i>
                        Kategori Pelanggaran
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a href="jenis_pelanggaran.php" class="nav-link">
                        <i class="bi bi-journal-text"></i>
                        Jenis Pelanggaran
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a href="cetak_export.php" class="nav-link">
                        <i class="bi bi-printer-fill"></i>
                        Cetak / Export
                    </a>
                </li>

                <?php } ?>

                <?php if ($_SESSION['role'] == 'guru') { ?>

                <li class="nav-item mb-2">
                    <a href="catat_pelanggaran.php" class="nav-link">
                        <i class="bi bi-pencil-square"></i>
                        Catat Pelanggaran
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a href="tindakan.php" class="nav-link">
                        <i class="bi bi-clipboard-check"></i>
                        Tindakan
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a href="laporan.php" class="nav-link">
                        <i class="bi bi-file-earmark-bar-graph"></i>
                        Laporan
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a href="riwayat.php" class="nav-link">
                        <i class="bi bi-clock-history"></i>
                        Riwayat
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a href="rekap_poin.php" class="nav-link">
                        <i class="bi bi-calculator"></i>
                        Rekap Poin
                    </a>
                </li>

                <?php } ?>

                <hr class="text-secondary">

                <li class="nav-item">
                    <a href="logout.php" class="nav-link text-danger">
                        <i class="bi bi-box-arrow-left"></i>
                        Logout
                    </a>
                </li>

            </ul>
        </div>

        <!-- KONTEN DASHBOARD -->
        <main class="col-md-9 col-lg-10 main-content">

            <div class="welcome">
                <h2>Dashboard</h2>

                <p>
                    Selamat datang,
                    <?= aman($_SESSION['name'] ?? 'Pengguna') ?>
                </p>

                <p>
                    Role: <?= aman($_SESSION['role']) ?>
                </p>
            </div>

            <hr class="garis-pemisah">

            <!-- TIGA KARTU RINGKASAN -->
            <div class="kartu-ringkasan">

                <div class="kartu-info">
                    <div class="ikon">
                        <i class="bi bi-person-fill"></i>
                    </div>

                    <div>
                        <div class="label">Siswa</div>
                        <div class="angka"><?= $totalSiswa ?></div>
                    </div>
                </div>

                <div class="kartu-info">
                    <div class="ikon">
                        <i class="bi bi-file-earmark-text-fill"></i>
                    </div>

                    <div>
                        <div class="label">Catatan</div>
                        <div class="angka"><?= $totalCatatan ?></div>
                    </div>
                </div>

                <div class="kartu-info">
                    <div class="ikon">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </div>

                    <div>
                        <div class="label">Pelanggaran</div>
                        <div class="angka"><?= $totalPelanggaran ?></div>
                    </div>
                </div>

            </div>

            <!-- INFORMASI CATATAN BULAN INI -->
            <h4 class="judul-catatan">
                Informasi Catatan Bulan Ini
            </h4>

            <div class="panel-catatan">
                <div class="table-responsive">

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th style="width: 8%">No</th>
                                <th>Nama</th>
                                <th>Kelas</th>
                                <th>Catatan</th>
                            </tr>
                        </thead>

                        <tbody>

                        <?php if ($catatanBulanan && $catatanBulanan->num_rows > 0) { ?>

                            <?php $no = 1; ?>

                            <?php while ($row = $catatanBulanan->fetch_assoc()) { ?>
                                <tr>
                                    <td class="text-center">
                                        <?= $no++ ?>
                                    </td>

                                    <td>
                                        <?= aman($row['nama_siswa']) ?>
                                    </td>

                                    <td>
                                        <?= aman($row['nama_kelas']) ?>
                                    </td>

                                    <td>
                                        <?= aman($row['nama_pelanggaran']) ?>
                                    </td>
                                </tr>
                            <?php } ?>

                        <?php } else { ?>

                            <tr>
                                <td colspan="4" class="text-center">
                                    Belum ada catatan pelanggaran bulan ini.
                                </td>
                            </tr>

                        <?php } ?>

                        </tbody>
                    </table>

                </div>
            </div>

        </main>

    </div>
</div>

</body>
</html>