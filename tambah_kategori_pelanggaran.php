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

$namaAdmin = htmlspecialchars(
    $_SESSION['name'] ?? 'Pengguna',
    ENT_QUOTES,
    'UTF-8'
);

$pesan = "";

if (isset($_POST['simpan'])) {
    $nama = trim($_POST['nama'] ?? '');
    $deskripsi = trim($_POST['deksripsi'] ?? '');
    $status_aktif = (int) ($_POST['status_aktif'] ?? 1);

    if ($nama === "" || $deskripsi === "") {
        $pesan = "Nama kategori dan deskripsi wajib diisi.";
    } elseif (!in_array($status_aktif, [0, 1], true)) {
        $pesan = "Status kategori tidak valid.";
    } else {
        $stmt = mysqli_prepare(
            $koneksi,
            "INSERT INTO t_pelanggaran_kategori
            (nama, deksripsi, status_aktif)
            VALUES (?, ?, ?)"
        );

        if (!$stmt) {
            $pesan = "Query gagal: " . mysqli_error($koneksi);
        } else {
            mysqli_stmt_bind_param(
                $stmt,
                "ssi",
                $nama,
                $deskripsi,
                $status_aktif
            );

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                header("Location: kategori_pelanggaran.php?pesan=berhasil");
                exit;
            } else {
                $pesan = "Gagal menyimpan data: " . mysqli_stmt_error($stmt);
                mysqli_stmt_close($stmt);
            }
        }
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

    <title>Tambah Kategori Pelanggaran - Sistem Informasi Pelanggaran Siswa</title>

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
                        <div class="fw-semibold"><?= $namaAdmin; ?></div>
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
                            class="nav-link <?= $item[0] === 'Kategori Pelanggaran' ? 'active' : 'text-white'; ?> rounded-2 py-2 px-3"
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

        <!-- KONTEN TAMBAH KATEGORI -->
        <main class="col-9 col-md-9 col-lg-10 vh-100 overflow-auto p-3 p-lg-5">

            <!-- JUDUL HALAMAN -->
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-4 border-bottom">

                <div>
                    <h2 class="fw-bold mb-2">
                        Tambah Kategori Pelanggaran
                    </h2>

                    <p class="text-secondary mb-0">
                        Silakan isi formulir kategori pelanggaran dengan lengkap.
                    </p>
                </div>

            </div>

            <!-- PESAN ERROR -->
            <?php if ($pesan !== ''): ?>

                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($pesan, ENT_QUOTES, 'UTF-8'); ?>

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
                                Formulir Kategori Pelanggaran
                            </h5>

                            <small class="text-secondary">
                                Isi informasi kategori pelanggaran pada kolom yang tersedia.
                            </small>
                        </div>

                        <div class="card-body p-4">

                            <form method="POST" action="">

                                <div class="row g-3">

                                    <!-- NAMA KATEGORI -->
                                    <div class="col-12">

                                        <label for="nama" class="form-label">
                                            Nama Kategori <span class="text-danger">*</span>
                                        </label>

                                        <input
                                            type="text"
                                            name="nama"
                                            id="nama"
                                            class="form-control"
                                            placeholder="Masukkan nama kategori"
                                            value="<?= htmlspecialchars($_POST['nama'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                            required
                                        >

                                    </div>

                                    <!-- DESKRIPSI -->
                                    <div class="col-12">

                                        <label for="deksripsi" class="form-label">
                                            Deskripsi <span class="text-danger">*</span>
                                        </label>

                                        <textarea
                                            name="deksripsi"
                                            id="deksripsi"
                                            class="form-control"
                                            rows="4"
                                            placeholder="Masukkan deskripsi kategori pelanggaran"
                                            required
                                        ><?= htmlspecialchars($_POST['deksripsi'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>

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
                                                <?= (($_POST['status_aktif'] ?? '1') == '1') ? 'selected' : ''; ?>
                                            >
                                                Aktif
                                            </option>

                                            <option
                                                value="0"
                                                <?= (($_POST['status_aktif'] ?? '') == '0') ? 'selected' : ''; ?>
                                            >
                                                Tidak Aktif
                                            </option>

                                        </select>

                                    </div>

                                </div>

                                <hr class="my-4">

                                <!-- TOMBOL -->
                                <div class="d-flex justify-content-end gap-2">

                                    <a href="kategori_pelanggaran.php" class="btn btn-secondary">
                                        Batal
                                    </a>

                                    <button type="submit" name="simpan" class="btn btn-primary">
                                        Simpan Kategori
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