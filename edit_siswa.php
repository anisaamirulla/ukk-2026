
<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    exit("Anda tidak memiliki akses");
}

require_once __DIR__ . "/config/koneksi.php";

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: siswa.php");
    exit;
}

$id = (int) $_GET['id'];
$pesan = "";

$stmt = mysqli_prepare($koneksi, "SELECT * FROM t_siswa WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$siswa = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$siswa) {
    exit("Data siswa tidak ditemukan.");
}

if (isset($_POST['update'])) {
    $nisn = trim($_POST['nisn'] ?? '');
    $nis = trim($_POST['nis'] ?? '');
    $nama = trim($_POST['nama'] ?? '');
    $jenis_kelamin = $_POST['jenis_kelamin'] ?? '';
    $tanggal_lahir = $_POST['tanggal_lahir'] ?? '';
    $status_aktif = (int) ($_POST['status_aktif'] ?? -1);

    if ($nisn === '' || $nis === '' || $nama === '' ||
        $jenis_kelamin === '' || $tanggal_lahir === '') {
        $pesan = "Semua data wajib diisi.";
    } elseif (!preg_match('/^[0-9]{10}$/', $nisn)) {
        $pesan = "NISN harus tepat 10 digit angka.";
    } elseif (!preg_match('/^[0-9]{10}$/', $nis)) {
        $pesan = "NIS harus tepat 10 digit angka.";
    } elseif (!in_array($jenis_kelamin, ['L', 'P'], true)) {
        $pesan = "Jenis kelamin tidak valid.";
    } elseif (!in_array($status_aktif, [0, 1], true)) {
        $pesan = "Status siswa tidak valid.";
    } else {
        $cek = mysqli_prepare(
            $koneksi,
            "SELECT id FROM t_siswa
             WHERE (nisn = ? OR nip = ?) AND id != ?"
        );

        mysqli_stmt_bind_param($cek, "ssi", $nisn, $nis, $id);
        mysqli_stmt_execute($cek);
        $hasilCek = mysqli_stmt_get_result($cek);

        if (mysqli_num_rows($hasilCek) > 0) {
            $pesan = "NISN atau NIS sudah digunakan siswa lain.";
        } else {
            $stmt = mysqli_prepare(
                $koneksi,
                "UPDATE t_siswa
                 SET nisn = ?, nip = ?, nama = ?,
                     jenis_kelamin = ?, tanggal_lahir = ?,
                     status_aktif = ?
                 WHERE id = ?"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "sssssii",
                $nisn,
                $nis,
                $nama,
                $jenis_kelamin,
                $tanggal_lahir,
                $status_aktif,
                $id
            );

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                mysqli_stmt_close($cek);
                header("Location: siswa.php?pesan=edit_berhasil");
                exit;
            }

            $pesan = "Gagal menyimpan perubahan.";
            mysqli_stmt_close($stmt);
        }

        mysqli_stmt_close($cek);
    }

    $siswa['nisn'] = $nisn;
    $siswa['nip'] = $nis;
    $siswa['nama'] = $nama;
    $siswa['jenis_kelamin'] = $jenis_kelamin;
    $siswa['tanggal_lahir'] = $tanggal_lahir;
    $siswa['status_aktif'] = $status_aktif;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Data Siswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container-fluid">
    <div class="row min-vh-100">

        <aside class="col-md-3 col-lg-2 bg-dark text-white p-3">
            <h4 class="mb-4">Sistem Pelanggaran</h4>

            <nav class="nav nav-pills flex-column gap-2">
                <a href="dashboard.php" class="nav-link text-white">Dashboard</a>
                <a href="siswa.php" class="nav-link active">Data Siswa</a>
                <a href="guru.php" class="nav-link text-white">Data Guru</a>
                <a href="kelas.php" class="nav-link text-white">Kelas</a>
                <a href="tahun_ajaran.php" class="nav-link text-white">Tahun Ajaran</a>
                <a href="penempatan_siswa.php" class="nav-link text-white">Penempatan Siswa</a>
                <a href="wali_kelas.php" class="nav-link text-white">Wali Kelas</a>
                <a href="kategori_pelanggaran.php" class="nav-link text-white">Kategori Pelanggaran</a>
                <a href="jenis_pelanggaran.php" class="nav-link text-white">Jenis Pelanggaran</a>
                <a href="laporan.php" class="nav-link text-white">Laporan</a>
                <a href="abautme.php" class="nav-link text-white">About Me</a>
                <hr>
                <a href="logout.php" class="nav-link text-danger">Logout</a>
            </nav>
        </aside>

        <main class="col-md-9 col-lg-10 p-4">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold">Data Siswa</h2>
                    <p class="text-secondary">Perbarui informasi data siswa.</p>
                </div>
                <a href="siswa.php" class="btn btn-outline-secondary">
                    Kembali ke Data Siswa
                </a>
            </div>

            <?php if ($pesan !== ''): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($pesan, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Edit Data Siswa</h5>
                </div>

                <div class="card-body p-4">
                    <form method="POST">
                        <div class="row">

                            <div class="col-md-6 mb-3">
                                <label for="nisn" class="form-label">NISN</label>
                                <input type="text" name="nisn" id="nisn"
                                    class="form-control"
                                    inputmode="numeric"
                                    maxlength="10" minlength="10"
                                    pattern="[0-9]{10}"
                                    oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,10)"
                                    value="<?= htmlspecialchars($siswa['nisn'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                    required>
                                <div class="form-text">Wajib tepat 10 digit angka.</div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="nis" class="form-label">NIS</label>
                                <input type="text" name="nis" id="nis"
                                    class="form-control"
                                    inputmode="numeric"
                                    maxlength="10" minlength="10"
                                    pattern="[0-9]{10}"
                                    oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,10)"
                                    value="<?= htmlspecialchars($siswa['nip'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                    required>
                                <div class="form-text">Wajib tepat 10 digit angka.</div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="nama" class="form-label">Nama Siswa</label>
                                <input type="text" name="nama" id="nama"
                                    class="form-control"
                                    value="<?= htmlspecialchars($siswa['nama'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                    required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="jenis_kelamin" class="form-label">Jenis Kelamin</label>
                                <select name="jenis_kelamin" id="jenis_kelamin"
                                    class="form-select" required>
                                    <option value="L" <?= ($siswa['jenis_kelamin'] ?? '') === 'L' ? 'selected' : '' ?>>Laki-laki</option>
                                    <option value="P" <?= ($siswa['jenis_kelamin'] ?? '') === 'P' ? 'selected' : '' ?>>Perempuan</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="tanggal_lahir" class="form-label">Tanggal Lahir</label>
                                <input type="date" name="tanggal_lahir" id="tanggal_lahir"
                                    class="form-control"
                                    value="<?= htmlspecialchars($siswa['tanggal_lahir'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                    required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="status_aktif" class="form-label">Status Siswa</label>
                                <select name="status_aktif" id="status_aktif"
                                    class="form-select" required>
                                    <option value="1" <?= (int)($siswa['status_aktif'] ?? 0) === 1 ? 'selected' : '' ?>>Aktif</option>
                                    <option value="0" <?= (int)($siswa['status_aktif'] ?? 0) === 0 ? 'selected' : '' ?>>Tidak Aktif</option>
                                </select>
                            </div>

                        </div>

                        <hr>

                        <button type="submit" name="update" class="btn btn-primary">
                            Simpan Perubahan
                        </button>
                        <a href="siswa.php" class="btn btn-secondary">Batal</a>
                    </form>
                </div>
            </div>

        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
