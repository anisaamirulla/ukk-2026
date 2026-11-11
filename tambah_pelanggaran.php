
<?php
/* 1. SESSION DAN HAK AKSES
   Memastikan pengguna sudah login dan memiliki role admin/guru.
*/
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

$role = strtolower(trim($_SESSION['role'] ?? ''));

if (!in_array($role, ['admin', 'guru'], true)) {
    exit("Anda tidak memiliki akses");
}

/* 2. KONEKSI DATABASE
   Menghubungkan halaman dengan database aplikasi.
*/
require_once __DIR__ . "/config/koneksi.php";

$pesan = "";

$kelasTerpilih = (int)($_POST['kelas_id'] ?? 0);
$siswaTerpilih = (int)($_POST['siswa_id'] ?? 0);

/* 3. DATA GURU LOGIN
   Mengambil identitas guru yang sedang masuk.
*/

$guruLogin = null;
$guru_id = 0;

if ($role === 'guru') {

    $user_id = (int)$_SESSION['id'];

    $stmtGuruLogin = mysqli_prepare(
        $koneksi,
        "SELECT id, nama
         FROM t_guru
         WHERE user_id = ?
           AND status_aktif = 1
         LIMIT 1"
    );

    if (!$stmtGuruLogin) {
        die("Query guru login gagal: " . mysqli_error($koneksi));
    }

    mysqli_stmt_bind_param($stmtGuruLogin, "i", $user_id);
    mysqli_stmt_execute($stmtGuruLogin);

    $hasilGuruLogin = mysqli_stmt_get_result($stmtGuruLogin);
    $guruLogin = mysqli_fetch_assoc($hasilGuruLogin);

    if ($guruLogin) {
        $guru_id = (int)$guruLogin['id'];
    }

    mysqli_stmt_close($stmtGuruLogin);
}

/* 4. PEMERIKSAAN WALI KELAS
   Menentukan apakah guru yang login juga merupakan wali kelas aktif.
*/

$isWaliKelas = false;

