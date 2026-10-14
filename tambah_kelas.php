
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
$nama_kelas = "";
$tingkat = "";
$jurusan = "";
$status_aktif = "1";

if (isset($_POST['simpan'])) {
    $nama_kelas = trim($_POST['nama_kelas'] ?? "");
    $tingkat = trim($_POST['tingkat'] ?? "");
    $jurusan = trim($_POST['jurusan'] ?? "");
    $status_aktif = $_POST['status_aktif'] ?? "1";

    if (
        $nama_kelas === "" ||
        $tingkat === "" ||
        $jurusan === "" ||
        !in_array($status_aktif, ["0", "1"], true)
    ) {
        $pesan = "Semua data kelas wajib diisi dengan benar.";
    } else {
        $status = (int) $status_aktif;

        $stmt = mysqli_prepare(
            $koneksi,
            "INSERT INTO t_kelas (nama, tingkat, jurusan, status_aktif)
             VALUES (?, ?, ?, ?)"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "sssi",
            $nama_kelas,
            $tingkat,
            $jurusan,
            $status
        );

        if (mysqli_stmt_execute($stmt)) {
            header("Location: kelas.php");
            exit;
        } else {
            $pesan = "Data kelas gagal disimpan: " .
                mysqli_stmt_error($stmt);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tambah Data Kelas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>

<body class="bg-light">
<div class="container-fluid">
    <div class="row">

        <!-- SIDEBAR -->
        <aside class="col-md-3 col-lg-2 bg-dark text-white min-vh-100 p-3">
            <h4 class="mb-4">Admin UKK</h4>

            <div class="nav nav-pills flex-column gap-2">
                <a href="dashboard.php" class="nav-link text-white">Dashboard</a>
                <a href="siswa.php" class="nav-link text-white">Data Siswa</a>
                <a href="guru.php" class="nav-link text-white">Data Guru</a>
                <a href="kelas.php" class="nav-link active">Data Kelas</a>
                <a href="tahun_ajaran.php" class="nav-link text-white">Tahun Ajaran</a>
                <a href="penempatan_siswa.php" class="nav-link text-white">Penempatan Siswa</a>
                <a href="wali_kelas.php" class="nav-link text-white">Wali Kelas</a>
                <a href="kategori_pelanggaran.php" class="nav-link text-white">Kategori Pelanggaran</a>
                <a href="jenis_pelanggaran.php" class="nav-link text-white">Jenis Pelanggaran</a>
                <a href="cetak_export.php" class="nav-link text-white">Cetak / Export</a>
                <a href="logout.php" class="nav-link text-danger">Logout</a>
            </div>
        </aside>

        <!-- KONTEN -->
        <main class="col-md-9 col-lg-10 p-4">
            <h2 class="fw-bold mb-1">Tambah Data Kelas</h2>
            <p class="text-secondary mb-3">Silakan isi formulir data kelas.</p>

            <a href="kelas.php" class="btn btn-secondary btn-sm mb-4">
                Kembali ke Data Kelas
            </a>
            <a href="dashboard.php" class="btn btn-outline-secondary btn-sm mb-4">
                Kembali ke Dashboard
            </a>

            <?php if ($pesan !== "") : ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($pesan) ?>
                </div>
            <?php endif; ?>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white py-3">
                    <h5 class="mb-0">Formulir Data Kelas</h5>
                </div>

                <div class="card-body p-4">
                    <form method="POST">

                        <div class="mb-3">
                            <label for="nama_kelas" class="form-label">Nama Kelas</label>
                            <input type="text" name="nama_kelas" id="nama_kelas"
                                   class="form-control"
                                   value="<?= htmlspecialchars($nama_kelas) ?>"
                                   placeholder="Contoh: X RPL 1" required>
                        </div>

                        <div class="mb-3">
                            <label for="tingkat" class="form-label">Tingkat</label>
                            <select name="tingkat" id="tingkat" class="form-select" required>
                                <option value="">-- Pilih Tingkat --</option>
                                <?php foreach (["X", "XI", "XII"] as $pilihan) : ?>
                                    <option value="<?= $pilihan ?>"
                                        <?= $tingkat === $pilihan ? "selected" : "" ?>>
                                        <?= $pilihan ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="jurusan" class="form-label">Jurusan</label>
                            <input type="text" name="jurusan" id="jurusan"
                                   class="form-control"
                                   value="<?= htmlspecialchars($jurusan) ?>"
                                   placeholder="Contoh: Rekayasa Perangkat Lunak"
                                   required>
                        </div>

                        <div class="mb-4">
                            <label for="status_aktif" class="form-label">Status</label>
                            <select name="status_aktif" id="status_aktif"
                                    class="form-select" required>
                                <option value="1"
                                    <?= $status_aktif === "1" ? "selected" : "" ?>>
                                    Aktif
                                </option>
                                <option value="0"
                                    <?= $status_aktif === "0" ? "selected" : "" ?>>
                                    Tidak Aktif
                                </option>
                            </select>
                        </div>

                        <button type="submit" name="simpan" class="btn btn-primary">
                            Simpan Data Kelas
                        </button>
                        <a href="kelas.php" class="btn btn-secondary">Batal</a>

                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
```