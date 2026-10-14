```php
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
    $nama = trim($_POST['nama']);
    $deskripsi = trim($_POST['deksripsi']);
    $status_aktif = (int) $_POST['status_aktif'];

    if ($nama === "" || $deskripsi === "") {
        $pesan = "Nama kategori dan deskripsi wajib diisi.";
    } else {
        $stmt = mysqli_prepare(
            $koneksi,
            "INSERT INTO t_pelanggaran_kategori
            (nama, deksripsi, status_aktif)
            VALUES (?, ?, ?)"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "ssi",
            $nama,
            $deskripsi,
            $status_aktif
        );

        if (mysqli_stmt_execute($stmt)) {
            header("Location: kategori_pelanggaran.php");
            exit;
        } else {
            $pesan = "Gagal menyimpan data: " . mysqli_error($koneksi);
        }

        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Kategori Pelanggaran</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>

<body class="bg-light">

<div class="container-fluid">
    <div class="row">

        <!-- SIDEBAR -->
        <aside class="col-md-3 col-lg-2 bg-dark text-white min-vh-100 p-3">
            <h5 class="text-center mb-4">Admin UKK 2026</h5>
            <hr>

            <ul class="nav nav-pills flex-column gap-2">
                <li class="nav-item">
                    <a href="dashboard.php" class="nav-link text-white">
                        Dashboard
                    </a>
                </li>

                <li class="nav-item">
                    <a href="siswa.php" class="nav-link text-white">
                        Data Siswa
                    </a>
                </li>

                <li class="nav-item">
                    <a href="guru.php" class="nav-link text-white">
                        Data Guru
                    </a>
                </li>

                <li class="nav-item">
                    <a href="kelas.php" class="nav-link text-white">
                        Data Kelas
                    </a>
                </li>

                <li class="nav-item">
                    <a href="tahun_ajaran.php" class="nav-link text-white">
                        Tahun Ajaran
                    </a>
                </li>

                <li class="nav-item">
                    <a href="penempatan_siswa.php" class="nav-link text-white">
                        Penempatan Siswa
                    </a>
                </li>

                <li class="nav-item">
                    <a href="wali_kelas.php" class="nav-link text-white">
                        Wali Kelas
                    </a>
                </li>

                <li class="nav-item">
                    <a href="kategori_pelanggaran.php" class="nav-link active">
                        Kategori Pelanggaran
                    </a>
                </li>

                <li class="nav-item">
                    <a href="jenis_pelanggaran.php" class="nav-link text-white">
                        Jenis Pelanggaran
                    </a>
                </li>

                <li class="nav-item">
                    <a href="cetak_export.php" class="nav-link text-white">
                        Cetak / Export
                    </a>
                </li>

                <li class="nav-item mt-3">
                    <a href="logout.php" class="nav-link text-white">
                        Logout
                    </a>
                </li>
            </ul>
        </aside>

        <!-- KONTEN -->
        <main class="col-md-9 col-lg-10 p-4">

            <div class="mb-4">
                <h3 class="fw-bold">Tambah Kategori Pelanggaran</h3>
                <p class="text-secondary mb-0">
                    Tambahkan kategori pelanggaran siswa.
                </p>
            </div>

            <div class="mb-3">
                <a href="dashboard.php" class="btn btn-secondary">
                    Kembali ke Dashboard
                </a>

                <a href="kategori_pelanggaran.php" class="btn btn-outline-primary">
                    Data Kategori
                </a>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Form Tambah Kategori</h5>
                </div>

                <div class="card-body p-4">

                    <?php if ($pesan !== ""): ?>
                        <div class="alert alert-danger">
                            <?= htmlspecialchars($pesan); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">

                        <div class="mb-3">
                            <label for="nama" class="form-label">
                                Nama Kategori
                            </label>

                            <input
                                type="text"
                                class="form-control"
                                id="nama"
                                name="nama"
                                placeholder="Contoh: Terlambat"
                                value="<?= htmlspecialchars($_POST['nama'] ?? ''); ?>"
                                required
                            >
                        </div>

                        <div class="mb-3">
                            <label for="deksripsi" class="form-label">
                                Deskripsi
                            </label>

                            <textarea
                                class="form-control"
                                id="deksripsi"
                                name="deksripsi"
                                rows="4"
                                placeholder="Masukkan deskripsi kategori"
                                required
                            ><?= htmlspecialchars($_POST['deksripsi'] ?? ''); ?></textarea>
                        </div>

                        <div class="mb-4">
                            <label for="status_aktif" class="form-label">
                                Status
                            </label>

                            <select
                                class="form-select"
                                id="status_aktif"
                                name="status_aktif"
                                required
                            >
                                <option value="1"
                                    <?= (($_POST['status_aktif'] ?? '1') == '1') ? 'selected' : ''; ?>>
                                    Aktif
                                </option>

                                <option value="0"
                                    <?= (($_POST['status_aktif'] ?? '') == '0') ? 'selected' : ''; ?>>
                                    Tidak Aktif
                                </option>
                            </select>
                        </div>

                        <button type="submit" name="simpan" class="btn btn-primary">
                            Simpan Kategori
                        </button>

                        <a href="kategori_pelanggaran.php" class="btn btn-outline-secondary">
                            Batal
                        </a>

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