if ($role === 'guru' && $guru_id > 0) {

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

/* 5. DATA KELAS
   Mengambil daftar kelas yang memiliki penempatan siswa aktif.
*/

$dataKelas = [];

$queryKelas = mysqli_query(
    $koneksi,
    "SELECT DISTINCT
        k.id,
        k.nama
     FROM t_kelas_siswa ks
     INNER JOIN t_kelas k ON k.id = ks.kelas_id
     WHERE ks.status_aktif = 1
     ORDER BY k.nama ASC"
);

if (!$queryKelas) {
    die("Gagal mengambil data kelas: " . mysqli_error($koneksi));
}

while ($kelas = mysqli_fetch_assoc($queryKelas)) {
    $dataKelas[] = $kelas;
}

/* 6. DATA SISWA
   Mengambil siswa beserta kelas dan tahun ajarannya.
*/

$dataSiswa = [];

if ($role === 'guru') {

    $sqlSiswa = "
        SELECT
            ks.siswa_id,
            s.nama AS nama_siswa,
            ks.kelas_id,
            k.nama AS nama_kelas,
            ks.tahun_ajaran_id,
            ta.nama AS nama_tahun_ajaran
        FROM t_kelas_siswa ks
        INNER JOIN t_siswa s ON s.id = ks.siswa_id
        INNER JOIN t_kelas k ON k.id = ks.kelas_id
        INNER JOIN t_tahun_ajaran ta ON ta.id = ks.tahun_ajaran_id
        WHERE ks.status_aktif = 1
          AND ks.id = (
              SELECT MAX(ks2.id)
              FROM t_kelas_siswa ks2
              WHERE ks2.siswa_id = ks.siswa_id
                AND ks2.kelas_id = ks.kelas_id
                AND ks2.status_aktif = 1
          )
        ORDER BY s.nama ASC
    ";

} else {

    $sqlSiswa = "
        SELECT
            ks.siswa_id,
            s.nama AS nama_siswa,
            ks.kelas_id,
            k.nama AS nama_kelas,
            ks.tahun_ajaran_id,
            ta.nama AS nama_tahun_ajaran
        FROM t_kelas_siswa ks
        INNER JOIN t_siswa s ON s.id = ks.siswa_id
        INNER JOIN t_kelas k ON k.id = ks.kelas_id
        INNER JOIN t_tahun_ajaran ta ON ta.id = ks.tahun_ajaran_id
        WHERE ks.status_aktif = 1
          AND ks.id = (
              SELECT MAX(ks2.id)
              FROM t_kelas_siswa ks2
              WHERE ks2.siswa_id = ks.siswa_id
                AND ks2.status_aktif = 1
          )
        ORDER BY s.nama ASC
    ";
}

$querySiswa = mysqli_query($koneksi, $sqlSiswa);

if (!$querySiswa) {
    die("Gagal mengambil data siswa: " . mysqli_error($koneksi));
}

while ($siswaData = mysqli_fetch_assoc($querySiswa)) {
    $dataSiswa[] = $siswaData;
}

/* 7. DATA JENIS PELANGGARAN
   Mengambil jenis pelanggaran aktif beserta poinnya.
*/

$queryPelanggaran = mysqli_query(
    $koneksi,
    "SELECT id, pelanggaran_kategori_id, nama, poin
     FROM t_pelanggaran
     WHERE status_aktif = 1
     ORDER BY nama ASC"
);

if (!$queryPelanggaran) {
    die("Gagal mengambil jenis pelanggaran: " . mysqli_error($koneksi));
}

/* 8. DATA GURU
   Mengambil daftar guru aktif untuk pilihan admin.
*/

$queryGuru = mysqli_query(
    $koneksi,
    "SELECT id, nama
     FROM t_guru
     WHERE status_aktif = 1
     ORDER BY nama ASC"
);

if (!$queryGuru) {
    die("Gagal mengambil data guru: " . mysqli_error($koneksi));
}

/* 9. PROSES SIMPAN PELANGGARAN
   Memeriksa data, mengambil informasi terkait, lalu menyimpan ke database.
*/

if (isset($_POST['simpan'])) {

    $siswa_id = (int)($_POST['siswa_id'] ?? 0);
    $pelanggaran_id = (int)($_POST['pelanggaran_id'] ?? 0);

    if ($role === 'guru') {
        $guru_id = (int)($guruLogin['id'] ?? 0);
    } else {
        $guru_id = (int)($_POST['guru_id'] ?? 0);
    }

    $tanggal = trim($_POST['tanggal'] ?? '');
    $keterangan = trim($_POST['keterangan'] ?? '');
    $tindakan = trim($_POST['tindakan'] ?? '');
    $status = trim($_POST['status'] ?? '');

    if ($role === 'guru' && !$guruLogin) {

        $pesan = "Akun guru belum terhubung dengan data guru. Silakan hubungi admin.";

    } elseif (
        $siswa_id <= 0 ||
        $pelanggaran_id <= 0 ||
        $guru_id <= 0 ||
        $tanggal === '' ||
        $keterangan === '' ||
        $tindakan === '' ||
        $status === '' ||
        ($role === 'guru' && $kelasTerpilih <= 0)
    ) {

        $pesan = "Formulir belum lengkap. Silakan isi semua kolom terlebih dahulu.";

    } elseif (
        !DateTime::createFromFormat('Y-m-d', $tanggal) ||
        DateTime::createFromFormat('Y-m-d', $tanggal)->format('Y-m-d') !== $tanggal
    ) {

        $pesan = "Tanggal pelanggaran tidak valid.";

    } elseif (!in_array($status, ['Pending', 'Diproses', 'Selesai'], true)) {

        $pesan = "Status pelanggaran tidak valid.";

    } else {

        /* 9A. AMBIL PENEMPATAN SISWA
           Memastikan siswa memiliki penempatan kelas aktif.
        */

        if ($role === 'guru') {

            $stmtSiswa = mysqli_prepare(
                $koneksi,
                "SELECT
                    ks.tahun_ajaran_id,
                    ks.kelas_id,
                    s.nama AS nama_siswa,
                    k.nama AS nama_kelas
                 FROM t_kelas_siswa ks
                 INNER JOIN t_siswa s ON s.id = ks.siswa_id
                 INNER JOIN t_kelas k ON k.id = ks.kelas_id
                 WHERE ks.siswa_id = ?
                   AND ks.kelas_id = ?
                   AND ks.status_aktif = 1
                 ORDER BY ks.id DESC
                 LIMIT 1"
            );

            if (!$stmtSiswa) {
                die("Query siswa gagal: " . mysqli_error($koneksi));
            }

            mysqli_stmt_bind_param(
                $stmtSiswa,
                "ii",
                $siswa_id,
                $kelasTerpilih
            );

        } else {

            $stmtSiswa = mysqli_prepare(
                $koneksi,
                "SELECT
                    ks.tahun_ajaran_id,
                    ks.kelas_id,
                    s.nama AS nama_siswa,
                    k.nama AS nama_kelas
                 FROM t_kelas_siswa ks
                 INNER JOIN t_siswa s ON s.id = ks.siswa_id
                 INNER JOIN t_kelas k ON k.id = ks.kelas_id
                 WHERE ks.siswa_id = ?
                   AND ks.status_aktif = 1
                 ORDER BY ks.id DESC
                 LIMIT 1"
            );

            if (!$stmtSiswa) {
                die("Query siswa gagal: " . mysqli_error($koneksi));
            }

            mysqli_stmt_bind_param(
                $stmtSiswa,
                "i",
                $siswa_id
            );
        }

        mysqli_stmt_execute($stmtSiswa);

        $hasilSiswa = mysqli_stmt_get_result($stmtSiswa);
        $siswa = mysqli_fetch_assoc($hasilSiswa);

        mysqli_stmt_close($stmtSiswa);

        /* 9B. AMBIL JENIS PELANGGARAN
           Mengambil nama kategori pelanggaran dan jumlah poin.
        */

        $stmtPelanggaran = mysqli_prepare(
            $koneksi,
            "SELECT id, pelanggaran_kategori_id, nama, poin
             FROM t_pelanggaran
             WHERE id = ?
               AND status_aktif = 1
             LIMIT 1"
        );

        if (!$stmtPelanggaran) {
            die("Query pelanggaran gagal: " . mysqli_error($koneksi));
        }

        mysqli_stmt_bind_param(
            $stmtPelanggaran,
            "i",
            $pelanggaran_id
        );

        mysqli_stmt_execute($stmtPelanggaran);

        $hasilPelanggaran = mysqli_stmt_get_result($stmtPelanggaran);
        $pelanggaran = mysqli_fetch_assoc($hasilPelanggaran);

        mysqli_stmt_close($stmtPelanggaran);

        /* 9C. AMBIL DATA GURU
           Memastikan guru pencatat terdaftar dan aktif.
        */

        $stmtGuru = mysqli_prepare(
            $koneksi,
            "SELECT id, nama
             FROM t_guru
             WHERE id = ?
               AND status_aktif = 1
             LIMIT 1"
        );

        if (!$stmtGuru) {
            die("Query guru gagal: " . mysqli_error($koneksi));
        }

        mysqli_stmt_bind_param(
            $stmtGuru,
            "i",
            $guru_id
        );

        mysqli_stmt_execute($stmtGuru);

        $hasilGuru = mysqli_stmt_get_result($stmtGuru);
        $guru = mysqli_fetch_assoc($hasilGuru);

        mysqli_stmt_close($stmtGuru);

        /* 9D. VALIDASI DATA
           Memastikan data siswa, pelanggaran, dan guru tersedia.
        */

        if (!$siswa) {

            $pesan = "Siswa tidak ditemukan pada kelas yang dipilih atau belum memiliki penempatan aktif.";

        } elseif (!$pelanggaran) {

            $pesan = "Jenis pelanggaran tidak ditemukan.";

        } elseif (!$guru) {

            $pesan = "Data guru tidak ditemukan.";

        } else {

            $tahun_ajaran_id = (int)$siswa['tahun_ajaran_id'];
            $kelas_id = (int)$siswa['kelas_id'];

            $nama_siswa = $siswa['nama_siswa'];
            $nama_kelas = $siswa['nama_kelas'];

            $nama_pelanggaran = $pelanggaran['nama'];
            $pelanggaran_kategori_id = (int)$pelanggaran['pelanggaran_kategori_id'];
            $poin = (int)$pelanggaran['poin'];

            $nama_guru = $guru['nama'];

            /* 9E. SIMPAN KE DATABASE
               Memasukkan catatan pelanggaran ke tabel t_pelanggaran_siswa.
            */

            $stmtSimpan = mysqli_prepare(
                $koneksi,
                "INSERT INTO t_pelanggaran_siswa (
                    tahun_ajaran_id,
                    siswa_id,
                    nama_siswa,
                    kelas_id,
                    nama_kelas,
                    pelanggaran_id,
                    nama_pelanggaran,
                    pelanggaran_kategori_id,
                    guru_id,
                    nama_guru,
                    tanggal,
                    keterangan,
                    poin,
                    tindakan,
                    status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );

            if ($stmtSimpan) {

                mysqli_stmt_bind_param(
                    $stmtSimpan,
                    "iisisisiisssiss",
                    $tahun_ajaran_id,
                    $siswa_id,
                    $nama_siswa,
                    $kelas_id,
                    $nama_kelas,
                    $pelanggaran_id,
                    $nama_pelanggaran,
                    $pelanggaran_kategori_id,
                    $guru_id,
                    $nama_guru,
                    $tanggal,
                    $keterangan,
                    $poin,
                    $tindakan,
                    $status
                );

                if (mysqli_stmt_execute($stmtSimpan)) {

                    mysqli_stmt_close($stmtSimpan);

                    header("Location: catatan_pelanggaran.php?pesan=berhasil");
                    exit;

                } else {

                    $pesan = "Gagal menyimpan catatan: " . mysqli_stmt_error($stmtSimpan);

                    mysqli_stmt_close($stmtSimpan);
                }

            } else {

                $pesan = "Query penyimpanan gagal: " . mysqli_error($koneksi);
            }
        }
    }
}

/* 10. NAMA PENGGUNA
   Menampilkan nama pengguna yang sedang login.
*/

$nama = htmlspecialchars(
    $_SESSION['name'] ?? 'Pengguna',
    ENT_QUOTES,
    'UTF-8'
);

/* 11. MENU SIDEBAR
   Menampilkan menu berbeda sesuai role admin, wali kelas, atau guru.
*/

if ($role === 'admin') {

    $menu = [
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

    $labelRole = 'ADMIN';
    $panelRole = 'Admin';

} elseif ($isWaliKelas) {

    $menu = [
        ['Dashboard', 'dashboard.php'],
        ['Data Siswa di Kelasnya', 'walikelas/data_siswa.php'],
        ['Catat Pelanggaran', 'tambah_pelanggaran.php'],
        ['Riwayat Pelanggaran Siswa', 'riwayat.php'],
        ['Rekap Poin Siswa', 'rekap_poin.php'],
        ['Laporan Pelanggaran Kelas', 'walikelas/rekap_pelanggaran.php'],
        ['About Me', 'abautme.php']
    ];

    $labelRole = 'WALI KELAS';
    $panelRole = 'Wali Kelas';

} else {

    $menu = [
        ['Dashboard', 'dashboard.php'],
        ['Catatan Pelanggaran', 'catatan_pelanggaran.php'],
        ['Riwayat', 'riwayat.php'],
        ['Rekap Poin', 'rekap_poin.php'],
        ['About Me', 'abautme.php']
    ];

    $labelRole = 'GURU';
    $panelRole = 'Guru';
}
?>

<!DOCTYPE html>
<html lang="id">

<head>

    <!-- 12. PENGATURAN HALAMAN
         Mengatur judul, ukuran layar, Bootstrap, dan ikon.
    -->

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Tambah Pelanggaran - Sistem Informasi Pelanggaran Siswa</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

</head>

<body class="bg-light overflow-hidden">

<!-- 13. KERANGKA HALAMAN
     Membuat area sidebar dan area konten utama.
-->

<div class="container-fluid vh-100 overflow-hidden">

    <div class="row h-100 flex-nowrap">

        <!-- 14. SIDEBAR
             Menampilkan identitas pengguna, menu, dan tombol logout.
        -->

        <aside class="col-12 col-md-3 col-lg-2 bg-dark vh-100 overflow-auto position-sticky top-0 p-3">

            <div class="text-white mb-4 pt-3 pb-3 border-bottom border-secondary">

                <h5 class="fw-bold mb-2">
                    <i class="bi bi-shield-check me-2"></i>
                    Sistem Pelanggaran
                </h5>

                <small class="text-secondary">
                    Panel <?= htmlspecialchars($panelRole, ENT_QUOTES, 'UTF-8'); ?>
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

            <div class="text-secondary small fw-semibold mb-3">
                MENU UTAMA
            </div>

            <ul class="nav nav-pills flex-column gap-2">

                <?php foreach ($menu as $item): ?>

                    <?php
                    $aktif = (
                        $item[0] === 'Catat Pelanggaran'
                        || $item[0] === 'Catatan Pelanggaran'
                    );
                    ?>

                    <li class="nav-item">

                        <a
                            href="<?= htmlspecialchars($item[1], ENT_QUOTES, 'UTF-8'); ?>"
                            class="nav-link <?= $aktif ? 'active' : 'text-white'; ?> rounded-2 py-2 px-3"
                            <?= $aktif ? 'aria-current="page"' : ''; ?>
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

        <!-- 15. KONTEN UTAMA
             Menampilkan breadcrumb dan formulir catatan pelanggaran.
        -->

        <main class="col-12 col-md-9 col-lg-10 vh-100 overflow-auto p-3 p-lg-5">

            <div class="mb-4 pb-4 border-bottom">

                <div class="text-secondary small mb-2">
                    Dashboard / Catat Pelanggaran
                </div>

                <h2 class="fw-bold mb-2">
                    Catat Pelanggaran Siswa
                </h2>

                <p class="text-secondary mb-0">
                    Masukkan catatan pelanggaran siswa pada formulir berikut.
                </p>

            </div>

            <!-- 16. PESAN PHP
                 Menampilkan pesan jika proses penyimpanan mengalami masalah.
            -->

            <?php if ($pesan !== ''): ?>

                <div class="alert alert-danger alert-dismissible fade show" role="alert">

                    <?= htmlspecialchars($pesan, ENT_QUOTES, 'UTF-8'); ?>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"
                    ></button>

                </div>

            <?php endif; ?>

            <!-- 17. KARTU FORMULIR
                 Membungkus seluruh kolom input catatan pelanggaran.
            -->

            <div class="row justify-content-center">

                <div class="col-12 col-xl-10">

                    <div class="card border-0 shadow-sm rounded-3">

                        <div class="card-header bg-white p-3">

                            <h5 class="text-primary fw-bold mb-1">
                                Formulir Catatan Pelanggaran
                            </h5>

                            <small class="text-secondary">
                                Lengkapi informasi pelanggaran siswa.
                            </small>

                        </div>

                        <div class="card-body p-4">

                            <form method="POST" id="formPelanggaran" novalidate>

                                <div class="row g-3">

                                    <!-- PILIH KELAS KHUSUS GURU -->

                                    <?php if ($role === 'guru'): ?>

                                        <div class="col-md-6">

                                            <label for="kelas_id" class="form-label">
                                                Kelas <span class="text-danger">*</span>
                                            </label>

                                            <select
                                                name="kelas_id"
                                                id="kelas_id"
                                                class="form-select"
                                                required
                                            >

                                                <option value="">
                                                    -- Pilih Kelas --
                                                </option>

                                                <?php foreach ($dataKelas as $kelas): ?>

                                                    <option
                                                        value="<?= (int)$kelas['id']; ?>"
                                                        <?= $kelasTerpilih === (int)$kelas['id'] ? 'selected' : ''; ?>
                                                    >
                                                        <?= htmlspecialchars($kelas['nama'], ENT_QUOTES, 'UTF-8'); ?>
                                                    </option>

                                                <?php endforeach; ?>

                                            </select>

                                        </div>

                                    <?php endif; ?>

                                    <!-- NAMA SISWA -->

                                    <div class="col-md-6">

                                        <label for="siswa_id" class="form-label">
                                            Nama Siswa <span class="text-danger">*</span>
                                        </label>

                                        <select
                                            name="siswa_id"
                                            id="siswa_id"
                                            class="form-select"
                                            required
                                            <?= ($role === 'guru' && $kelasTerpilih <= 0) ? 'disabled' : ''; ?>
                                        >

                                            <option value="">
                                                -- Pilih Siswa --
                                            </option>

                                            <?php foreach ($dataSiswa as $siswaData): ?>

                                                <option
                                                    value="<?= (int)$siswaData['siswa_id']; ?>"
                                                    data-kelas="<?= (int)$siswaData['kelas_id']; ?>"
                                                    data-tahun="<?= (int)$siswaData['tahun_ajaran_id']; ?>"
                                                    data-nama-tahun="<?= htmlspecialchars($siswaData['nama_tahun_ajaran'], ENT_QUOTES, 'UTF-8'); ?>"
                                                    <?= $siswaTerpilih === (int)$siswaData['siswa_id'] ? 'selected' : ''; ?>
                                                >
                                                    <?= htmlspecialchars($siswaData['nama_siswa'], ENT_QUOTES, 'UTF-8'); ?>
                                                </option>

                                            <?php endforeach; ?>

                                        </select>

                                        <?php if ($role === 'guru'): ?>

                                            <small class="text-secondary">
                                                Nama siswa otomatis menyesuaikan kelas yang dipilih.
                                            </small>

                                        <?php endif; ?>

                                    </div>

                                    <!-- TAHUN AJARAN -->

                                    <div class="col-md-6">

                                        <label for="tahun_ajaran" class="form-label">
                                            Tahun Ajaran
                                        </label>

                                        <input
                                            type="text"
                                            id="tahun_ajaran"
                                            class="form-control"
                                            placeholder="Otomatis muncul setelah memilih siswa"
                                            readonly
                                        >

                                        <small class="text-secondary">
                                            Tahun ajaran mengikuti penempatan siswa yang dipilih.
                                        </small>

                                    </div>

                                    <!-- JENIS PELANGGARAN -->

                                    <div class="col-md-6">

                                        <label for="pelanggaran_id" class="form-label">
                                            Jenis Pelanggaran <span class="text-danger">*</span>
                                        </label>

                                        <select
                                            name="pelanggaran_id"
                                            id="pelanggaran_id"
                                            class="form-select"
                                            required
                                        >

                                            <option value="">
                                                -- Pilih Pelanggaran --
                                            </option>

                                            <?php while ($pelanggaran = mysqli_fetch_assoc($queryPelanggaran)): ?>

                                                <option value="<?= (int)$pelanggaran['id']; ?>">
                                                    <?= htmlspecialchars($pelanggaran['nama'], ENT_QUOTES, 'UTF-8'); ?>
                                                    - <?= (int)$pelanggaran['poin']; ?> poin
                                                </option>

                                            <?php endwhile; ?>

                                        </select>

                                    </div>

                                    <!-- GURU PENCATAT -->

                                    <div class="col-md-6">

                                        <label for="guru_id" class="form-label">
                                            Guru Pencatat <span class="text-danger">*</span>
                                        </label>

                                        <?php if ($role === 'guru'): ?>

                                            <input
                                                type="text"
                                                id="guru_id"
                                                class="form-control"
                                                value="<?= htmlspecialchars($guruLogin['nama'] ?? 'Data guru tidak ditemukan', ENT_QUOTES, 'UTF-8'); ?>"
                                                readonly
                                            >

                                            <small class="text-secondary">
                                                Otomatis mengikuti akun guru yang sedang login.
                                            </small>

                                        <?php else: ?>

                                            <select
                                                name="guru_id"
                                                id="guru_id"
                                                class="form-select"
                                                required
                                            >

                                                <option value="">
                                                    -- Pilih Guru --
                                                </option>

                                                <?php while ($guru = mysqli_fetch_assoc($queryGuru)): ?>

                                                    <option value="<?= (int)$guru['id']; ?>">
                                                        <?= htmlspecialchars($guru['nama'], ENT_QUOTES, 'UTF-8'); ?>
                                                    </option>

                                                <?php endwhile; ?>

                                            </select>

                                        <?php endif; ?>

                                    </div>

                                    <!-- TANGGAL -->

                                    <div class="col-md-6">

                                        <label for="tanggal" class="form-label">
                                            Tanggal Pelanggaran <span class="text-danger">*</span>
                                        </label>

                                        <input
                                            type="date"
                                            name="tanggal"
                                            id="tanggal"
                                            class="form-control"
                                            value="<?= htmlspecialchars($_POST['tanggal'] ?? date('Y-m-d'), ENT_QUOTES, 'UTF-8'); ?>"
                                            required
                                        >

                                    </div>

                                    <!-- KETERANGAN -->

                                    <div class="col-12">

                                        <label for="keterangan" class="form-label">
                                            Keterangan <span class="text-danger">*</span>
                                        </label>

                                        <textarea
                                            name="keterangan"
                                            id="keterangan"
                                            class="form-control"
                                            rows="3"
                                            placeholder="Masukkan keterangan pelanggaran"
                                            required
                                        ><?= htmlspecialchars($_POST['keterangan'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>

                                    </div>

                                    <!-- TINDAKAN -->

                                    <div class="col-12">

                                        <label for="tindakan" class="form-label">
                                            Tindakan <span class="text-danger">*</span>
                                        </label>

                                        <textarea
                                            name="tindakan"
                                            id="tindakan"
                                            class="form-control"
                                            rows="3"
                                            placeholder="Masukkan tindakan yang diberikan"
                                            required
                                        ><?= htmlspecialchars($_POST['tindakan'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>

                                    </div>

                                    <!-- STATUS -->

                                    <div class="col-md-6">

                                        <label for="status" class="form-label">
                                            Status <span class="text-danger">*</span>
                                        </label>

                                        <select
                                            name="status"
                                            id="status"
                                            class="form-select"
                                            required
                                        >

                                            <option value="Pending"
                                                <?= ($_POST['status'] ?? 'Pending') === 'Pending' ? 'selected' : ''; ?>>
                                                Pending
                                            </option>

                                            <option value="Diproses"
                                                <?= ($_POST['status'] ?? '') === 'Diproses' ? 'selected' : ''; ?>>
                                                Diproses
                                            </option>

                                            <option value="Selesai"
                                                <?= ($_POST['status'] ?? '') === 'Selesai' ? 'selected' : ''; ?>>
                                                Selesai
                                            </option>

                                        </select>

                                    </div>

                                </div>

                                <hr class="my-4">

                                <!-- TOMBOL FORMULIR -->

                                <div class="d-flex justify-content-end gap-2">

                                    <a href="catatan_pelanggaran.php" class="btn btn-secondary">
                                        Batal
                                    </a>

                                    <button
                                        type="submit"
                                        name="simpan"
                                        class="btn btn-primary"
                                    >
                                        <i class="bi bi-save me-1"></i>
                                        Simpan Pelanggaran
                                    </button>

                                </div>

                                <p class="text-secondary small mt-3 mb-0">
                                    <span class="text-danger">*</span>
                                    Kolom bertanda bintang wajib diisi.
                                </p>

                            </form>

                        </div>

                    </div>

                </div>

            </div>

            <div class="text-secondary small mt-5 pt-3 border-top">
                Sistem Informasi Pelanggaran Siswa
            </div>

        </main>

    </div>

</div>

<!-- 18. JAVASCRIPT FILTER SISWA
     Mengatur pilihan siswa berdasarkan kelas dan menampilkan tahun ajaran.
-->

<script>
document.addEventListener('DOMContentLoaded', function () {

    const pilihKelas = document.getElementById('kelas_id');
    const pilihSiswa = document.getElementById('siswa_id');
    const tahunAjaran = document.getElementById('tahun_ajaran');

    const semuaSiswa = Array.from(pilihSiswa.options).slice(1);

    function tampilkanTahunAjaran() {

        const siswaDipilih = pilihSiswa.options[pilihSiswa.selectedIndex];

        if (
            siswaDipilih &&
            siswaDipilih.value !== '' &&
            siswaDipilih.dataset.namaTahun
        ) {

            tahunAjaran.value = siswaDipilih.dataset.namaTahun;

        } else {

            tahunAjaran.value = '';

        }
    }

    function filterSiswa() {

        if (!pilihKelas) {
            return;
        }

        const kelasDipilih = pilihKelas.value;

        pilihSiswa.innerHTML = '';

        const optionAwal = document.createElement('option');

        optionAwal.value = '';

        optionAwal.textContent = kelasDipilih
            ? '-- Pilih Siswa --'
            : '-- Pilih Kelas Terlebih Dahulu --';

        pilihSiswa.appendChild(optionAwal);

        tahunAjaran.value = '';

        if (kelasDipilih === '') {

            pilihSiswa.disabled = true;
            return;

        }

        semuaSiswa.forEach(function (option) {

            if (option.dataset.kelas === kelasDipilih) {
                pilihSiswa.appendChild(option.cloneNode(true));
            }

        });

        if (pilihSiswa.options.length === 1) {

            optionAwal.textContent = '-- Tidak Ada Siswa Di Kelas Ini --';

            pilihSiswa.disabled = true;

            return;

        }

        pilihSiswa.disabled = false;

    }

    if (pilihKelas) {

        pilihKelas.addEventListener('change', function () {
            filterSiswa();
        });

        filterSiswa();

        const siswaSebelumnya = <?= json_encode((string)$siswaTerpilih); ?>;

        if (siswaSebelumnya !== '') {
            pilihSiswa.value = siswaSebelumnya;
        }

    }

    pilihSiswa.addEventListener('change', function () {
        tampilkanTahunAjaran();
    });

    tampilkanTahunAjaran();

    /* 19. VALIDASI FORMULIR
       Memberi tanda merah pada kolom yang belum diisi.
       Tanda merah hilang setelah kolom diisi.
    */

    const form = document.getElementById('formPelanggaran');
    const kolomWajib = form.querySelectorAll('[required]');

    form.addEventListener('submit', function (event) {

        let adaYangKosong = false;

        kolomWajib.forEach(function (kolom) {

            // Lewati kolom yang sedang dinonaktifkan.
            if (kolom.disabled) {
                return;
            }

            if (kolom.value.trim() === '') {

                kolom.classList.add('is-invalid');
                adaYangKosong = true;

            } else {

                kolom.classList.remove('is-invalid');

            }

        });

        // Mencegah formulir disimpan jika masih ada kolom kosong.
        if (adaYangKosong) {
            event.preventDefault();
        }

    });

    // Hilangkan tanda merah ketika kolom mulai diisi.
    kolomWajib.forEach(function (kolom) {

        kolom.addEventListener('input', function () {

            if (kolom.value.trim() !== '') {
                kolom.classList.remove('is-invalid');
            }

        });

        kolom.addEventListener('change', function () {

            if (kolom.value.trim() !== '') {
                kolom.classList.remove('is-invalid');
            }

        });

    });

});
</script>

<!-- 20. BOOTSTRAP JAVASCRIPT
     Mengaktifkan fungsi komponen Bootstrap seperti tombol tutup pesan.
-->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
