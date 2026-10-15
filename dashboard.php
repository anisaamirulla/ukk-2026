
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
</head>

<body>

<div class="container-fluid">
    <div class="row">

        <aside class="col-md-3 col-lg-2 bg-dark min-vh-100 p-3">

            <h4 class="text-white mb-4">Sistem Pelanggaran</h4>

            <ul class="nav nav-pills flex-column">

                <?php foreach ($menu as $item): ?>
                    <li class="nav-item mb-2">
                        <a href="<?= htmlspecialchars($item[1]); ?>"
                           class="nav-link <?= $item[0] === 'Dashboard' ? 'active' : 'text-white'; ?>">
                            <?= htmlspecialchars($item[0]); ?>
                        </a>
                    </li>
                <?php endforeach; ?>

                <hr class="text-secondary">

                <li class="nav-item">
                    <a href="logout.php" class="nav-link text-danger">
                        Logout
                    </a>
                </li>

            </ul>
        </aside>

        <main class="col-md-9 col-lg-10 p-4">

            <h2>Dashboard <?= ucfirst($role); ?></h2>

            <p>Selamat datang, <b><?= $nama; ?></b></p>

            <?php if ($role === 'admin'): ?>

                <div class="row">

                    <?php
                    $kartuAdmin = [
                        ['Data Siswa', 'Kelola data siswa.', 'siswa.php'],
                        ['Data Guru', 'Kelola data guru.', 'guru.php'],
                        ['Data Kelas', 'Kelola data kelas.', 'kelas.php']
                    ];

                    foreach ($kartuAdmin as $kartu):
                    ?>

                        <div class="col-md-4 mb-3">
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <?= htmlspecialchars($kartu[0]); ?>
                                    </h5>

                                    <p class="card-text">
                                        <?= htmlspecialchars($kartu[1]); ?>
                                    </p>

                                    <a href="<?= htmlspecialchars($kartu[2]); ?>"
                                       class="btn btn-primary">
                                        Lihat Data
                                    </a>
                                </div>
                            </div>
                        </div>

                    <?php endforeach; ?>

                </div>

            <?php else: ?>

                <div class="row">

                    <?php
                    $kartuGuru = [
                        ['Catatan Pelanggaran', 'Lihat catatan pelanggaran siswa.', 'catatan_pelanggaran.php'],
                        ['Tindakan', 'Kelola tindakan pelanggaran.', 'tindakan.php'],
                        ['Riwayat', 'Lihat riwayat pelanggaran siswa.', 'riwayat.php'],
                        ['Rekap Poin', 'Lihat rekap poin pelanggaran siswa.', 'rekap_poin.php'],
                        ['About Me', 'Lihat informasi profil.', 'abautme.php']
                    ];

                    foreach ($kartuGuru as $kartu):
                    ?>

                        <div class="col-md-4 mb-3">
                            <div class="card shadow-sm h-100">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <?= htmlspecialchars($kartu[0]); ?>
                                    </h5>

                                    <p class="card-text">
                                        <?= htmlspecialchars($kartu[1]); ?>
                                    </p>

                                    <a href="<?= htmlspecialchars($kartu[2]); ?>"
                                       class="btn btn-primary">
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

</body>
</html>
