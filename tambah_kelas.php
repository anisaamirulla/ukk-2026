
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
    $nama_kelas = mysqli_real_escape_string($koneksi, trim($_POST['nama_kelas']));
    $tingkat = mysqli_real_escape_string($koneksi, trim($_POST['tingkat']));
    $jurusan = mysqli_real_escape_string($koneksi, trim($_POST['jurusan']));

    $query = mysqli_query($koneksi, "
        INSERT INTO t_kelas (nama_kelas, tingkat, jurusan)
        VALUES ('$nama_kelas', '$tingkat', '$jurusan')
    ");

    if ($query) {
        header("Location: kelas.php");
        exit;
    } else {
        $pesan = "Data kelas gagal disimpan. Periksa nama kolom pada tabel t_kelas.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tambah Kelas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container-fluid">
    <div class="row min-vh-100">

        <!-- SIDEBAR -->
        <div class="col-md-2 bg-dark text-white p-3">
            <h4 class="mb-4">Admin UKK</h4>

            <div class="nav nav-pills flex-column gap-2">
                <a href="dashboard.php" class="nav-link text-white">Dashboard</a>
                <a href="siswa.php" class="nav-link text-white">Data Siswa</a>
                <a href="guru.php" class="nav-link text-white">Data Guru</a>
                <a href="kelas.php" class="nav-link active bg-primary text-white">Data Kelas</a>
                <a href="tahun_ajaran.php" class="nav-link text-white">Tahun Ajaran</a>
                <a href="penempatan_siswa.php" class="nav-link text-white">Penempatan Siswa</a>
                <a href="wali_kelas.php" class="nav-link text-white">Wali Kelas</a>
                <a href="kategori_pelanggaran.php" class="nav-link text-white">Kategori Pelanggaran</a>
                <a href="jenis_pelanggaran.php" class="nav-link text-white">Jenis Pelanggaran</a>
                <a href="cetak_export.php" class="nav-link text-white">Cetak / Export</a>
                <a href="logout.php" class="nav-link text-danger">Logout</a>
            </div>
        </div>

        <!-- FORM TAMBAH KELAS -->
        <div class="col-md-10 p-4 bg-light">

            <h2 class="fw-bold mb-1">Tambah Data Kelas</h2>
            <p class="text-secondary mb-4">Silakan isi formulir data kelas.</p>

            <?php if ($pesan != "") { ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($pesan); ?>
                </div>
            <?php } ?>

            <div class="bg-white p-4 rounded">
                <form method="POST" action="">

                    <div class="mb-3">
                        <label for="nama_kelas" class="form-label">Nama Kelas</label>
                        <input type="text" name="nama_kelas" id="nama_kelas"
                               class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="tingkat" class="form-label">Tingkat</label>
                        <select name="tingkat" id="tingkat" class="form-select" required>
                            <option value="">-- Pilih Tingkat --</option>
                            <option value="X">X</option>
                            <option value="XI">XI</option>
                            <option value="XII">XII</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="jurusan" class="form-label">Jurusan</label>
                        <input type="text" name="jurusan" id="jurusan"
                               class="form-control" required>
                    </div>

                    <button type="submit" name="simpan" class="btn btn-primary">
                        Simpan
                    </button>

                    <a href="kelas.php" class="btn btn-secondary">
                        Kembali
                    </a>

                </form>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
```