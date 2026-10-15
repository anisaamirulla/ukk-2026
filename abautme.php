
<?php
session_start();
require_once __DIR__ . "/config/koneksi.php";

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

$role = strtolower(trim($_SESSION['role'] ?? ''));

if (!in_array($role, ['admin', 'guru'])) {
    exit("Anda tidak memiliki akses");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>About Me | Sistem Pelanggaran</title>

    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>

<body class="bg-light">

<div class="container-fluid">
    <div class="row">

    
        <aside class="col-md-3 col-lg-2 bg-dark min-vh-100 p-3">
            <h4 class="text-white mb-4">Sistem Pelanggaran</h4>

            <ul class="nav nav-pills flex-column">
                <li class="nav-item mb-2">
                    <a href="dashboard.php" class="nav-link text-white">Dashboard</a>
                </li>

                <?php if ($role == 'admin') { ?>

                <li class="nav-item mb-2">
                    <a href="siswa.php" class="nav-link text-white">Data Siswa</a>
                </li>
                <li class="nav-item mb-2">
                    <a href="guru.php" class="nav-link text-white">Data Guru</a>
                </li>
                <li class="nav-item mb-2">
                    <a href="kelas.php" class="nav-link text-white">Kelas</a>
                </li>
                <li class="nav-item mb-2">
                    <a href="tahun_ajaran.php" class="nav-link text-white">Tahun Ajaran</a>
                </li>
                <li class="nav-item mb-2">
                    <a href="penempatan_siswa.php" class="nav-link text-white">Penempatan Siswa</a>
                </li>
                <li class="nav-item mb-2">
                    <a href="wali_kelas.php" class="nav-link text-white">Wali Kelas</a>
                </li>
                <li class="nav-item mb-2">
                    <a href="kategori_pelanggaran.php" class="nav-link text-white">Kategori Pelanggaran</a>
                </li>
                <li class="nav-item mb-2">
                    <a href="jenis_pelanggaran.php" class="nav-link text-white">Jenis Pelanggaran</a>
                </li>
                <li class="nav-item mb-2">
                    <a href="laporan.php" class="nav-link text-white">Laporan</a>
                </li>

                <?php } else { ?>

                <li class="nav-item mb-2">
                    <a href="catatan_pelanggaran.php" class="nav-link text-white">Catatan Pelanggaran</a>
                </li>
                <li class="nav-item mb-2">
                    <a href="tindakan.php" class="nav-link text-white">Tindakan</a>
                </li>
                <li class="nav-item mb-2">
                    <a href="riwayat.php" class="nav-link text-white">Riwayat</a>
                </li>
                <li class="nav-item mb-2">
                    <a href="rekap_poin.php" class="nav-link text-white">Rekap Poin</a>
                </li>

                <?php } ?>

                <li class="nav-item mb-2">
                    <a href="abautme.php" class="nav-link active" aria-current="page">About Me</a>
                </li>

                <hr class="text-secondary">

                <li class="nav-item">
                    <a href="logout.php" class="nav-link text-danger">Logout</a>
                </li>
            </ul>
        </aside>

    
        <main class="col-md-9 col-lg-10 p-4">

            <div class="mb-4">
                <span class="badge text-bg-primary mb-2">PROFILE</span>
                <h2 class="fw-bold">Tentang Saya</h2>
                <p class="text-secondary">
                    Sedikit cerita tentang saya dan proyek yang sedang dikembangkan.
                </p>
            </div>

        
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="row align-items-center g-4">

                        <div class="col-md-3 text-center">

                            
                            <img src="anisa_gambar.jpeg"
                                 alt="Foto Anisa Amirulla"
                                 class="img-fluid rounded-4 border"
                                 width="180">

                        </div>

                        <div class="col-md-9">
                            <p class="text-primary fw-semibold mb-1">
                                ADMINISTRATOR
                            </p>
                            <h3 class="fw-bold mb-2">Anisa Amirulla</h3>
                            <h5 class="fw-bold mb-2">XII RPL 2</h5>
                            <p class="text-secondary mb-0">
                                Pengembang Sistem Informasi Pelanggaran Siswa
                            </p>
                        </div>

                    </div>
                </div>
            </div>

            
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3">Halo, selamat datang!</h5>

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

           
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4">Informasi Proyek</h5>

                    <div class="row g-3">

                        <div class="col-md-6">
                            <div class="card h-100 bg-light border-0">
                                <div class="card-body">
                                    <p class="text-secondary mb-1">Nama Aplikasi</p>
                                    <h6 class="fw-bold">
                                        Sistem Informasi Pelanggaran Siswa
                                    </h6>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card h-100 bg-light border-0">
                                <div class="card-body">
                                    <p class="text-secondary mb-1">Jenis Aplikasi</p>
                                    <h6 class="fw-bold">Website</h6>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card h-100 bg-light border-0">
                                <div class="card-body">
                                    <p class="text-secondary mb-1">Fokus Sistem</p>
                                    <h6 class="fw-bold">
                                        Pengelolaan Data Pelanggaran
                                    </h6>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card h-100 bg-light border-0">
                                <div class="card-body">
                                    <p class="text-secondary mb-1">Tujuan</p>
                                    <h6 class="fw-bold">
                                        Mempermudah pengelolaan data
                                    </h6>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="mt-4">
                        <a href="dashboard.php" class="btn btn-primary">
                            Kembali ke Dashboard
                        </a>
                    </div>

                </div>
            </div>

        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
