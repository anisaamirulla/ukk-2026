
<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

$role = strtolower(trim($_SESSION['role'] ?? ''));

if (!in_array($role, ['admin', 'guru'])) {
    exit("Role pengguna tidak valid. Silakan login kembali.");
}

require_once __DIR__ . "/config/koneksi.php";

// FOLDER WALI KELAS
$folderWaliKelas = "walikelas/";

// NAMA PENGGUNA
$nama = $_SESSION['name'] ?? 'Pengguna';

// DATA GURU LOGIN
$guru_id = 0;
$namaGuruLogin = '';
$isWaliKelas = false;

if ($role === 'guru') {

    $cekGuru = mysqli_prepare(
        $koneksi,
        "SELECT id, nama
         FROM t_guru
         WHERE user_id = ?
         LIMIT 1"
    );

    if ($cekGuru) {

        mysqli_stmt_bind_param($cekGuru, "i", $_SESSION['id']);
        mysqli_stmt_execute($cekGuru);

        $hasilGuru = mysqli_stmt_get_result($cekGuru);
        $dataGuru = mysqli_fetch_assoc($hasilGuru);

        if ($dataGuru) {

            $guru_id = (int) $dataGuru['id'];
            $namaGuruLogin = trim($dataGuru['nama']);
            $nama = $dataGuru['nama'];

            // PEMERIKSAAN WALI KELAS AKTIF
            $cekWali = mysqli_prepare(
                $koneksi,
                "SELECT id
                 FROM t_wali_kelas
                 WHERE guru_id = ?
                 AND status_aktif = 1
                 AND tanggal_mulai <= CURDATE()
                 AND (
                     tanggal_selesai IS NULL
                     OR tanggal_selesai >= CURDATE()
                 )
                 LIMIT 1"
            );

            if ($cekWali) {

                mysqli_stmt_bind_param($cekWali, "i", $guru_id);
                mysqli_stmt_execute($cekWali);

                $hasilWali = mysqli_stmt_get_result($cekWali);

                if (mysqli_num_rows($hasilWali) > 0) {
                    $isWaliKelas = true;
                }

                mysqli_stmt_close($cekWali);
            }
        }

        mysqli_stmt_close($cekGuru);
    }
}

// MENGAMANKAN NAMA PENGGUNA
$nama = htmlspecialchars(
    $nama,
    ENT_QUOTES,
    'UTF-8'
);

