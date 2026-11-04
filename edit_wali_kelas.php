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

$nama = htmlspecialchars($_SESSION['name'] ?? 'Pengguna', ENT_QUOTES, 'UTF-8');

function aman($nilai) {
    return htmlspecialchars((string)($nilai ?? ''), ENT_QUOTES, 'UTF-8');
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: wali_kelas.php");
    exit;
}

$id = (int) $_GET['id'];
$pesan = '';

$stmt = mysqli_prepare($koneksi, "
    SELECT id, tahun_ajaran_id, kelas_id, guru_id,
           tanggal_mulai, tanggal_selesai, status_aktif
    FROM t_wali_kelas
    WHERE id = ?
");

if (!$stmt) {
    exit("Query gagal: " . aman(mysqli_error($koneksi)));
}

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$wali = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$wali) {
    exit("Data wali kelas tidak ditemukan.");
}

$tahun_list = mysqli_query($koneksi, "SELECT id, nama FROM t_tahun_ajaran ORDER BY id DESC");
$kelas_list = mysqli_query($koneksi, "SELECT id, nama FROM t_kelas ORDER BY nama ASC");
$guru_list = mysqli_query($koneksi, "SELECT id, nip, nama FROM t_guru ORDER BY nama ASC");

if (!$tahun_list || !$kelas_list || !$guru_list) {
    die("Gagal mengambil data referensi: " . mysqli_error($koneksi));
}

