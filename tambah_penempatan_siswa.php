
<?php
session_start();
require_once __DIR__ . "/config/koneksi.php";

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

$role = strtolower(trim($_SESSION['role'] ?? ''));

if ($role !== 'admin') {
    exit("Anda tidak memiliki akses");
}

function aman($nilai) {
    return htmlspecialchars((string)($nilai ?? ''), ENT_QUOTES, 'UTF-8');
}

$nama = aman($_SESSION['name'] ?? 'Pengguna');
$pesan_error = '';

/* AMBIL DATA REFERENSI DARI DATABASE */

/* HANYA MENAMPILKAN SISWA YANG BELUM MEMILIKI PENEMPATAN KELAS */
$query_siswa = mysqli_query(
    $koneksi,
    "SELECT s.id, s.nisn, s.nama
     FROM t_siswa s
     WHERE NOT EXISTS (
         SELECT 1
         FROM t_kelas_siswa ks
         WHERE ks.siswa_id = s.id
     )
     ORDER BY s.nama ASC"
);

$query_kelas = mysqli_query(
    $koneksi,
    "SELECT id, nama FROM t_kelas ORDER BY nama ASC"
);

/* HANYA MENGAMBIL TAHUN AJARAN YANG AKTIF */
$query_tahun = mysqli_query(
    $koneksi,
    "SELECT id, nama
     FROM t_tahun_ajaran
     WHERE status_aktif = 1
     ORDER BY id DESC"
);

if (!$query_siswa || !$query_kelas || !$query_tahun) {
    die("Gagal mengambil data: " . mysqli_error($koneksi));
}

// DATA SISWA
$siswa_list = [];
$siswa_map = [];

while ($row = mysqli_fetch_assoc($query_siswa)) {
    $siswa_list[] = $row;
    $siswa_map[(int)$row['id']] = $row;
}

// DATA KELAS
$kelas_list = [];
$kelas_map = [];

while ($row = mysqli_fetch_assoc($query_kelas)) {
    $kelas_list[] = $row;
    $kelas_map[(int)$row['id']] = $row['nama'];
}

// DATA TAHUN AJARAN
$tahun_list = [];
$tahun_map = [];

while ($row = mysqli_fetch_assoc($query_tahun)) {
    $tahun_list[] = $row;
    $tahun_map[(int)$row['id']] = $row['nama'];
}

/* PROSES TAMBAH DATA */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $siswa_id = (int)($_POST['siswa_id'] ?? 0);
    $kelas_id = (int)($_POST['kelas_id'] ?? 0);
    $tahun_id = (int)($_POST['tahun_ajaran_id'] ?? 0);

    $tanggal_mulai = $_POST['tanggal_mulai'] ?? '';
    $tanggal_selesai = $_POST['tanggal_selesai'] ?? '';

    $status = (int)($_POST['status_aktif'] ?? 1);

    $valid_mulai = DateTime::createFromFormat('!Y-m-d', $tanggal_mulai);
    $valid_selesai = DateTime::createFromFormat('!Y-m-d', $tanggal_selesai);

    $tanggal_valid = $valid_mulai &&
        $valid_mulai->format('Y-m-d') === $tanggal_mulai &&
        $valid_selesai &&
        $valid_selesai->format('Y-m-d') === $tanggal_selesai &&
        $tanggal_selesai >= $tanggal_mulai;

    /* CEK ULANG APAKAH SISWA SUDAH MEMILIKI KELAS */
    $cek_penempatan = mysqli_prepare(
        $koneksi,
        "SELECT id FROM t_kelas_siswa WHERE siswa_id = ? LIMIT 1"
    );

    if (!$cek_penempatan) {
        die("Gagal memeriksa penempatan: " . mysqli_error($koneksi));
    }

    mysqli_stmt_bind_param($cek_penempatan, "i", $siswa_id);
    mysqli_stmt_execute($cek_penempatan);

    $hasil_penempatan = mysqli_stmt_get_result($cek_penempatan);
    $sudah_punya_kelas = mysqli_num_rows($hasil_penempatan) > 0;

    mysqli_stmt_close($cek_penempatan);

    if (
        !isset($siswa_map[$siswa_id]) ||
        $sudah_punya_kelas ||
        !isset($kelas_map[$kelas_id]) ||
        !isset($tahun_map[$tahun_id]) ||
        !$tanggal_valid ||
        !in_array($status, [0, 1], true)
    ) {

        $pesan_error = $sudah_punya_kelas
            ? "Siswa tersebut sudah memiliki kelas dan tidak dapat dipilih kembali."
            : "Periksa kembali data dan tanggal yang kamu masukkan.";

    } else {

        /* SIMPAN LANGSUNG KE TABEL t_kelas_siswa */
        $query_simpan = mysqli_prepare(
            $koneksi,
            "INSERT INTO t_kelas_siswa
            (
                siswa_id,
                tahun_ajaran_id,
                kelas_id,
                tanggal_mulai,
                tanggal_selesai,
                status_aktif
            )
            VALUES (?, ?, ?, ?, ?, ?)"
        );

        if (!$query_simpan) {

            $pesan_error = "Gagal menyiapkan penyimpanan: " .
                mysqli_error($koneksi);

        } else {

            mysqli_stmt_bind_param(
                $query_simpan,
                "iiissi",
                $siswa_id,
                $tahun_id,
                $kelas_id,
                $tanggal_mulai,
                $tanggal_selesai,
                $status
            );

            if (mysqli_stmt_execute($query_simpan)) {

                mysqli_stmt_close($query_simpan);

                header("Location: penempatan_siswa.php?pesan=berhasil");
                exit;

            } else {

                $pesan_error = "Gagal menyimpan penempatan: " .
                    mysqli_stmt_error($query_simpan);

                mysqli_stmt_close($query_simpan);
            }
        }
    }
}

