
<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

$role = strtolower(trim($_SESSION['role'] ?? ''));

if (!in_array($role, ['admin', 'guru'], true)) {
    exit("Anda tidak memiliki akses");
}

require_once __DIR__ . "/config/koneksi.php";

/* NAMA PENGGUNA */
$nama = $_SESSION['name'] ?? 'Pengguna';

/* IDENTITAS GURU LOGIN */
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

        mysqli_stmt_bind_param(
            $cekGuru,
            "i",
            $_SESSION['id']
        );

        mysqli_stmt_execute($cekGuru);

        $hasilGuru = mysqli_stmt_get_result($cekGuru);
        $guru = mysqli_fetch_assoc($hasilGuru);

        mysqli_stmt_close($cekGuru);

        if ($guru) {

            $guru_id = (int) $guru['id'];
            $namaGuruLogin = $guru['nama'];
            $nama = $guru['nama'];

            /* PEMERIKSAAN WALI KELAS UNTUK MENU SIDEBAR */
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

                mysqli_stmt_bind_param(
                    $cekWali,
                    "i",
                    $guru_id
                );

                mysqli_stmt_execute($cekWali);

                $hasilWali = mysqli_stmt_get_result($cekWali);

                $isWaliKelas = mysqli_num_rows($hasilWali) > 0;

                mysqli_stmt_close($cekWali);
            }
        }
    }
}

/* MENGAMANKAN NAMA */
$nama = htmlspecialchars(
    $nama,
    ENT_QUOTES,
    'UTF-8'
);

/* LABEL ROLE */
if ($role === 'admin') {
    $labelRole = 'ADMIN';
} elseif ($isWaliKelas) {
    $labelRole = 'WALI KELAS';
} else {
    $labelRole = 'GURU';
}

/* AMBIL DATA RIWAYAT */

/*
   ADMIN:
   Menampilkan seluruh riwayat yang sudah selesai.

   GURU DAN WALI KELAS:
   Hanya menampilkan riwayat pelanggaran
   yang dicatat oleh guru yang sedang login.
*/

if ($role === 'admin') {

    $data = mysqli_query(
        $koneksi,
        "SELECT id, nama_siswa, nama_kelas,
                nama_pelanggaran, nama_guru,
                tanggal, keterangan, poin, tindakan, status
         FROM t_pelanggaran_siswa
         WHERE status = 'Selesai'
         ORDER BY tanggal DESC, id DESC"
    );

} else {

    $data = mysqli_prepare(
        $koneksi,
        "SELECT id, nama_siswa, nama_kelas,
                nama_pelanggaran, nama_guru,
                tanggal, keterangan, poin, tindakan, status
         FROM t_pelanggaran_siswa
         WHERE status = 'Selesai'
           AND LOWER(TRIM(nama_guru)) = LOWER(TRIM(?))
         ORDER BY tanggal DESC, id DESC"
    );

    if ($data) {

        mysqli_stmt_bind_param(
            $data,
            "s",
            $namaGuruLogin
        );

        mysqli_stmt_execute($data);

        $data = mysqli_stmt_get_result($data);
    }
}

/* PEMERIKSAAN DATA */
if (!$data) {
    die("Gagal mengambil data riwayat: " . mysqli_error($koneksi));
}

/* TOTAL RIWAYAT */
$totalRiwayat = mysqli_num_rows($data);

/* MENU SIDEBAR ADMIN */
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
    ['Catatan Pelanggaran', 'catatan_pelanggaran.php'],
    ['Tindakan', 'tindakan.php'],
    ['Riwayat', 'riwayat.php'],
    ['Rekap Poin', 'rekap_poin.php'],
    ['About Me', 'abautme.php']
];

/* MENU GURU BIASA */
$menuGuru = [
    ['Dashboard', 'dashboard.php'],
    ['Catatan Pelanggaran', 'catatan_pelanggaran.php'],
    ['Riwayat', 'riwayat.php'],
    ['Rekap Poin', 'rekap_poin.php'],
    ['About Me', 'abautme.php']
];

/* MENU WALI KELAS */
$menuWaliKelas = [
    ['Dashboard', 'dashboard.php'],
    ['Data Siswa di Kelasnya', 'walikelas/data_siswa.php'],
    ['Catat Pelanggaran', 'catatan_pelanggaran.php'],
    ['Riwayat Pelanggaran Siswa', 'riwayat.php'],
    ['Rekap Poin Siswa', 'rekap_poin.php'],
    ['Laporan Pelanggaran Kelas', 'walikelas/rekap_pelanggaran.php'],
    ['About Me', 'abautme.php']
];