// LABEL ROLE SIDEBAR
if ($role === 'admin') {
    $labelRole = 'ADMIN';
} elseif ($isWaliKelas) {
    $labelRole = 'WALI KELAS';
} else {
    $labelRole = 'GURU';
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

// MENU GURU BIASA
$menuGuru = [
    ['Dashboard', 'dashboard.php'],
    ['Catatan Pelanggaran', 'catatan_pelanggaran.php'],
    ['Riwayat', 'riwayat.php'],
    ['Rekap Poin', 'rekap_poin.php'],
    ['About Me', 'abautme.php']
];

// MENU WALI KELAS
$menuWaliKelas = [
    ['Dashboard', 'dashboard.php'],
    ['Data Siswa di Kelasnya', $folderWaliKelas . 'data_siswa.php'],
    ['Catat Pelanggaran', 'catatan_pelanggaran.php'],
    ['Riwayat Pelanggaran Siswa', 'riwayat.php'],
    ['Rekap Poin Siswa', 'rekap_poin.php'],
    ['Laporan Pelanggaran Kelas', $folderWaliKelas . 'rekap_pelanggaran.php'],
    ['About Me', 'abautme.php']
];

// MENENTUKAN MENU BERDASARKAN ROLE
if ($role === 'admin') {
    $menu = $menuAdmin;
} elseif ($isWaliKelas) {
    $menu = $menuWaliKelas;
} else {
    $menu = $menuGuru;
}


// ==========================================
// AMBIL DAFTAR KELAS
// ==========================================

$sqlKelas = "SELECT id, nama
             FROM t_kelas
             WHERE status_aktif = 1
             ORDER BY nama ASC";

$queryKelas = mysqli_query($koneksi, $sqlKelas);

if (!$queryKelas) {
    die("Query kelas gagal: " . mysqli_error($koneksi));
}

$daftarKelas = mysqli_fetch_all($queryKelas, MYSQLI_ASSOC);


// ==========================================
// KELAS YANG DIPILIH
// ==========================================

$kelasTerpilih = isset($_GET['kelas_id'])
    ? (int) $_GET['kelas_id']
    : 0;

$namaKelasTerpilih = '';

foreach ($daftarKelas as $kelas) {

    if ((int) $kelas['id'] === $kelasTerpilih) {
        $namaKelasTerpilih = $kelas['nama'];
        break;
    }
}

// Jika ID kelas tidak tersedia, batalkan pilihan
if ($namaKelasTerpilih === '') {
    $kelasTerpilih = 0;
}


// ==========================================
// AMBIL SELURUH SISWA DALAM KELAS
// TERMASUK SISWA TANPA PELANGGARAN
// STATUS DIPROSES DAN SELESAI TETAP DIHITUNG
// ==========================================

$daftarRekap = [];

$totalSiswa = 0;
$totalPelanggaran = 0;
$totalPoin = 0;

if ($kelasTerpilih > 0) {

    $sqlRekap = "
        SELECT
            s.id AS siswa_id,
            s.nama AS nama_siswa,
            k.nama AS nama_kelas,
            COALESCE(rekap.jumlah_pelanggaran, 0) AS jumlah_pelanggaran,
            COALESCE(rekap.total_poin, 0) AS total_poin

        FROM t_kelas_siswa ks

        INNER JOIN t_siswa s
            ON ks.siswa_id = s.id

        INNER JOIN t_kelas k
            ON ks.kelas_id = k.id

        LEFT JOIN (
            SELECT
                siswa_id,
                kelas_id,
                COUNT(*) AS jumlah_pelanggaran,
                SUM(COALESCE(poin, 0)) AS total_poin

            FROM t_pelanggaran_siswa

            GROUP BY siswa_id, kelas_id

        ) rekap
            ON rekap.siswa_id = s.id
            AND rekap.kelas_id = k.id

        WHERE ks.kelas_id = ?
        AND ks.status_aktif = 1

        GROUP BY
            s.id,
            s.nama,
            k.nama,
            rekap.jumlah_pelanggaran,
            rekap.total_poin

        ORDER BY total_poin DESC, s.nama ASC
    ";

    $stmtRekap = mysqli_prepare($koneksi, $sqlRekap);

    if (!$stmtRekap) {
        die("Query rekap gagal: " . mysqli_error($koneksi));
    }

    mysqli_stmt_bind_param(
        $stmtRekap,
        "i",
        $kelasTerpilih
    );

    mysqli_stmt_execute($stmtRekap);

    $hasilRekap = mysqli_stmt_get_result($stmtRekap);

    $daftarRekap = mysqli_fetch_all(
        $hasilRekap,
        MYSQLI_ASSOC
    );

    mysqli_stmt_close($stmtRekap);

    // HITUNG RINGKASAN KELAS TERPILIH
    $totalSiswa = count($daftarRekap);

    foreach ($daftarRekap as $row) {

        $totalPelanggaran += (int) $row['jumlah_pelanggaran'];

        $totalPoin += (int) $row['total_poin'];
    }
}

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Rekap Poin - Sistem Informasi Pelanggaran Siswa</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- BOOTSTRAP ICONS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <link href="https://cdn.datatables.net/2.3.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
</head>

<body class="bg-light overflow-hidden">

<div class="container-fluid vh-100 overflow-hidden">

    <div class="row h-100 flex-nowrap">

        <!-- SIDEBAR -->
        <aside class="col-12 col-md-3 col-lg-2 bg-dark vh-100 overflow-auto position-sticky top-0 p-3">

            <!-- JUDUL SIDEBAR -->
            <div class="text-white mb-4 pt-3 pb-3 border-bottom border-secondary">

                <h5 class="fw-bold mb-2">
                    <i class="bi bi-shield-check me-2"></i>
                    Sistem Pelanggaran
                </h5>

                <small class="text-secondary">
                    Panel <?= $isWaliKelas && $role === 'guru' ? 'Wali Kelas' : ucfirst($role); ?>
                </small>

            </div>

            <!-- PROFIL PENGGUNA -->
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
                            <?= $labelRole; ?>
                        </small>

                    </div>

                </div>

            </div>

            <!-- MENU SIDEBAR -->
            <div class="text-secondary small fw-semibold mb-3">
                MENU UTAMA
            </div>

            <ul class="nav nav-pills flex-column gap-2">

                <?php foreach ($menu as $item): ?>

                    <?php
                    $halamanAktif = basename($item[1]) === 'rekap_poin.php';
                    ?>

                    <li class="nav-item">

                        <a
                            href="<?= htmlspecialchars($item[1], ENT_QUOTES, 'UTF-8'); ?>"
                            class="nav-link <?= $halamanAktif ? 'active' : 'text-white'; ?> rounded-2 py-2 px-3"
                            <?= $halamanAktif ? 'aria-current="page"' : ''; ?>
                        >
                            <?= htmlspecialchars($item[0], ENT_QUOTES, 'UTF-8'); ?>
                        </a>

                    </li>

                <?php endforeach; ?>

            </ul>

            <hr class="border-secondary my-4">

            <!-- LOGOUT -->
            <a
                href="logout.php"
                class="nav-link text-danger rounded-2 py-2 px-3"
            >
                <i class="bi bi-box-arrow-left me-2"></i>
                Logout
            </a>

        </aside>

        <!-- KONTEN UTAMA -->
        <main class="col-12 col-md-9 col-lg-10 vh-100 overflow-auto p-3 p-lg-5">

            <!-- HEADER HALAMAN -->
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-4 border-bottom">

                <div>

                    <div class="text-secondary small mb-2">
                        Dashboard / Rekap Poin
                    </div>

                    <h2 class="fw-bold mb-2">
                        Rekap Poin Pelanggaran Siswa
                    </h2>

                    <p class="text-secondary mb-0">
                        Informasi jumlah pelanggaran dan akumulasi poin setiap siswa.
                    </p>

                </div>

            </div>

            <!-- PILIH KELAS -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">

                <div class="card-body p-4">

                    <form method="GET" action="rekap_poin.php">

                        <label for="kelas_id" class="form-label fw-semibold">
                            <i class="bi bi-building me-2"></i>
                            Pilih Kelas
                        </label>

                        <div class="row g-2">

                            <div class="col-12 col-md-9">

                                <select
                                    name="kelas_id"
                                    id="kelas_id"
                                    class="form-select"
                                    required
                                >

                                    <option value="">
                                        -- Pilih Kelas Terlebih Dahulu --
                                    </option>

                                    <?php foreach ($daftarKelas as $kelas): ?>

                                        <option
                                            value="<?= (int) $kelas['id']; ?>"
                                            <?= $kelasTerpilih === (int) $kelas['id'] ? 'selected' : ''; ?>
                                        >
                                            <?= htmlspecialchars($kelas['nama'], ENT_QUOTES, 'UTF-8'); ?>
                                        </option>

                                    <?php endforeach; ?>

                                </select>

                            </div>

                            <div class="col-12 col-md-3">

                                <button
                                    type="submit"
                                    class="btn btn-dark w-100"
                                >
                                    <i class="bi bi-search me-2"></i>
                                    Tampilkan
                                </button>

                            </div>

                        </div>

                    </form>

                </div>

            </div>

            <?php if ($kelasTerpilih > 0): ?>

                <!-- RINGKASAN DATA -->
                <div class="row g-3 mb-4">

                    <div class="col-12 col-md-4">

                        <div class="card border-0 shadow-sm rounded-3 h-100">

                            <div class="card-body p-4">

                                <p class="text-secondary small mb-2">
                                    Jumlah Data Siswa
                                </p>

                                <h3 class="fw-bold mb-1">
                                    <?= $totalSiswa; ?>
                                </h3>

                                <p class="text-secondary small mb-0">
                                    Siswa dalam kelas <?= htmlspecialchars($namaKelasTerpilih, ENT_QUOTES, 'UTF-8'); ?>
                                </p>

                            </div>

                        </div>

                    </div>

                    <div class="col-12 col-md-4">

                        <div class="card border-0 shadow-sm rounded-3 h-100">

                            <div class="card-body p-4">

                                <p class="text-secondary small mb-2">
                                    Total Pelanggaran
                                </p>

                                <h3 class="fw-bold mb-1">
                                    <?= $totalPelanggaran; ?>
                                </h3>

                                <p class="text-secondary small mb-0">
                                    Seluruh catatan pelanggaran
                                </p>

                            </div>

                        </div>

                    </div>

                    <div class="col-12 col-md-4">

                        <div class="card border-0 shadow-sm rounded-3 h-100">

                            <div class="card-body p-4">

                                <p class="text-secondary small mb-2">
                                    Total Poin
                                </p>

                                <h3 class="fw-bold mb-1">
                                    <?= $totalPoin; ?>
                                </h3>

                                <p class="text-secondary small mb-0">
                                    Akumulasi poin pelanggaran
                                </p>

                            </div>

                        </div>

                    </div>

                </div>

                <!-- TABEL REKAP POIN -->
                <div class="card border-0 shadow-sm rounded-3 mb-4">

                    <div class="card-header bg-white border-0 p-4">

                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">

                            <div>

                                <h5 class="fw-bold mb-1">
                                    Daftar Rekap Poin Siswa
                                </h5>

                                <p class="text-secondary small mb-0">
                                    Rekap seluruh siswa kelas
                                    <?= htmlspecialchars($namaKelasTerpilih, ENT_QUOTES, 'UTF-8'); ?>.
                                </p>

                            </div>

                            <span class="badge text-bg-dark rounded-pill px-3 py-2">
                                <?= $totalSiswa; ?> Data Siswa
                            </span>

                        </div>

                    </div>

                    <div class="card-body p-4 pt-2">

                        <div class="table-responsive">

                            <table id="tabelRekapPoin" class="table table-hover table-bordered align-middle">

                                <thead class="table-light">

                                    <tr>
                                        <th class="text-center">No</th>
                                        <th>Nama Siswa</th>
                                        <th>Kelas</th>
                                        <th class="text-center">Jumlah Pelanggaran</th>
                                        <th class="text-center">Total Poin</th>
                                    </tr>

                                </thead>

                                <tbody>

                                    <?php $no = 1; ?>

                                    <?php foreach ($daftarRekap as $row): ?>

                                        <tr>

                                            <td class="text-center">
                                                <?= $no++; ?>
                                            </td>

                                            <td class="fw-semibold">
                                                <?= htmlspecialchars($row['nama_siswa'], ENT_QUOTES, 'UTF-8'); ?>
                                            </td>

                                            <td>
                                                <?= htmlspecialchars($row['nama_kelas'], ENT_QUOTES, 'UTF-8'); ?>
                                            </td>

                                            <td class="text-center">
                                                <?= (int) $row['jumlah_pelanggaran']; ?>
                                            </td>

                                            <td class="text-center">

                                                <span class="badge bg-danger rounded-pill px-3 py-2">
                                                    <?= (int) $row['total_poin']; ?> poin
                                                </span>

                                            </td>

                                        </tr>

                                    <?php endforeach; ?>

                                </tbody>

                            </table>

                        </div>

                        <div class="border-top pt-3 mt-3">

                            <p class="text-secondary small mb-0">
                                Data rekap menghitung seluruh catatan pelanggaran, baik yang berstatus Diproses maupun Selesai.
                            </p>

                        </div>

                    </div>

                </div>

            <?php else: ?>

                <div class="card border-0 shadow-sm rounded-3">

                    <div class="card-body text-center p-5">

                        <i class="bi bi-building text-secondary display-4"></i>

                        <h5 class="fw-bold mt-3">
                            Silakan Pilih Kelas
                        </h5>

                        <p class="text-secondary mb-0">
                            Pilih kelas terlebih dahulu untuk melihat rekap poin seluruh siswa.
                        </p>

                    </div>

                </div>

            <?php endif; ?>

            <!-- KEMBALI KE DASHBOARD -->
            <div class="d-flex justify-content-start mb-4 mt-4">

                <a href="dashboard.php" class="btn btn-secondary rounded-2 px-4">
                    Kembali ke Dashboard
                </a>

            </div>

            <!-- FOOTER -->
            <div class="text-secondary small mt-4 pt-3 border-top">

                <div class="d-flex flex-wrap justify-content-between gap-2">

                    <span>
                        Sistem Informasi Pelanggaran Siswa
                    </span>

                    <span>
                        Halaman Rekap Poin
                    </span>

                </div>

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

    if ($('#tabelRekapPoin').length) {

        $('#tabelRekapPoin').DataTable({

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

                emptyTable: "Belum ada siswa dalam kelas ini",

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
</script>

</body>
</html>
