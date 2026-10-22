
<?php
session_start();
require_once __DIR__ . "/config/koneksi.php";

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    exit("Anda tidak memiliki akses");
}

$pesan = "";

if (isset($_POST['simpan'])) {
    $nisn = trim($_POST['nisn'] ?? '');
    $nis = trim($_POST['nis'] ?? '');
    $nama = trim($_POST['nama'] ?? '');
    $jenis_kelamin = $_POST['jenis_kelamin'] ?? '';
    $tanggal_lahir = $_POST['tanggal_lahir'] ?? '';
    $alamat = trim($_POST['alamat'] ?? '');
    $status_aktif = $_POST['status_aktif'] ?? 'Aktif';

    if (!preg_match('/^[0-9]{10}$/', $nisn)) {
        $pesan = "NISN harus tepat 10 digit angka.";
    } elseif (!preg_match('/^[0-9]{10}$/', $nis)) {
        $pesan = "NIS harus tepat 10 digit angka.";
    } elseif ($nama == '') {
        $pesan = "Nama siswa wajib diisi.";
    } elseif (!in_array($jenis_kelamin, ['L', 'P'], true)) {
        $pesan = "Pilih jenis kelamin.";
    } elseif ($tanggal_lahir == '') {
        $pesan = "Tanggal lahir wajib diisi.";
    } elseif (!in_array($status_aktif, ['Aktif', 'Tidak Aktif'], true)) {
        $pesan = "Status tidak valid.";
    } else {
        $stmt = mysqli_prepare(
            $koneksi,
            "INSERT INTO t_siswa
            (nisn, nis, nama, jenis_kelamin, tanggal_lahir, alamat, status_aktif)
            VALUES (?, ?, ?, ?, ?, ?, ?)"
        );

        if (!$stmt) {
            $pesan = "Query gagal: " . mysqli_error($koneksi);
        } else {
            mysqli_stmt_bind_param(
                $stmt,
                "sssssss",
                $nisn,
                $nis,
                $nama,
                $jenis_kelamin,
                $tanggal_lahir,
                $alamat,
                $status_aktif
            );

            if (mysqli_stmt_execute($stmt)) {
                header("Location: siswa.php");
                exit;
            } else {
                $pesan = "Data gagal disimpan. Pastikan NIS dan NISN tidak digunakan sebelumnya.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Data Siswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container-fluid">
    <div class="row min-vh-100">

        <aside class="col-md-3 col-lg-2 bg-dark p-3">
            <h4 class="text-white text-center mb-4">Admin Panel</h4>

            <ul class="nav nav-pills flex-column gap-2">
                <li class="nav-item">
                    <a href="dashboard.php" class="nav-link text-white">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a href="siswa.php" class="nav-link active">Data Siswa</a>
                </li>
                <li class="nav-item">
                    <a href="guru.php" class="nav-link text-white">Data Guru</a>
                </li>
                <li class="nav-item">
                    <a href="kelas.php" class="nav-link text-white">Data Kelas</a>
                </li>
                <li class="nav-item">
                    <a href="tahun_ajaran.php" class="nav-link text-white">Tahun Ajaran</a>
                </li>
                <li class="nav-item">
                    <a href="penempatan_siswa.php" class="nav-link text-white">Penempatan Siswa</a>
                </li>
                <li class="nav-item">
                    <a href="wali_kelas.php" class="nav-link text-white">Wali Kelas</a>
                </li>
                <li class="nav-item">
                    <a href="kategori_pelanggaran.php" class="nav-link text-white">Kategori Pelanggaran</a>
                </li>
                <li class="nav-item">
                    <a href="jenis_pelanggaran.php" class="nav-link text-white">Jenis Pelanggaran</a>
                </li>
                <li class="nav-item">
                    <a href="laporan.php" class="nav-link text-white">Laporan</a>
                </li>
                <li class="nav-item">
                    <a href="abautme.php" class="nav-link text-white">About Me</a>
                </li>
                <hr class="text-secondary">
                <li class="nav-item">
                    <a href="logout.php" class="nav-link text-danger">Logout</a>
                </li>
            </ul>
        </aside>

        <main class="col-md-9 col-lg-10 p-4">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold mb-1">Tambah Data Siswa</h2>
                    <p class="text-secondary">Silakan isi formulir data siswa dengan lengkap.</p>
                </div>
                <a href="siswa.php" class="btn btn-outline-secondary">Kembali ke Data Siswa</a>
            </div>

            <?php if ($pesan != ''): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?= htmlspecialchars($pesan); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="row justify-content-center">
                <div class="col-12 col-xl-10">

                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white p-3">
                            <h5 class="text-primary fw-bold mb-1">Formulir Siswa</h5>
                            <small class="text-secondary">Isi informasi siswa pada kolom yang tersedia.</small>
                        </div>

                        <div class="card-body p-4">
                            <form method="POST">

                                <div class="row g-3">

                                    <div class="col-md-6">
                                        <label for="nisn" class="form-label">NISN <span class="text-danger">*</span></label>
                                        <input type="text" name="nisn" id="nisn"
                                            class="form-control"
                                            placeholder="Masukkan 10 digit NISN"
                                            inputmode="numeric"
                                            maxlength="10"
                                            pattern="[0-9]{10}"
                                            oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,10)"
                                            value="<?= htmlspecialchars($_POST['nisn'] ?? '') ?>"
                                            required>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="nis" class="form-label">NIS <span class="text-danger">*</span></label>
                                        <input type="text" name="nis" id="nis"
                                            class="form-control"
                                            placeholder="Masukkan 10 digit NIS"
                                            inputmode="numeric"
                                            maxlength="10"
                                            pattern="[0-9]{10}"
                                            title="NIS harus tepat 10 digit angka"
                                            oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,10)"
                                            value="<?= htmlspecialchars($_POST['nis'] ?? '') ?>"
                                            required>
                                    </div>

                                    <div class="col-12">
                                        <label for="nama" class="form-label">Nama Siswa <span class="text-danger">*</span></label>
                                        <input type="text" name="nama" id="nama"
                                            class="form-control"
                                            placeholder="Masukkan nama lengkap siswa"
                                            value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>"
                                            required>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="jenis_kelamin" class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
                                        <select name="jenis_kelamin" id="jenis_kelamin" class="form-select" required>
                                            <option value="">-- Pilih Jenis Kelamin --</option>
                                            <option value="L" <?= ($_POST['jenis_kelamin'] ?? '') == 'L' ? 'selected' : '' ?>>Laki-laki</option>
                                            <option value="P" <?= ($_POST['jenis_kelamin'] ?? '') == 'P' ? 'selected' : '' ?>>Perempuan</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="tanggal_lahir" class="form-label">Tanggal Lahir <span class="text-danger">*</span></label>
                                        <input type="date" name="tanggal_lahir" id="tanggal_lahir"
                                            class="form-control"
                                            value="<?= htmlspecialchars($_POST['tanggal_lahir'] ?? '') ?>"
                                            required>
                                    </div>

                                    <div class="col-12">
                                        <label for="alamat" class="form-label">Alamat</label>
                                        <textarea name="alamat" id="alamat" class="form-control" rows="3"
                                            placeholder="Masukkan alamat siswa"><?= htmlspecialchars($_POST['alamat'] ?? '') ?></textarea>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="status_aktif" class="form-label">Status <span class="text-danger">*</span></label>
                                        <select name="status_aktif" id="status_aktif" class="form-select" required>
                                            <option value="Aktif" <?= ($_POST['status_aktif'] ?? 'Aktif') == 'Aktif' ? 'selected' : '' ?>>Aktif</option>
                                            <option value="Tidak Aktif" <?= ($_POST['status_aktif'] ?? '') == 'Tidak Aktif' ? 'selected' : '' ?>>Tidak Aktif</option>
                                        </select>
                                    </div>

                                </div>

                                <hr class="my-4">

                                <div class="d-flex justify-content-end gap-2">
                                    <a href="siswa.php" class="btn btn-secondary">Batal</a>
                                    <button type="submit" name="simpan" class="btn btn-primary">
                                        Simpan Data Siswa
                                    </button>
                                </div>

                                <p class="text-secondary small mt-3 mb-0">
                                    <span class="text-danger">*</span> Kolom bertanda bintang wajib diisi.
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
