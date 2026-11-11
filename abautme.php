
<?php
session_start();
require_once __DIR__ . "/config/koneksi.php";

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

$role = strtolower(trim($_SESSION['role'] ?? ''));

if (!in_array($role, ['admin', 'guru'], true)) {
    exit("Anda tidak memiliki akses");
}

// FOLDER WALI KELAS
$folderWaliKelas = "walikelas/";

// NAMA PENGGUNA
$nama = $_SESSION['name'] ?? 'Pengguna';

// PEMERIKSAAN GURU DAN WALI KELAS
$isWaliKelas = false;
$guru_id = 0;

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
            $nama = $dataGuru['nama'];

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

// MENU ADMIN (SAMA DENGAN DASHBOARD)
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

// MENU GURU BIASA (SAMA PERSIS DENGAN DASHBOARD)
$menuGuru = [
    ['Dashboard', 'dashboard.php'],
    ['Catatan Pelanggaran', 'catatan_pelanggaran.php'],
    ['Riwayat', 'riwayat.php'],
    ['Rekap Poin', 'rekap_poin.php'],
    ['About Me', 'abautme.php']
];

// MENU WALI KELAS (SAMA PERSIS DENGAN DASHBOARD)
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
    $menuTampil = $menuAdmin;
} elseif ($isWaliKelas) {
    $menuTampil = $menuWaliKelas;
} else {
    $menuTampil = $menuGuru;
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>About Me - Sistem Informasi Pelanggaran Siswa</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
        rel="stylesheet"
    >

</head>

<body class="bg-light overflow-hidden">

<div class="container-fluid vh-100 overflow-hidden">

    <div class="row h-100 flex-nowrap">

        <!-- SIDEBAR -->
        <aside class="col-12 col-md-3 col-lg-2 bg-dark vh-100 overflow-auto position-sticky top-0 p-3 flex-shrink-0">

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

                <?php foreach ($menuTampil as $item): ?>

                    <li class="nav-item">

                        <a
                            href="<?= htmlspecialchars($item[1], ENT_QUOTES, 'UTF-8'); ?>"
                            class="nav-link <?= $item[0] === 'About Me' ? 'active' : 'text-white'; ?> rounded-2 py-2 px-3"
                            <?= $item[0] === 'About Me' ? 'aria-current="page"' : ''; ?>
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

        <!-- KONTEN ABOUT ME -->
        <main class="col-12 col-md-9 col-lg-10 vh-100 overflow-auto p-3 p-lg-5">

            <!-- JUDUL HALAMAN -->
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4 pb-4 border-bottom">

                <div>

                    <h2 class="fw-bold mb-2">
                        Tentang Saya
                    </h2>

                    <p class="text-secondary mb-2">
                        Sedikit cerita tentang saya dan proyek yang sedang dikembangkan.
                    </p>

                    <a
                        href="dashboard.php"
                        class="btn btn-secondary btn-sm rounded-2"
                    >
                        <i class="bi bi-arrow-left me-1"></i>
                        Kembali ke Dashboard
                    </a>

                </div>

            </div>

            <!-- PROFIL -->
            <div class="card border-0 shadow-sm mb-4 rounded-3">

                <div class="card-body p-4">

                    <div class="row align-items-center g-4">

                        <div class="col-md-3 text-center">

                            <img
                                src="anisa_gambar.jpeg"
                                alt="Foto Anisa Amirulla"
                                class="img-fluid rounded-4 border"
                                width="180"
                            >

                        </div>

                        <div class="col-md-9">

                            <p class="text-primary fw-semibold mb-1">
                                ADMINISTRATOR
                            </p>

                            <h3 class="fw-bold mb-2">
                                Anisa Amirulla
                            </h3>

                            <h5 class="fw-bold mb-2">
                                XII RPL 2
                            </h5>

                            <p class="text-secondary mb-0">
                                Pengembang Sistem Informasi Pelanggaran Siswa
                            </p>

                        </div>

                    </div>

                </div>

            </div>

            <!-- TENTANG SAYA -->
            <div class="card border-0 shadow-sm mb-4 rounded-3">

                <div class="card-body p-4">

                    <h5 class="fw-bold mb-3">
                        Halo, selamat datang!
                    </h5>

                    <p class="text-secondary">
                        Saya merupakan pengembang aplikasi Sistem Informasi
                        Pelanggaran Siswa. Aplikasi ini dibuat untuk membantu
                        proses pengelolaan data siswa dan pencatatan pelanggaran
                        agar lebih mudah digunakan.
                    </p>

                    <p class="text-secondary mb-0">
                        Melalui proyek ini, saya ingin mengembangkan kemampuan
                        dalam membuat aplikasi berbasis web, mulai dari
                        perancangan tampilan hingga pengelolaan data.
                    </p>

                </div>

            </div>

            <!-- INFORMASI PROYEK -->
            <div class="card border-0 shadow-sm rounded-3">

                <div class="card-body p-4">

                    <h5 class="fw-bold mb-4">
                        Informasi Proyek
                    </h5>

                    <div class="row g-3">

                        <div class="col-md-6">

                            <div class="card h-100 bg-light border-0">

                                <div class="card-body">

                                    <p class="text-secondary mb-1">
                                        Nama Aplikasi
                                    </p>

                                    <h6 class="fw-bold">
                                        Sistem Informasi Pelanggaran Siswa
                                    </h6>

                                </div>

                            </div>

                        </div>

                        <div class="col-md-6">

                            <div class="card h-100 bg-light border-0">

                                <div class="card-body">

                                    <p class="text-secondary mb-1">
                                        Jenis Aplikasi
                                    </p>

                                    <h6 class="fw-bold">
                                        Website
                                    </h6>

                                </div>

                            </div>

                        </div>

                        <div class="col-md-6">

                            <div class="card h-100 bg-light border-0">

                                <div class="card-body">

                                    <p class="text-secondary mb-1">
                                        Fokus Sistem
                                    </p>

                                    <h6 class="fw-bold">
                                        Pengelolaan Data Pelanggaran
                                    </h6>

                                </div>

                            </div>

                        </div>

                        <div class="col-md-6">

                            <div class="card h-100 bg-light border-0">

                                <div class="card-body">

                                    <p class="text-secondary mb-1">
                                        Tujuan
                                    </p>

                                    <h6 class="fw-bold">
                                        Mempermudah pengelolaan data
                                    </h6>

                                </div>

                            </div>

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
