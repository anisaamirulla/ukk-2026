
<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

if (strtolower(trim($_SESSION['role'] ?? '')) !== 'admin') {
    exit("Anda tidak memiliki akses");
}

require_once __DIR__ . "/config/koneksi.php";

function aman($nilai) {
    return htmlspecialchars((string)($nilai ?? ''), ENT_QUOTES, 'UTF-8');
}

$nama = htmlspecialchars($_SESSION['name'] ?? 'Pengguna', ENT_QUOTES, 'UTF-8');

if (!isset($_SESSION['data_penempatan'])) {
    header("Location: penempatan_siswa.php?pesan=gagal_hapus");
    exit;
}

$id = (int)($_GET['id'] ?? 0);
$edit_data = null;

foreach ($_SESSION['data_penempatan'] as $row) {
    if ((int)$row['id'] === $id) {
        $edit_data = $row;
        break;
    }
}

if (!$edit_data) {
    header("Location: penempatan_siswa.php");
    exit;
}

$query_siswa = mysqli_query($koneksi, "SELECT id, nisn, nama FROM t_siswa ORDER BY nama ASC");
$query_kelas = mysqli_query($koneksi, "SELECT id, nama FROM t_kelas ORDER BY nama ASC");
$query_tahun = mysqli_query($koneksi, "SELECT id, nama FROM t_tahun_ajaran ORDER BY id DESC");

if (!$query_siswa || !$query_kelas || !$query_tahun) {
    die("Gagal mengambil data referensi: " . mysqli_error($koneksi));
}

