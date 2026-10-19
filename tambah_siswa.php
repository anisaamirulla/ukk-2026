
<?php
session_start();
include "config/koneksi.php";

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    echo "Anda tidak memiliki akses";
    exit;
}

$pesan = "";

if (isset($_POST['simpan'])) {
    $nisn = mysqli_real_escape_string($koneksi, $_POST['nisn']);
    $nip = mysqli_real_escape_string($koneksi, $_POST['nip']);
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $jenis_kelamin = mysqli_real_escape_string($koneksi, $_POST['jenis_kelamin']);
    $tanggal_lahir = mysqli_real_escape_string($koneksi, $_POST['tanggal_lahir']);
    $alamat = mysqli_real_escape_string($koneksi, $_POST['alamat']);
    $status_aktif = mysqli_real_escape_string($koneksi, $_POST['status_aktif']);

    $query = mysqli_query($koneksi, "
        INSERT INTO t_siswa
        (nisn, nip, nama, jenis_kelamin, tanggal_lahir, alamat, status_aktif)
        VALUES
        ('$nisn', '$nip', '$nama', '$jenis_kelamin', '$tanggal_lahir', '$alamat', '$status_aktif')
    ");

    if ($query) {
        header("Location: siswa.php");
        exit;
    } else {
        $pesan = "Data siswa gagal disimpan. Periksa kembali struktur tabel database.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tambah Siswa</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container-fluid">
    <div class="row min-vh-100">

        <!-- SIDEBAR -->
        <aside class="col-md-3 col-lg-2 bg-dark text-white p-3">

            <h4 class="text-center mb-4">
                Admin Panel
            </h4>

            <div class="nav nav-pills flex-column gap-2">
                <a href="dashboard.php" class="nav-link text-white">
                    Dashboard
                </a>

                <a href="siswa.php" class="nav-link active">
                    Data Siswa
                </a>

                <a href="guru.php" class="nav-link text-white">
                    Data Guru
                </a>

                <a href="kelas.php" class="nav-link text-white">
                    Data Kelas
                </a>

                <a href="tahun_ajaran.php" class="nav-link text-white">
                    Tahun Ajaran
                </a>

                <a href="penempatan_siswa.php" class="nav-link text-white">
                    Penempatan Siswa
                </a>

                <a href="wali_kelas.php" class="nav-link text-white">
                    Wali Kelas
                </a>

                <a href="kategori_pelanggaran.php" class="nav-link text-white">
                    Kategori Pelanggaran
                </a>

                <a href="jenis_pelanggaran.php" class="nav-link text-white">
                    Jenis Pelanggaran
                </a>

                <a href="laporan.php" class="nav-link text-white">
                    Laporan
                </a>

                <a href="abautme.php" class="nav-link text-white">
                    About Me
                </a>

                <hr class="text-secondary">

                <a href="logout.php" class="nav-link text-danger">
                    Logout
                </a>
            </div>

        </aside>

        <!-- KONTEN TAMBAH SISWA -->
        <main class="col-md-9 col-lg-10 p-3 p-md-4">

            <!-- JUDUL HALAMAN -->
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">

                <div>
                    <h2 class="fw-bold mb-1">
                        Tambah Data Siswa
                    </h2>

                    <p class="text-secondary mb-0">
                        Silakan isi formulir data siswa dengan lengkap.
                    </p>
                </div>

                <a href="siswa.php" class="btn btn-outline-secondary">
                    Kembali ke Data Siswa
                </a>

            </div>

            <?php if ($pesan != "") { ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($pesan); ?>

                    <button type="button" class="btn-close"
                            data-bs-dismiss="alert" aria-label="Tutup"></button>
                </div>
            <?php } ?>

            <!-- FORMULIR -->
            <div class="row justify-content-center">
                <div class="col-12 col-xl-10">

                    <div class="card border-0 shadow-sm">

                        <div class="card-header bg-white p-3">
                            <h5 class="fw-bold mb-1">
                                <span class="text-primary">Formulir Siswa</span>
                            </h5>

                            <small class="text-secondary">
                                Isi informasi siswa pada kolom yang tersedia.
                            </small>
                        </div>

                        <div class="card-body p-3 p-md-4">

                            <form method="POST" action="">

                                <div class="row g-3">

                                    <div class="col-md-6">
                                        <label for="nisn" class="form-label fw-semibold">
                                            NISN <span class="text-danger">*</span>
                                        </label>

                                        <input type="text" name="nisn" id="nisn"
                                            class="form-control"
                                            placeholder="Masukkan NISN"
                                            required>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="nip" class="form-label fw-semibold">
                                            NIP
                                        </label>

                                        <input type="text" name="nip" id="nip"
                                            class="form-control"
                                            placeholder="Masukkan NIP jika ada">
                                    </div>

                                    <div class="col-12">
                                        <label for="nama" class="form-label fw-semibold">
                                            Nama Siswa <span class="text-danger">*</span>
                                        </label>

                                        <input type="text" name="nama" id="nama"
                                            class="form-control"
                                            placeholder="Masukkan nama lengkap siswa"
                                            required>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="jenis_kelamin" class="form-label fw-semibold">
                                            Jenis Kelamin <span class="text-danger">*</span>
                                        </label>

                                        <select name="jenis_kelamin" id="jenis_kelamin"
                                            class="form-select" required>
                                            <option value="">-- Pilih Jenis Kelamin --</option>
                                            <option value="Laki-laki">Laki-laki</option>
                                            <option value="Perempuan">Perempuan</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="tanggal_lahir" class="form-label fw-semibold">
                                            Tanggal Lahir <span class="text-danger">*</span>
                                        </label>

                                        <input type="date" name="tanggal_lahir"
                                            id="tanggal_lahir"
                                            class="form-control" required>
                                    </div>

                                    <div class="col-12">
                                        <label for="alamat" class="form-label fw-semibold">
                                            Alamat
                                        </label>

                                        <textarea name="alamat" id="alamat"
                                            class="form-control" rows="3"
                                            placeholder="Masukkan alamat siswa"></textarea>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="status_aktif" class="form-label fw-semibold">
                                            Status <span class="text-danger">*</span>
                                        </label>

                                        <select name="status_aktif" id="status_aktif"
                                            class="form-select" required>
                                            <option value="Aktif">Aktif</option>
                                            <option value="Tidak Aktif">Tidak Aktif</option>
                                        </select>
                                    </div>

                                </div>

                                <hr class="my-4">

                                <div class="d-flex flex-wrap justify-content-end gap-2">
                                    <a href="siswa.php" class="btn btn-secondary">
                                        Batal
                                    </a>

                                    <button type="submit" name="simpan"
                                        class="btn btn-primary px-4">
                                        Simpan Data Siswa
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

        </main>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
