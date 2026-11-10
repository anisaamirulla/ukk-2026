
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

$pesan = "";

$kelasTerpilih = (int)($_POST['kelas_id'] ?? 0);
$siswaTerpilih = (int)($_POST['siswa_id'] ?? 0);

/* ==========================================
   AMBIL DATA GURU YANG SEDANG LOGIN
========================================== */

$guruLogin = null;

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

    mysqli_stmt_close($stmtGuruLogin);
}

/* ==========================================
   AMBIL DATA KELAS AKTIF
========================================== */

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

/* ==========================================
   AMBIL DATA SISWA DAN PENEMPATANNYA
========================================== */

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

/* ==========================================
   AMBIL DATA JENIS PELANGGARAN
========================================== */

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

/* ==========================================
   AMBIL DATA GURU UNTUK ADMIN
========================================== */

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

/* ==========================================
   PROSES SIMPAN PELANGGARAN
========================================== */

if (isset($_POST['simpan'])) {

    $siswa_id = (int)($_POST['siswa_id'] ?? 0);
    $pelanggaran_id = (int)($_POST['pelanggaran_id'] ?? 0);

    // Guru otomatis menggunakan akun yang sedang login.
    // Admin tetap memilih guru secara manual.
    if ($role === 'guru') {
        $guru_id = (int)($guruLogin['id'] ?? 0);
    } else {
        $guru_id = (int)($_POST['guru_id'] ?? 0);
    }

    $tanggal = trim($_POST['tanggal'] ?? '');
    $keterangan = trim($_POST['keterangan'] ?? '');
    $tindakan = trim($_POST['tindakan'] ?? '');
    $status = trim($_POST['status'] ?? 'Pending');

    if ($role === 'guru' && !$guruLogin) {

        $pesan = "Akun guru belum terhubung dengan data guru. Silakan hubungi admin.";

    } elseif (
        $siswa_id <= 0 ||
        $pelanggaran_id <= 0 ||
        $guru_id <= 0 ||
        $tanggal === '' ||
        ($role === 'guru' && $kelasTerpilih <= 0)
    ) {

        $pesan = "Semua kolom wajib bertanda bintang harus diisi.";

    } elseif (!in_array($status, ['Pending', 'Diproses', 'Selesai'], true)) {

        $pesan = "Status pelanggaran tidak valid.";

    } else {

        /* ======================================
           AMBIL PENEMPATAN SISWA
        ====================================== */

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

        /* ======================================
           AMBIL JENIS PELANGGARAN
        ====================================== */

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

        /* ======================================
           AMBIL GURU
        ====================================== */

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

        /* ======================================
           VALIDASI DATA
        ====================================== */

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

            /* ======================================
               SIMPAN KE TABEL PELANGGARAN SISWA
            ====================================== */

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

/* ==========================================
   NAMA PENGGUNA
========================================== */

$nama = htmlspecialchars(
    $_SESSION['name'] ?? 'Pengguna',
    ENT_QUOTES,
    'UTF-8'
);
?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Tambah Pelanggaran - Sistem Informasi Pelanggaran Siswa</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

</head>

<body class="bg-light overflow-hidden">

<div class="container-fluid vh-100 overflow-hidden">

    <div class="row h-100 flex-nowrap">

        <!-- SIDEBAR -->
        <aside class="col-12 col-md-3 col-lg-2 bg-dark vh-100 overflow-auto position-sticky top-0 p-3">

            <div class="text-white mb-4 pt-3 pb-3 border-bottom border-secondary">

                <h5 class="fw-bold mb-2">
                    <i class="bi bi-shield-check me-2"></i>
                    Sistem Pelanggaran
                </h5>

                <small class="text-secondary">
                    Panel <?= strtoupper($role); ?>
                </small>

            </div>

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
                            <?= strtoupper($role); ?>
                        </small>

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

                <?php if ($role === 'admin'): ?>

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
                        <a href="wali_kelas.php" class="nav-link text-white rounded-2 py-2 px-3">
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

                <?php endif; ?>

                <li class="nav-item">
                    <a href="catatan_pelanggaran.php" class="nav-link active rounded-2 py-2 px-3">
                        Catatan Pelanggaran
                    </a>
                </li>

                <li class="nav-item">
                    <a href="tindakan.php" class="nav-link text-white rounded-2 py-2 px-3">
                        Tindakan
                    </a>
                </li>

                <li class="nav-item">
                    <a href="riwayat.php" class="nav-link text-white rounded-2 py-2 px-3">
                        Riwayat
                    </a>
                </li>

                <li class="nav-item">
                    <a href="rekap_poin.php" class="nav-link text-white rounded-2 py-2 px-3">
                        Rekap Poin
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

        <!-- KONTEN FORM -->
        <main class="col-12 col-md-9 col-lg-10 vh-100 overflow-auto p-3 p-lg-5">

            <div class="mb-4 pb-4 border-bottom">

                <h2 class="fw-bold mb-2">
                    Tambah Pelanggaran Siswa
                </h2>

                <p class="text-secondary mb-3">
                    Masukkan catatan pelanggaran siswa pada formulir berikut.
                </p>

                <a href="catatan_pelanggaran.php" class="btn btn-secondary btn-sm">
                    Kembali ke Catatan Pelanggaran
                </a>

            </div>

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

                            <form method="POST">

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

                                    <!-- TAHUN AJARAN OTOMATIS -->
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

                                    <!-- GURU PENCATAT OTOMATIS -->
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
                                            Keterangan
                                        </label>

                                        <textarea
                                            name="keterangan"
                                            id="keterangan"
                                            class="form-control"
                                            rows="3"
                                            placeholder="Masukkan keterangan pelanggaran"
                                        ><?= htmlspecialchars($_POST['keterangan'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>

                                    </div>

                                    <!-- TINDAKAN -->
                                    <div class="col-12">

                                        <label for="tindakan" class="form-label">
                                            Tindakan
                                        </label>

                                        <textarea
                                            name="tindakan"
                                            id="tindakan"
                                            class="form-control"
                                            rows="3"
                                            placeholder="Masukkan tindakan yang diberikan"
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

                                <div class="d-flex justify-content-end gap-2">

                                    <a href="catatan_pelanggaran.php" class="btn btn-secondary">
                                        Batal
                                    </a>

                                    <button
                                        type="submit"
                                        name="simpan"
                                        class="btn btn-primary"
                                    >
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

<!-- JAVASCRIPT FILTER KELAS, SISWA, DAN TAHUN AJARAN -->
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

});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