/* MENU ADMIN */

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

    <title>Tambah Penempatan Siswa - Sistem Informasi Pelanggaran Siswa</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

</head>

<body class="bg-light">

<div class="container-fluid">

    <div class="row">

        <!-- SIDEBAR -->

        <aside class="col-12 col-md-3 col-lg-2 bg-dark min-vh-100 p-3">

            <div class="text-white mb-4 pt-3 pb-3 border-bottom border-secondary">

                <h5 class="fw-bold mb-2">
                    <i class="bi bi-shield-check me-2"></i>
                    Sistem Pelanggaran
                </h5>

                <small class="text-secondary">
                    Panel Admin
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
                            class="nav-link <?= $item[0] === 'Penempatan Siswa' ? 'active' : 'text-white'; ?> rounded-2 py-2 px-3"
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

        <main class="col-12 col-md-9 col-lg-10 p-3 p-lg-5">

            <!-- JUDUL HALAMAN -->

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-4 border-bottom">

                <div>

                    <h2 class="fw-bold mb-2">
                        Tambah Penempatan Siswa
                    </h2>

                    <p class="text-secondary mb-0">
                        Silakan isi formulir penempatan siswa dengan lengkap.
                    </p>

                </div>

            </div>

            <!-- PESAN ERROR -->

            <?php if ($pesan_error !== ''): ?>

                <div class="alert alert-danger alert-dismissible fade show" role="alert">

                    <?= aman($pesan_error); ?>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"
                        aria-label="Tutup"
                    ></button>

                </div>

            <?php endif; ?>

            <!-- FORMULIR -->

            <div class="row justify-content-center">

                <div class="col-12 col-xl-10">

                    <div class="card border-0 shadow-sm rounded-3">

                        <div class="card-header bg-white p-3">

                            <h5 class="text-primary fw-bold mb-1">
                                Formulir Penempatan Siswa
                            </h5>

                            <small class="text-secondary">
                                Isi informasi penempatan siswa pada kolom yang tersedia.
                            </small>

                        </div>

                        <div class="card-body p-4">

                            <form method="POST" action="">

                                <div class="row g-3">

                                    <!-- SISWA -->

                                    <div class="col-md-6">

                                        <label for="siswa_id" class="form-label">
                                            Siswa <span class="text-danger">*</span>
                                        </label>

                                        <select
                                            name="siswa_id"
                                            id="siswa_id"
                                            class="form-select"
                                            required
                                        >

                                            <option value="">
                                                -- Pilih Siswa --
                                            </option>

                                            <?php foreach ($siswa_list as $s): ?>

                                                <option
                                                    value="<?= (int)$s['id']; ?>"
                                                    <?= (int)($_POST['siswa_id'] ?? 0) === (int)$s['id'] ? 'selected' : ''; ?>
                                                >
                                                    <?= aman($s['nisn']); ?> - <?= aman($s['nama']); ?>
                                                </option>

                                            <?php endforeach; ?>

                                        </select>

                                        <?php if (count($siswa_list) === 0): ?>

                                            <small class="text-secondary">
                                                Semua siswa sudah memiliki kelas.
                                            </small>

                                        <?php endif; ?>

                                    </div>

                                    <!-- KELAS -->

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

                                            <?php foreach ($kelas_list as $k): ?>

                                                <option
                                                    value="<?= (int)$k['id']; ?>"
                                                    <?= (int)($_POST['kelas_id'] ?? 0) === (int)$k['id'] ? 'selected' : ''; ?>
                                                >
                                                    <?= aman($k['nama']); ?>
                                                </option>

                                            <?php endforeach; ?>

                                        </select>

                                    </div>

                                    <!-- TAHUN AJARAN -->

                                    <div class="col-md-6">

                                        <label for="tahun_ajaran_id" class="form-label">
                                            Tahun Ajaran <span class="text-danger">*</span>
                                        </label>

                                        <select
                                            name="tahun_ajaran_id"
                                            id="tahun_ajaran_id"
                                            class="form-select"
                                            required
                                        >

                                            <option value="">
                                                -- Pilih Tahun Ajaran --
                                            </option>

                                            <?php foreach ($tahun_list as $t): ?>

                                                <option
                                                    value="<?= (int)$t['id']; ?>"
                                                    <?= (int)($_POST['tahun_ajaran_id'] ?? 0) === (int)$t['id'] ? 'selected' : ''; ?>
                                                >
                                                    <?= aman($t['nama']); ?>
                                                </option>

                                            <?php endforeach; ?>

                                        </select>

                                    </div>

                                    <!-- STATUS -->

                                    <div class="col-md-6">

                                        <label for="status_aktif" class="form-label">
                                            Status <span class="text-danger">*</span>
                                        </label>

                                        <select
                                            name="status_aktif"
                                            id="status_aktif"
                                            class="form-select"
                                            required
                                        >

                                            <option
                                                value="1"
                                                <?= (int)($_POST['status_aktif'] ?? 1) === 1 ? 'selected' : ''; ?>
                                            >
                                                Aktif
                                            </option>

                                            <option
                                                value="0"
                                                <?= (int)($_POST['status_aktif'] ?? 1) === 0 ? 'selected' : ''; ?>
                                            >
                                                Tidak Aktif
                                            </option>

                                        </select>

                                    </div>

                                    <!-- TANGGAL MULAI -->

                                    <div class="col-md-6">

                                        <label for="tanggal_mulai" class="form-label">
                                            Tanggal Mulai <span class="text-danger">*</span>
                                        </label>

                                        <input
                                            type="date"
                                            name="tanggal_mulai"
                                            id="tanggal_mulai"
                                            class="form-control"
                                            value="<?= aman($_POST['tanggal_mulai'] ?? ''); ?>"
                                            required
                                        >

                                    </div>

                                    <!-- TANGGAL SELESAI -->

                                    <div class="col-md-6">

                                        <label for="tanggal_selesai" class="form-label">
                                            Tanggal Selesai <span class="text-danger">*</span>
                                        </label>

                                        <input
                                            type="date"
                                            name="tanggal_selesai"
                                            id="tanggal_selesai"
                                            class="form-control"
                                            value="<?= aman($_POST['tanggal_selesai'] ?? ''); ?>"
                                            required
                                        >

                                    </div>

                                </div>

                                <hr class="my-4">

                                <!-- TOMBOL -->

                                <div class="d-flex justify-content-end gap-2">

                                    <a href="penempatan_siswa.php" class="btn btn-secondary">
                                        Batal
                                    </a>

                                    <button
                                        type="submit"
                                        name="simpan"
                                        class="btn btn-primary"
                                        <?= count($siswa_list) === 0 ? 'disabled' : ''; ?>
                                    >
                                        Simpan Data Penempatan
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

            <!-- FOOTER -->

            <div class="text-secondary small mt-5 pt-3 border-top">
                Sistem Informasi Pelanggaran Siswa
            </div>

        </main>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