$siswa_list = mysqli_fetch_all($query_siswa, MYSQLI_ASSOC);
$kelas_list = mysqli_fetch_all($query_kelas, MYSQLI_ASSOC);
$tahun_list = mysqli_fetch_all($query_tahun, MYSQLI_ASSOC);

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $siswa_id = (int)($_POST['siswa_id'] ?? 0);
    $kelas_id = (int)($_POST['kelas_id'] ?? 0);
    $tahun_id = (int)($_POST['tahun_ajaran_id'] ?? 0);
    $tanggal_mulai = $_POST['tanggal_mulai'] ?? '';
    $tanggal_selesai = $_POST['tanggal_selesai'] ?? '';
    $status = (int)($_POST['status_aktif'] ?? 1);

    $siswa_map = [];
    foreach ($siswa_list as $s) {
        $siswa_map[(int)$s['id']] = $s;
    }

    $kelas_map = [];
    foreach ($kelas_list as $k) {
        $kelas_map[(int)$k['id']] = $k['nama'];
    }

    $tahun_map = [];
    foreach ($tahun_list as $t) {
        $tahun_map[(int)$t['id']] = $t['nama'];
    }

    $mulai = DateTime::createFromFormat('!Y-m-d', $tanggal_mulai);
    $selesai = DateTime::createFromFormat('!Y-m-d', $tanggal_selesai);

    if (
        !isset($siswa_map[$siswa_id]) ||
        !isset($kelas_map[$kelas_id]) ||
        !isset($tahun_map[$tahun_id]) ||
        !$mulai || $mulai->format('Y-m-d') !== $tanggal_mulai ||
        !$selesai || $selesai->format('Y-m-d') !== $tanggal_selesai ||
        $tanggal_selesai < $tanggal_mulai ||
        !in_array($status, [0, 1], true)
    ) {
        $error = "Periksa kembali data dan tanggal yang kamu masukkan.";
    } else {
        foreach ($_SESSION['data_penempatan'] as &$row) {
            if ((int)$row['id'] === $id) {
                $row['siswa_id'] = $siswa_id;
                $row['nisn'] = $siswa_map[$siswa_id]['nisn'];
                $row['nama_siswa'] = $siswa_map[$siswa_id]['nama'];
                $row['kelas_id'] = $kelas_id;
                $row['nama_kelas'] = $kelas_map[$kelas_id];
                $row['tahun_ajaran_id'] = $tahun_id;
                $row['nama_tahun_ajaran'] = $tahun_map[$tahun_id];
                $row['tanggal_mulai'] = $tanggal_mulai;
                $row['tanggal_selesai'] = $tanggal_selesai;
                $row['status_aktif'] = $status;
                break;
            }
        }
        unset($row);

        header("Location: penempatan_siswa.php?pesan=edit");
        exit;
    }

    $edit_data['siswa_id'] = $siswa_id;
    $edit_data['kelas_id'] = $kelas_id;
    $edit_data['tahun_ajaran_id'] = $tahun_id;
    $edit_data['tanggal_mulai'] = $tanggal_mulai;
    $edit_data['tanggal_selesai'] = $tanggal_selesai;
    $edit_data['status_aktif'] = $status;
}

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

    <title>Edit Penempatan Siswa - Sistem Informasi Pelanggaran Siswa</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container-fluid">
    <div class="row">

        <!-- SIDEBAR SAMA DENGAN EDIT SISWA -->
        <aside class="col-12 col-md-3 col-lg-2 bg-dark min-vh-100 p-3">

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
                    <img
                        src="logo.png"
                        alt="Logo Aplikasi"
                        width="44"
                        height="44"
                        class="rounded-circle bg-white p-1"
                    >

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
                            class="nav-link <?= $item[0] === 'Penempatan Siswa' ? 'active' : 'text-white'; ?> rounded-2 py-2 px-3"
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

        <!-- KONTEN EDIT PENEMPATAN SISWA -->
        <main class="col-12 col-md-9 col-lg-10 p-3 p-lg-5">

            <!-- JUDUL HALAMAN -->
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-4 border-bottom">
                <div>
                    <h2 class="fw-bold mb-2">Edit Penempatan Siswa</h2>
                    <p class="text-secondary mb-0">
                        Silakan perbarui data penempatan siswa dengan lengkap.
                    </p>
                </div>
            </div>

            <!-- PESAN ERROR -->
            <?php if ($error !== ''): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= aman($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
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
                                Perbarui informasi penempatan siswa pada kolom yang tersedia.
                            </small>
                        </div>

                        <div class="card-body p-4">

                            <form method="POST" action="">

                                <div class="row g-3">

                                    <div class="col-md-6">
                                        <label for="siswa_id" class="form-label">
                                            Siswa <span class="text-danger">*</span>
                                        </label>

                                        <select name="siswa_id" id="siswa_id" class="form-select" required>
                                            <option value="">-- Pilih Siswa --</option>

                                            <?php foreach ($siswa_list as $s): ?>
                                                <option
                                                    value="<?= (int)$s['id']; ?>"
                                                    <?= (int)$edit_data['siswa_id'] === (int)$s['id'] ? 'selected' : ''; ?>
                                                >
                                                    <?= aman($s['nisn']); ?> - <?= aman($s['nama']); ?>
                                                </option>
                                            <?php endforeach; ?>

                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="kelas_id" class="form-label">
                                            Kelas <span class="text-danger">*</span>
                                        </label>

                                        <select name="kelas_id" id="kelas_id" class="form-select" required>
                                            <option value="">-- Pilih Kelas --</option>

                                            <?php foreach ($kelas_list as $k): ?>
                                                <option
                                                    value="<?= (int)$k['id']; ?>"
                                                    <?= (int)$edit_data['kelas_id'] === (int)$k['id'] ? 'selected' : ''; ?>
                                                >
                                                    <?= aman($k['nama']); ?>
                                                </option>
                                            <?php endforeach; ?>

                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="tahun_ajaran_id" class="form-label">
                                            Tahun Ajaran <span class="text-danger">*</span>
                                        </label>

                                        <select name="tahun_ajaran_id" id="tahun_ajaran_id" class="form-select" required>
                                            <option value="">-- Pilih Tahun Ajaran --</option>

                                            <?php foreach ($tahun_list as $t): ?>
                                                <option
                                                    value="<?= (int)$t['id']; ?>"
                                                    <?= (int)$edit_data['tahun_ajaran_id'] === (int)$t['id'] ? 'selected' : ''; ?>
                                                >
                                                    <?= aman($t['nama']); ?>
                                                </option>
                                            <?php endforeach; ?>

                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="status_aktif" class="form-label">
                                            Status <span class="text-danger">*</span>
                                        </label>

                                        <select name="status_aktif" id="status_aktif" class="form-select" required>
                                            <option value="1" <?= (int)$edit_data['status_aktif'] === 1 ? 'selected' : ''; ?>>
                                                Aktif
                                            </option>

                                            <option value="0" <?= (int)$edit_data['status_aktif'] === 0 ? 'selected' : ''; ?>>
                                                Tidak Aktif
                                            </option>
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="tanggal_mulai" class="form-label">
                                            Tanggal Mulai <span class="text-danger">*</span>
                                        </label>

                                        <input
                                            type="date"
                                            name="tanggal_mulai"
                                            id="tanggal_mulai"
                                            class="form-control"
                                            value="<?= aman($edit_data['tanggal_mulai']); ?>"
                                            required
                                        >
                                    </div>

                                    <div class="col-md-6">
                                        <label for="tanggal_selesai" class="form-label">
                                            Tanggal Selesai <span class="text-danger">*</span>
                                        </label>

                                        <input
                                            type="date"
                                            name="tanggal_selesai"
                                            id="tanggal_selesai"
                                            class="form-control"
                                            value="<?= aman($edit_data['tanggal_selesai']); ?>"
                                            required
                                        >
                                    </div>

                                </div>

                                <hr class="my-4">

                                <div class="d-flex justify-content-end gap-2">
                                    <a href="penempatan_siswa.php" class="btn btn-secondary">
                                        Batal
                                    </a>

                                    <button type="submit" class="btn btn-primary">
                                        Simpan Perubahan
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
