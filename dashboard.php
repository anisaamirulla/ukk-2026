
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

$nama = htmlspecialchars($_SESSION['name'] ?? 'Pengguna');

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

$menuGuru = [
    ['Dashboard', 'dashboard.php'],
    ['Catatan Pelanggaran', 'catatan_pelanggaran.php'],
    ['Tindakan', 'tindakan.php'],
    ['Riwayat', 'riwayat.php'],
    ['Rekap Poin', 'rekap_poin.php'],
    ['About Me', 'abautme.php']
];

$menu = ($role === 'admin') ? $menuAdmin : $menuGuru;

$kartuAdmin = [
    ['Dashboard', 'Lihat halaman utama aplikasi.', 'dashboard.php', 'bi-speedometer2'],
    ['Data Siswa', 'Kelola data siswa.', 'siswa.php', 'bi-people-fill'],
    ['Data Guru', 'Kelola data guru dan akun guru.', 'guru.php', 'bi-person-badge-fill'],
    ['Kelas', 'Kelola data kelas.', 'kelas.php', 'bi-building'],
    ['Tahun Ajaran', 'Kelola tahun ajaran sekolah.', 'tahun_ajaran.php', 'bi-calendar-event'],
    ['Penempatan Siswa', 'Kelola penempatan siswa di kelas.', 'penempatan_siswa.php', 'bi-person-lines-fill'],
    ['Wali Kelas', 'Kelola data wali kelas.', 'wali_kelas.php', 'bi-person-workspace'],
    ['Kategori Pelanggaran', 'Kelola kategori pelanggaran siswa.', 'kategori_pelanggaran.php', 'bi-collection-fill'],
    ['Jenis Pelanggaran', 'Kelola jenis pelanggaran siswa.', 'jenis_pelanggaran.php', 'bi-clipboard2-check-fill'],
    ['Laporan', 'Lihat laporan pelanggaran siswa.', 'laporan.php', 'bi-file-earmark-text-fill'],
    ['About Me', 'Lihat informasi profil pengembang.', 'abautme.php', 'bi-person-circle']
];

$kartuGuru = [
    ['Dashboard', 'Lihat halaman utama aplikasi.', 'dashboard.php', 'bi-speedometer2'],
    ['Catatan Pelanggaran', 'Lihat catatan pelanggaran siswa.', 'catatan_pelanggaran.php', 'bi-journal-text'],
    ['Tindakan', 'Kelola tindakan pelanggaran.', 'tindakan.php', 'bi-clipboard-check-fill'],
    ['Riwayat', 'Lihat riwayat pelanggaran siswa.', 'riwayat.php', 'bi-clock-history'],
    ['Rekap Poin', 'Lihat rekap poin pelanggaran siswa.', 'rekap_poin.php', 'bi-bar-chart-fill'],
    ['About Me', 'Lihat informasi profil pengembang.', 'abautme.php', 'bi-person-circle']
];

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Dashboard <?= ucfirst($role); ?> - Sistem Informasi Pelanggaran Siswa</title>

    <!-- BOOTSTRAP 5.3.3 -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- BOOTSTRAP ICONS -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
        rel="stylesheet"
    >
</head>

<body class="bg-light">

<div class="container-fluid">
    <div class="row">

        <!-- SIDEBAR -->
        <aside class="col-12 col-md-3 col-lg-2 bg-dark min-vh-100 p-3">

            <!-- JUDUL SIDEBAR -->
            <div class="text-white mb-4 pt-3 pb-3 border-bottom border-secondary">

                <h5 class="fw-bold mb-2">
                    <i class="bi bi-shield-check me-2"></i>
                    Sistem Pelanggaran
                </h5>

                <small class="text-secondary">
                    Panel <?= ucfirst($role); ?>
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
                            <?= strtoupper($role); ?>
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

                    <li class="nav-item">

                        <a
                            href="<?= htmlspecialchars($item[1]); ?>"
                            class="nav-link <?= $item[0] === 'Dashboard' ? 'active' : 'text-white'; ?> rounded-2 py-2 px-3"
                        >
                            <?= htmlspecialchars($item[0]); ?>
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

        <!-- KONTEN DASHBOARD -->
        <main class="col-12 col-md-9 col-lg-10 p-3 p-lg-5">

            <!-- JUDUL DASHBOARD -->
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-4 border-bottom">

                <div>

                    <h2 class="fw-bold mb-2">
                        Dashboard <?= ucfirst($role); ?>
                    </h2>

                    <p class="text-secondary mb-0">
                        Selamat datang, <strong><?= $nama; ?></strong>
                    </p>

                </div>

                <span class="badge text-bg-dark rounded-pill px-3 py-2">
                    <?= ucfirst($role); ?>
                </span>

            </div>

            <!-- KARTU MENU ADMIN -->
            <?php if ($role === 'admin'): ?>

                <div class="row g-4">

                    <?php foreach ($kartuAdmin as $kartu): ?>

                        <?php if ($kartu[0] === 'Dashboard') continue; ?>

                        <div class="col-12 col-md-6 col-xl-4">

                            <div class="card border-0 shadow-sm rounded-3 h-100">

                                <div class="card-body p-4 d-flex flex-column align-items-start">

                                    <h5 class="card-title fw-bold mb-3">
                                        <?= htmlspecialchars($kartu[0]); ?>
                                    </h5>

                                    <p class="card-text text-secondary mb-4">
                                        <?= htmlspecialchars($kartu[1]); ?>
                                    </p>

                                    <a
                                        href="<?= htmlspecialchars($kartu[2]); ?>"
                                        class="btn btn-primary rounded-2 px-4 mt-auto"
                                    >
                                        Lihat Menu
                                    </a>

                                </div>

                            </div>

                        </div>

                    <?php endforeach; ?>

                </div>

            <?php else: ?>

                <!-- KARTU MENU GURU -->
                <div class="row g-4">

                    <?php foreach ($kartuGuru as $kartu): ?>

                        <?php if ($kartu[0] === 'Dashboard') continue; ?>

                        <div class="col-12 col-md-6 col-xl-4">

                            <div class="card border-0 shadow-sm rounded-3 h-100">

                                <div class="card-body p-4 d-flex flex-column align-items-start">

                                    <h5 class="card-title fw-bold mb-3">
                                        <?= htmlspecialchars($kartu[0]); ?>
                                    </h5>

                                    <p class="card-text text-secondary mb-4">
                                        <?= htmlspecialchars($kartu[1]); ?>
                                    </p>

                                    <a
                                        href="<?= htmlspecialchars($kartu[2]); ?>"
                                        class="btn btn-primary rounded-2 px-4 mt-auto"
                                    >
                                        Buka Fitur
                                    </a>

                                </div>

                            </div>

                        </div>

                    <?php endforeach; ?>

                </div>

            <?php endif; ?>

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
