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
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Dashboard <?= ucfirst($role); ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
          rel="stylesheet">
</head>

<body class="bg-white">

<div class="container-fluid">
    <div class="row">

        <!-- SIDEBAR -->
        <aside class="col-12 col-md-3 col-lg-2 bg-dark min-vh-100 p-3">

            <div class="text-white mb-4">
                <h4 class="fw-bold mb-0">
                    <i class="bi bi-shield-check me-2"></i>
                    Sistem Pelanggaran
                </h4>

                <small class="text-secondary">
                    Panel <?= ucfirst($role); ?>
                </small>
            </div>

            <!-- PROFIL PENGGUNA -->
            <div class="bg-secondary bg-opacity-25 rounded-3 p-3 mb-4">

                <div class="d-flex align-items-center gap-3">

                    <div class="bg-white text-dark rounded-circle p-2">
                        <i class="bi bi-person-fill fs-4"></i>
                    </div>

                    <div class="text-white">
                        <div class="fw-semibold"><?= $nama; ?></div>

                        <small class="text-secondary">
                            <?= strtoupper($role); ?>
                        </small>
                    </div>

                </div>

            </div>

            <!-- MENU UTAMA TANPA IKON -->
            <div class="text-secondary small fw-semibold mb-2">
                MENU UTAMA
            </div>

            <ul class="nav nav-pills flex-column gap-1">

                <?php foreach ($menu as $item): ?>

                    <li class="nav-item">

                        <a href="<?= htmlspecialchars($item[1]); ?>"
                           class="nav-link <?= $item[0] === 'Dashboard' ? 'active' : 'text-white'; ?>">

                            <?= htmlspecialchars($item[0]); ?>

                        </a>

                    </li>

                <?php endforeach; ?>

            </ul>

            <hr class="border-secondary">

            <!-- LOGOUT -->
            <a href="logout.php" class="nav-link text-danger">
                <i class="bi bi-box-arrow-left me-2"></i>
                Logout
            </a>

        </aside>

        <!-- KONTEN DASHBOARD -->
        <main class="col-12 col-md-9 col-lg-10 p-3 p-lg-4">

            <div class="d-flex justify-content-between align-items-center mb-4">

                <div>
                    <h2 class="fw-bold mb-1">
                        Dashboard <?= ucfirst($role); ?>
                    </h2>

                    <p class="text-secondary mb-0">
                        Selamat datang, <strong><?= $nama; ?></strong>
                    </p>
                </div>

                <span class="badge text-bg-secondary rounded-pill px-3 py-2">
                    <i class="bi bi-person-check me-1"></i>
                    <?= ucfirst($role); ?>
                </span>

            </div>

            <?php if ($role === 'admin'): ?>

                <!-- KARTU DASHBOARD ADMIN -->
                <div class="row g-3">

                    <?php
                    $kartuAdmin = [
                        [
                            'Data Siswa',
                            'Kelola data siswa.',
                            'siswa.php',
                            'bi-people-fill',
                            'bg-primary'
                        ],
                        [
                            'Data Guru',
                            'Kelola data guru.',
                            'guru.php',
                            'bi-person-badge-fill',
                            'bg-secondary'
                        ],
                        [
                            'Data Kelas',
                            'Kelola data kelas.',
                            'kelas.php',
                            'bi-building',
                            'bg-dark'
                        ]
                    ];

                    foreach ($kartuAdmin as $kartu):
                    ?>

                        <div class="col-12 col-md-6 col-xl-4">

                            <div class="card border-0 shadow-sm h-100">

                                <div class="card-body p-4">

                                    <div class="d-flex align-items-center gap-3 mb-3">

                                        <div class="<?= $kartu[4]; ?> bg-opacity-10 rounded-3 p-3">
                                            <i class="bi <?= $kartu[3]; ?> fs-2 text-dark"></i>
                                        </div>

                                        <div>
                                            <h5 class="card-title fw-bold mb-1">
                                                <?= htmlspecialchars($kartu[0]); ?>
                                            </h5>

                                            <p class="card-text text-secondary mb-0">
                                                <?= htmlspecialchars($kartu[1]); ?>
                                            </p>
                                        </div>

                                    </div>

                                    <a href="<?= htmlspecialchars($kartu[2]); ?>"
                                       class="btn btn-primary">

                                        <i class="bi bi-arrow-right-circle me-1"></i>
                                        Lihat Data

                                    </a>

                                </div>

                            </div>

                        </div>

                    <?php endforeach; ?>

                </div>

            <?php else: ?>

                <!-- KARTU DASHBOARD GURU -->
                <div class="row g-3">

                    <?php
                    $kartuGuru = [
                        [
                            'Catatan Pelanggaran',
                            'Lihat catatan pelanggaran siswa.',
                            'catatan_pelanggaran.php',
                            'bi-journal-text'
                        ],
                        [
                            'Tindakan',
                            'Kelola tindakan pelanggaran.',
                            'tindakan.php',
                            'bi-clipboard-check-fill'
                        ],
                        [
                            'Riwayat',
                            'Lihat riwayat pelanggaran siswa.',
                            'riwayat.php',
                            'bi-clock-history'
                        ],
                        [
                            'Rekap Poin',
                            'Lihat rekap poin pelanggaran siswa.',
                            'rekap_poin.php',
                            'bi-bar-chart-fill'
                        ],
                        [
                            'About Me',
                            'Lihat informasi profil.',
                            'abautme.php',
                            'bi-person-circle'
                        ]
                    ];

                    foreach ($kartuGuru as $kartu):
                    ?>

                        <div class="col-12 col-md-6 col-xl-4">

                            <div class="card border-0 shadow-sm h-100">

                                <div class="card-body p-4">

                                    <div class="d-flex align-items-center gap-3 mb-3">

                                        <div class="bg-light rounded-3 p-3">
                                            <i class="bi <?= htmlspecialchars($kartu[3]); ?> fs-2 text-secondary"></i>
                                        </div>

                                        <div>
                                            <h5 class="card-title fw-bold mb-1">
                                                <?= htmlspecialchars($kartu[0]); ?>
                                            </h5>

                                            <p class="card-text text-secondary mb-0">
                                                <?= htmlspecialchars($kartu[1]); ?>
                                            </p>
                                        </div>

                                    </div>

                                    <a href="<?= htmlspecialchars($kartu[2]); ?>"
                                       class="btn btn-primary">

                                        <i class="bi bi-arrow-right-circle me-1"></i>
                                        Buka Fitur

                                    </a>

                                </div>

                            </div>

                        </div>

                    <?php endforeach; ?>

                </div>

            <?php endif; ?>

        </main>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>