/* MENENTUKAN MENU */
if ($role === 'admin') {
    $menu = $menuAdmin;
} elseif ($isWaliKelas) {
    $menu = $menuWaliKelas;
} else {
    $menu = $menuGuru;
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Riwayat Pelanggaran - Sistem Informasi Pelanggaran Siswa</title>

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
                    $menuRiwayat = (
                        $item[0] === 'Riwayat' ||
                        $item[0] === 'Riwayat Pelanggaran Siswa'
                    );

                    $menuDashboard = $item[0] === 'Dashboard';
                    ?>

                    <li class="nav-item">

                        <a
                            href="<?= htmlspecialchars($item[1], ENT_QUOTES, 'UTF-8'); ?>"
                            class="nav-link <?= $menuRiwayat ? 'active' : 'text-white'; ?> rounded-2 py-2 px-3"
                            <?= $menuRiwayat ? 'aria-current="page"' : ''; ?>
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
            <div class="mb-4 pb-4 border-bottom">

                <div class="text-secondary small mb-2">
                    Dashboard / Riwayat
                </div>

                <h2 class="fw-bold mb-2">
                    Riwayat Pelanggaran Siswa
                </h2>

                <p class="text-secondary mb-0">
                    Menampilkan catatan pelanggaran siswa yang sudah selesai ditangani.
                </p>

            </div>

            <!-- INFORMASI RIWAYAT -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">

                <div class="card-body p-4">

                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">

                        <div>

                            <h5 class="fw-bold mb-2">
                                Informasi Riwayat
                            </h5>

                            <p class="text-secondary small mb-0">
                                Jumlah seluruh catatan pelanggaran dengan status selesai.
                            </p>

                        </div>

                        <div class="text-end">

                            <div class="text-secondary small mb-1">
                                Total Riwayat Selesai
                            </div>

                            <h2 class="fw-bold mb-0">
                                <?= $totalRiwayat; ?>
                                <span class="fs-6 fw-normal text-secondary">Data</span>
                            </h2>

                        </div>

                    </div>

                </div>

            </div>

            <!-- TABEL RIWAYAT -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">

                <div class="card-header bg-white border-0 p-4">

                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">

                        <div>

                            <h5 class="fw-bold mb-1">
                                Daftar Riwayat Pelanggaran
                            </h5>

                            <p class="text-secondary small mb-0">
                                Berikut adalah daftar pelanggaran siswa yang telah diselesaikan.
                            </p>

                        </div>

                        <span class="badge text-bg-dark rounded-pill px-3 py-2">
                            <?= $totalRiwayat; ?> Data
                        </span>

                    </div>

                </div>

                <div class="card-body p-4 pt-2">

                    <div class="table-responsive">

                        <table id="tabelRiwayat" class="table table-hover table-bordered align-middle">

                            <thead class="table-light">

                                <tr>
                                    <th class="text-center">No.</th>
                                    <th>Nama Siswa</th>
                                    <th>Kelas</th>
                                    <th>Pelanggaran</th>
                                    <th>Guru</th>
                                    <th>Tanggal</th>
                                    <th>Keterangan</th>
                                    <th>Poin</th>
                                    <th>Tindakan</th>
                                    <th>Status</th>
                                </tr>

                            </thead>

                            <tbody>

                                <?php
                                $no = 1;

                                while ($row = mysqli_fetch_assoc($data)):
                                ?>

                                    <tr>

                                        <td class="text-center">
                                            <?= $no++; ?>
                                        </td>

                                        <td class="fw-semibold">
                                            <?= htmlspecialchars($row['nama_siswa'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($row['nama_kelas'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($row['nama_pelanggaran'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($row['nama_guru'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($row['tanggal'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($row['keterangan'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                        </td>

                                        <td class="text-center">
                                            <span class="badge bg-secondary">
                                                <?= (int)($row['poin'] ?? 0); ?> poin
                                            </span>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($row['tindakan'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                        </td>

                                        <td class="text-center">
                                            <span class="badge bg-success">
                                                Selesai
                                            </span>
                                        </td>

                                    </tr>

                                <?php endwhile; ?>

                            </tbody>

                        </table>

                    </div>

                    <div class="border-top pt-3 mt-3">

                        <p class="text-secondary small mb-0">
                            Total riwayat pelanggaran:
                            <strong><?= $totalRiwayat; ?></strong> data.
                        </p>

                    </div>

                </div>

            </div>

            <!-- KEMBALI KE DASHBOARD -->
            <div class="mb-4">

                <a
                    href="dashboard.php"
                    class="btn btn-secondary rounded-2 px-4"
                >
                    Kembali ke Dashboard
                </a>

            </div>

            <!-- FOOTER -->
            <div class="text-secondary small mt-4 pt-3 border-top">

                <div class="d-flex flex-wrap justify-content-between gap-2">

                    <span>
                        Sistem Informasi Manajemen Pelanggaran Siswa Muhammadiyah
                    </span>

                    <span>
                        Halaman Riwayat
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

    $('#tabelRiwayat').DataTable({

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

            emptyTable: "Belum ada riwayat pelanggaran",

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
