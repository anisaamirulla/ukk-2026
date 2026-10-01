<?php

session_start();

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

if ($_SESSION['role'] != 'admin') {
    echo "Anda tidak memiliki akses";
    exit;
}

?>

<!DOCTYPE html>
<html>

<head>

    <title>Dashboard Admin</title>

    <!-- Bootstrap asli -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

</head>

<body>

<div class="container-fluid">

    <div class="row">

        <!-- SIDEBAR -->
        <div class="col-md-3 col-lg-2 bg-dark min-vh-100 p-3">

            <h4 class="text-white mb-4">
                Sistem Pelanggaran
            </h4>

            <ul class="nav nav-pills flex-column">

                <li class="nav-item mb-2">
                    <a href="dashboard.php"
                       class="nav-link active">
                        Dashboard
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a href="siswa.php"
                       class="nav-link text-white">
                        Data Siswa
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a href="guru.php"
                       class="nav-link text-white">
                        Data Guru
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a href="kelas.php"
                       class="nav-link text-white">
                        Kelas
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a href="tahun_ajaran.php"
                       class="nav-link text-white">
                        Tahun Ajaran
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a href="penempatan_siswa.php"
                       class="nav-link text-white">
                        Penempatan Siswa
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a href="wali_kelas.php"
                       class="nav-link text-white">
                        Wali Kelas
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a href="kategori_pelanggaran.php"
                       class="nav-link text-white">
                        Kategori Pelanggaran
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a href="jenis_pelanggaran.php"
                       class="nav-link text-white">
                        Jenis Pelanggaran
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a href="cetak_export.php"
                       class="nav-link text-white">
                        Laporan
                    </a>
                </li>

                <hr class="text-secondary">

                <li class="nav-item">
                    <a href="logout.php"
                       class="nav-link text-danger">
                        Logout
                    </a>
                </li>

            </ul>

        </div>


        <!-- KONTEN -->
        <main class="col-md-9 col-lg-10 p-4">

            <h2>Dashboard Admin</h2>

            <p>
                Selamat datang,
                <b><?= $_SESSION['name']; ?></b>
            </p>

            <div class="row">

                <!-- SISWA -->
                <div class="col-md-4 mb-3">

                    <div class="card shadow-sm">

                        <div class="card-body">

                            <h5 class="card-title">
                                Data Siswa
                            </h5>

                            <p class="card-text">
                                Kelola data siswa.
                            </p>

                            <a href="siswa.php"
                               class="btn btn-primary">
                                Lihat Data
                            </a>

                        </div>

                    </div>

                </div>


                <!-- GURU -->
                <div class="col-md-4 mb-3">

                    <div class="card shadow-sm">

                        <div class="card-body">

                            <h5 class="card-title">
                                Data Guru
                            </h5>

                            <p class="card-text">
                                Kelola data guru.
                            </p>

                            <a href="guru.php"
                               class="btn btn-primary">
                                Lihat Data
                            </a>

                        </div>

                    </div>

                </div>


                <!-- KELAS -->
                <div class="col-md-4 mb-3">

                    <div class="card shadow-sm">

                        <div class="card-body">

                            <h5 class="card-title">
                                Data Kelas
                            </h5>

                            <p class="card-text">
                                Kelola data kelas.
                            </p>

                            <a href="kelas.php"
                               class="btn btn-primary">
                                Lihat Data
                            </a>

                        </div>

                    </div>

                </div>

            </div>

        </main>

    </div>

</div>

</body>

</html>