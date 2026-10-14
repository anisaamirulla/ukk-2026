
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
<body>

<div class="container-fluid">
    <div class="row min-vh-100">

        <!-- SIDEBAR -->
        <div class="col-md-2 bg-dark text-white p-3">
            <h4 class="text-center mb-4">Admin Panel</h4>

            <div class="nav nav-pills flex-column gap-2">
                <a href="dashboard.php" class="nav-link text-white">Dashboard</a>
                <a href="siswa.php" class="nav-link active bg-primary text-white">Data Siswa</a>
                <a href="guru.php" class="nav-link text-white">Data Guru</a>
                <a href="kelas.php" class="nav-link text-white">Data Kelas</a>
                <a href="tahun_ajaran.php" class="nav-link text-white">Tahun Ajaran</a>
                <a href="penempatan_siswa.php" class="nav-link text-white">Penempatan Siswa</a>
                <a href="wali_kelas.php" class="nav-link text-white">Wali Kelas</a>
                <a href="kategori_pelanggaran.php" class="nav-link text-white">Kategori Pelanggaran</a>
                <a href="jenis_pelanggaran.php" class="nav-link text-white">Jenis Pelanggaran</a>
                <a href="cetak_export.php" class="nav-link text-white">Cetak / Export</a>
                <a href="logout.php" class="nav-link text-white">Logout</a>
            </div>
        </div>

        <!-- KONTEN TAMBAH SISWA -->
        <div class="col-md-10 p-4">

            <h2 class="fw-bold mb-1">Tambah Data Siswa</h2>
            <p class="text-secondary mb-4">Silakan isi formulir data siswa.</p>

            <?php if ($pesan != "") { ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($pesan); ?>
                </div>
            <?php } ?>

            <div class="card shadow-sm">
                <div class="card-body p-4">

                    <form method="POST" action="">

                        <div class="mb-3">
                            <label for="nisn" class="form-label">NISN</label>
                            <input type="text" name="nisn" id="nisn"
                                class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label for="nip" class="form-label">NIP</label>
                            <input type="text" name="nip" id="nip"
                                class="form-control">
                        </div>

                        <div class="mb-3">
                            <label for="nama" class="form-label">Nama Siswa</label>
                            <input type="text" name="nama" id="nama"
                                class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label for="jenis_kelamin" class="form-label">Jenis Kelamin</label>
                            <select name="jenis_kelamin" id="jenis_kelamin"
                                class="form-select" required>
                                <option value="">-- Pilih Jenis Kelamin --</option>
                                <option value="Laki-laki">Laki-laki</option>
                                <option value="Perempuan">Perempuan</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="tanggal_lahir" class="form-label">Tanggal Lahir</label>
                            <input type="date" name="tanggal_lahir"
                                id="tanggal_lahir" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label for="alamat" class="form-label">Alamat</label>
                            <textarea name="alamat" id="alamat"
                                class="form-control" rows="3"></textarea>
                        </div>

                        <div class="mb-4">
                            <label for="status_aktif" class="form-label">Status</label>
                            <select name="status_aktif" id="status_aktif"
                                class="form-select" required>
                                <option value="Aktif">Aktif</option>
                                <option value="Tidak Aktif">Tidak Aktif</option>
                            </select>
                        </div>

                        <button type="submit" name="simpan" class="btn btn-primary">
                            Simpan
                        </button>

                        <a href="siswa.php" class="btn btn-secondary">
                            Kembali
                        </a>

                    </form>

                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
```