if (isset($_POST['update'])) {

    $tahun_id = (int)($_POST['tahun_ajaran_id'] ?? 0);
    $kelas_id = (int)($_POST['kelas_id'] ?? 0);
    $guru_id = (int)($_POST['guru_id'] ?? 0);
    $mulai = $_POST['tanggal_mulai'] ?? '';
    $selesai = $_POST['tanggal_selesai'] ?? '';
    $status = (int)($_POST['status_aktif'] ?? -1);

    $valid_mulai = DateTime::createFromFormat('!Y-m-d', $mulai);
    $valid_selesai = DateTime::createFromFormat('!Y-m-d', $selesai);

    $valid = $valid_mulai && $valid_mulai->format('Y-m-d') === $mulai
        && $valid_selesai && $valid_selesai->format('Y-m-d') === $selesai
        && $selesai >= $mulai
        && in_array($status, [0, 1], true);

    $cek_tahun = mysqli_prepare($koneksi, "SELECT id FROM t_tahun_ajaran WHERE id = ?");
    mysqli_stmt_bind_param($cek_tahun, "i", $tahun_id);
    mysqli_stmt_execute($cek_tahun);
    $tahun_ada = mysqli_stmt_get_result($cek_tahun)->num_rows > 0;
    mysqli_stmt_close($cek_tahun);

    $cek_kelas = mysqli_prepare($koneksi, "SELECT id FROM t_kelas WHERE id = ?");
    mysqli_stmt_bind_param($cek_kelas, "i", $kelas_id);
    mysqli_stmt_execute($cek_kelas);
    $kelas_ada = mysqli_stmt_get_result($cek_kelas)->num_rows > 0;
    mysqli_stmt_close($cek_kelas);

    $cek_guru = mysqli_prepare($koneksi, "SELECT id FROM t_guru WHERE id = ?");
    mysqli_stmt_bind_param($cek_guru, "i", $guru_id);
    mysqli_stmt_execute($cek_guru);
    $guru_ada = mysqli_stmt_get_result($cek_guru)->num_rows > 0;
    mysqli_stmt_close($cek_guru);

    if (!$valid || !$tahun_ada || !$kelas_ada || !$guru_ada) {

        $pesan = "Periksa kembali data dan tanggal yang kamu masukkan.";

    } else {

        /* CEK DUPLIKASI JIKA STATUS AKTIF */
        if ($status === 1) {

            /* Guru tidak boleh memiliki dua penugasan aktif */
            $cek_guru = mysqli_prepare(
                $koneksi,
                "SELECT id FROM t_wali_kelas
                 WHERE guru_id = ?
                 AND status_aktif = 1
                 AND id != ?
                 LIMIT 1"
            );

            if (!$cek_guru) {
                $pesan = "Gagal memeriksa data guru: " . mysqli_error($koneksi);
            } else {
                mysqli_stmt_bind_param(
                    $cek_guru,
                    "ii",
                    $guru_id,
                    $id
                );

                mysqli_stmt_execute($cek_guru);
                $hasil_guru = mysqli_stmt_get_result($cek_guru);

                if (mysqli_num_rows($hasil_guru) > 0) {
                    $pesan = "Guru tersebut masih menjadi wali kelas aktif di kelas lain. Silakan pilih guru yang berbeda.";
                }

                mysqli_stmt_close($cek_guru);
            }

            /* Satu kelas hanya boleh memiliki satu wali kelas aktif
               pada tahun ajaran yang sama */
            if ($pesan === '') {
                $cek_kelas = mysqli_prepare(
                    $koneksi,
                    "SELECT id FROM t_wali_kelas
                     WHERE tahun_ajaran_id = ?
                     AND kelas_id = ?
                     AND status_aktif = 1
                     AND id != ?
                     LIMIT 1"
                );

                if (!$cek_kelas) {
                    $pesan = "Gagal memeriksa data kelas: " . mysqli_error($koneksi);
                } else {
                    mysqli_stmt_bind_param(
                        $cek_kelas,
                        "iii",
                        $tahun_id,
                        $kelas_id,
                        $id
                    );

                    mysqli_stmt_execute($cek_kelas);
                    $hasil_kelas = mysqli_stmt_get_result($cek_kelas);

                    if (mysqli_num_rows($hasil_kelas) > 0) {
                        $pesan = "Kelas tersebut sudah memiliki wali kelas aktif pada tahun ajaran ini.";
                    }

                    mysqli_stmt_close($cek_kelas);
                }
            }
        }

        /* UPDATE DATA JIKA TIDAK ADA DUPLIKASI */
        if ($pesan === '') {

            $update = mysqli_prepare($koneksi, "
                UPDATE t_wali_kelas
                SET tahun_ajaran_id = ?, kelas_id = ?, guru_id = ?,
                    tanggal_mulai = ?, tanggal_selesai = ?, status_aktif = ?
                WHERE id = ?
            ");

            if (!$update) {
                $pesan = "Query update gagal: " . mysqli_error($koneksi);
            } else {

                mysqli_stmt_bind_param(
                    $update,
                    "iiissii",
                    $tahun_id,
                    $kelas_id,
                    $guru_id,
                    $mulai,
                    $selesai,
                    $status,
                    $id
                );

                if (mysqli_stmt_execute($update)) {
                    mysqli_stmt_close($update);
                    header("Location: wali_kelas.php?pesan=edit");
                    exit;
                } else {
                    $pesan = "Data wali kelas gagal diperbarui.";
                    mysqli_stmt_close($update);
                }
            }
        }
    }

    $wali['tahun_ajaran_id'] = $tahun_id;
    $wali['kelas_id'] = $kelas_id;
    $wali['guru_id'] = $guru_id;
    $wali['tanggal_mulai'] = $mulai;
    $wali['tanggal_selesai'] = $selesai;
    $wali['status_aktif'] = $status;

    // Ambil ulang data pilihan agar tetap tersedia setelah validasi gagal
    $tahun_list = mysqli_query($koneksi, "SELECT id, nama FROM t_tahun_ajaran ORDER BY id DESC");
    $kelas_list = mysqli_query($koneksi, "SELECT id, nama FROM t_kelas ORDER BY nama ASC");
    $guru_list = mysqli_query($koneksi, "SELECT id, nip, nama FROM t_guru ORDER BY nama ASC");

    if (!$tahun_list || !$kelas_list || !$guru_list) {
        die("Gagal mengambil data referensi: " . mysqli_error($koneksi));
    }
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

    <title>Edit Wali Kelas - Sistem Informasi Pelanggaran Siswa</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
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
                            href="<?= aman($item[1]); ?>"
                            class="nav-link <?= $item[0] === 'Wali Kelas' ? 'active' : 'text-white'; ?> rounded-2 py-2 px-3"
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

        <!-- KONTEN EDIT WALI KELAS -->
        <main class="col-9 col-md-9 col-lg-10 vh-100 overflow-auto p-3 p-lg-5">

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-4 border-bottom">
                <div>
                    <h2 class="fw-bold mb-2">Edit Wali Kelas</h2>
                    <p class="text-secondary mb-0">
                        Silakan perbarui data wali kelas dengan lengkap.
                    </p>
                </div>
            </div>

            <!-- PESAN ERROR -->
            <?php if ($pesan !== ''): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= aman($pesan); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
                </div>
            <?php endif; ?>

            <!-- FORMULIR -->
            <div class="row justify-content-center">
                <div class="col-12 col-xl-10">

                    <div class="card border-0 shadow-sm rounded-3">

                        <div class="card-header bg-white p-3">
                            <h5 class="text-primary fw-bold mb-1">Formulir Wali Kelas</h5>
                            <small class="text-secondary">
                                Perbarui informasi wali kelas pada kolom yang tersedia.
                            </small>
                        </div>

                        <div class="card-body p-4">

                            <form method="POST" action="">

                                <div class="row g-3">

                                    <div class="col-md-6">
                                        <label for="tahun_ajaran_id" class="form-label">
                                            Tahun Ajaran <span class="text-danger">*</span>
                                        </label>

                                        <select name="tahun_ajaran_id" id="tahun_ajaran_id" class="form-select" required>
                                            <option value="">-- Pilih Tahun Ajaran --</option>

                                            <?php while ($t = mysqli_fetch_assoc($tahun_list)): ?>
                                                <option
                                                    value="<?= (int)$t['id']; ?>"
                                                    <?= (int)$wali['tahun_ajaran_id'] === (int)$t['id'] ? 'selected' : ''; ?>
                                                >
                                                    <?= aman($t['nama']); ?>
                                                </option>
                                            <?php endwhile; ?>

                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="kelas_id" class="form-label">
                                            Kelas <span class="text-danger">*</span>
                                        </label>

                                        <select name="kelas_id" id="kelas_id" class="form-select" required>
                                            <option value="">-- Pilih Kelas --</option>

                                            <?php while ($k = mysqli_fetch_assoc($kelas_list)): ?>
                                                <option
                                                    value="<?= (int)$k['id']; ?>"
                                                    <?= (int)$wali['kelas_id'] === (int)$k['id'] ? 'selected' : ''; ?>
                                                >
                                                    <?= aman($k['nama']); ?>
                                                </option>
                                            <?php endwhile; ?>

                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="guru_id" class="form-label">
                                            Guru <span class="text-danger">*</span>
                                        </label>

                                        <select name="guru_id" id="guru_id" class="form-select" required>
                                            <option value="">-- Pilih Guru --</option>

                                            <?php while ($g = mysqli_fetch_assoc($guru_list)): ?>
                                                <option
                                                    value="<?= (int)$g['id']; ?>"
                                                    <?= (int)$wali['guru_id'] === (int)$g['id'] ? 'selected' : ''; ?>
                                                >
                                                    <?= aman($g['nip']); ?> - <?= aman($g['nama']); ?>
                                                </option>
                                            <?php endwhile; ?>

                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="status_aktif" class="form-label">
                                            Status <span class="text-danger">*</span>
                                        </label>

                                        <select name="status_aktif" id="status_aktif" class="form-select" required>
                                            <option value="1" <?= (int)$wali['status_aktif'] === 1 ? 'selected' : ''; ?>>
                                                Aktif
                                            </option>

                                            <option value="0" <?= (int)$wali['status_aktif'] === 0 ? 'selected' : ''; ?>>
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
                                            value="<?= aman($wali['tanggal_mulai']); ?>"
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
                                            value="<?= aman($wali['tanggal_selesai']); ?>"
                                            required
                                        >
                                    </div>

                                </div>

                                <hr class="my-4">

                                <div class="d-flex justify-content-end gap-2">
                                    <a href="wali_kelas.php" class="btn btn-secondary">
                                        Batal
                                    </a>

                                    <button type="submit" name="update" class="btn btn-primary">
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

            <div class="text-secondary small mt-5 pt-3 border-top">
                Sistem Informasi Pelanggaran Siswa
            </div>

        </main>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>