
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

                <a href="siswa.php" class="nav-link text-white">
                    Data Siswa
                </a>

                <a href="guru.php" class="nav-link text-white">
                    Data Guru
                </a>

                <a href="kelas.php" class="nav-link active">
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

        <!-- KONTEN TAMBAH KELAS -->
        <main class="col-md-9 col-lg-10 p-3 p-md-4">

            <!-- JUDUL HALAMAN -->
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">

                <div>
                    <h2 class="fw-bold mb-1">
                        Tambah Data Kelas
                    </h2>

                    <p class="text-secondary mb-0">
                        Silakan isi formulir data kelas dengan lengkap.
                    </p>
                </div>

                <a href="kelas.php" class="btn btn-outline-secondary">
                    Kembali ke Data Kelas
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
                                <span class="text-primary">Formulir Kelas</span>
                            </h5>

                            <small class="text-secondary">
                                Isi informasi kelas pada kolom yang tersedia.
                            </small>
                        </div>

                        <div class="card-body p-3 p-md-4">

                            <form method="POST" action="">

                                <div class="row g-3">

                                    <div class="col-md-6">
                                        <label for="nama_kelas" class="form-label fw-semibold">
                                            Nama Kelas <span class="text-danger">*</span>
                                        </label>

                                        <input
                                            type="text"
                                            name="nama_kelas"
                                            id="nama_kelas"
                                            class="form-control"
                                            value="<?= htmlspecialchars($nama_kelas) ?>"
                                            placeholder="Contoh: X RPL 1"
                                            required>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="tingkat" class="form-label fw-semibold">
                                            Tingkat <span class="text-danger">*</span>
                                        </label>

                                        <select name="tingkat" id="tingkat"
                                                class="form-select" required>
                                            <option value="">-- Pilih Tingkat --</option>

                                            <?php foreach (["X", "XI", "XII"] as $pilihan) : ?>
                                                <option value="<?= $pilihan ?>"
                                                    <?= $tingkat === $pilihan ? "selected" : "" ?>>
                                                    <?= $pilihan ?>
                                                </option>
                                            <?php endforeach; ?>

                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="jurusan" class="form-label fw-semibold">
                                            Jurusan <span class="text-danger">*</span>
                                        </label>

                                        <input
                                            type="text"
                                            name="jurusan"
                                            id="jurusan"
                                            class="form-control"
                                            value="<?= htmlspecialchars($jurusan) ?>"
                                            placeholder="Contoh: Rekayasa Perangkat Lunak"
                                            required>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="status_aktif" class="form-label fw-semibold">
                                            Status <span class="text-danger">*</span>
                                        </label>

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

                                </div>

                                <hr class="my-4">

                                <div class="d-flex flex-wrap justify-content-end gap-2">

                                    <a href="kelas.php" class="btn btn-secondary">
                                        Batal
                                    </a>

                                    <button type="submit" name="simpan"
                                            class="btn btn-primary px-4">
                                        Simpan Data Kelas
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
