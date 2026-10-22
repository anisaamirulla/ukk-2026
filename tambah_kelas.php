
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
$jenis_pesan = "danger";

$nama_kelas = "";
$tingkat = "";
$jurusan = "";
$tahun_ajaran_id = "";
$status_aktif = "1";

/* AMBIL DATA TAHUN AJARAN */
$query_tahun = mysqli_query(
    $koneksi,
    "SELECT id, nama, status_aktif
     FROM t_tahun_ajaran
     ORDER BY id DESC"
);

if (!$query_tahun) {
    die("Gagal mengambil data tahun ajaran: " . mysqli_error($koneksi));
}

/* PROSES SIMPAN KELAS */
if (isset($_POST['simpan'])) {

    $nama_kelas = trim($_POST['nama_kelas'] ?? "");
    $tingkat = trim($_POST['tingkat'] ?? "");
    $jurusan = trim($_POST['jurusan'] ?? "");
    $tahun_ajaran_id = trim($_POST['tahun_ajaran_id'] ?? "");
    $status_aktif = $_POST['status_aktif'] ?? "1";

    if (
        $nama_kelas === "" ||
        $tingkat === "" ||
        $jurusan === "" ||
        $tahun_ajaran_id === "" ||
        !ctype_digit($tahun_ajaran_id) ||
        !in_array($tingkat, ["X", "XI", "XII"], true) ||
        !in_array($status_aktif, ["0", "1"], true)
    ) {
        $pesan = "Semua data wajib diisi dengan benar.";

    } else {

        $id_tahun = (int) $tahun_ajaran_id;
        $status = (int) $status_aktif;

        /* CEK TAHUN AJARAN AKTIF */
        $cek_tahun = mysqli_prepare(
            $koneksi,
            "SELECT id
             FROM t_tahun_ajaran
             WHERE id = ? AND status_aktif = 1"
        );

        if (!$cek_tahun) {
            die("Gagal memeriksa tahun ajaran: " . mysqli_error($koneksi));
        }

        mysqli_stmt_bind_param($cek_tahun, "i", $id_tahun);
        mysqli_stmt_execute($cek_tahun);
        $hasil_tahun = mysqli_stmt_get_result($cek_tahun);

        if (mysqli_num_rows($hasil_tahun) === 0) {

            $pesan = "Tahun ajaran tidak ditemukan atau tidak aktif.";

        } else {

            /*
             * CEK KELAS DUPLIKAT
             * Tidak menggunakan kolom tahun_ajaran_id di t_kelas.
             */
            $cek_kelas = mysqli_prepare(
                $koneksi,
                "SELECT id
                 FROM t_kelas
                 WHERE LOWER(TRIM(nama)) = LOWER(TRIM(?))
                   AND tingkat = ?
                   AND LOWER(TRIM(jurusan)) = LOWER(TRIM(?))
                 LIMIT 1"
            );

            if (!$cek_kelas) {
                die("Gagal memeriksa data kelas: " . mysqli_error($koneksi));
            }

            mysqli_stmt_bind_param(
                $cek_kelas,
                "sss",
                $nama_kelas,
                $tingkat,
                $jurusan
            );

            mysqli_stmt_execute($cek_kelas);
            $hasil_kelas = mysqli_stmt_get_result($cek_kelas);

            if (mysqli_num_rows($hasil_kelas) > 0) {

                $pesan = "Kelas sudah terdaftar! Data tidak disimpan.";

            } else {

                /*
                 * SIMPAN KELAS
                 * Menggunakan kolom asli t_kelas:
                 * nama, tingkat, jurusan, status_aktif
                 */
                $simpan = mysqli_prepare(
                    $koneksi,
                    "INSERT INTO t_kelas
                     (nama, tingkat, jurusan, status_aktif)
                     VALUES (?, ?, ?, ?)"
                );

                if (!$simpan) {
                    die("Gagal menyiapkan penyimpanan: " . mysqli_error($koneksi));
                }

                mysqli_stmt_bind_param(
                    $simpan,
                    "sssi",
                    $nama_kelas,
                    $tingkat,
                    $jurusan,
                    $status
                );

                if (mysqli_stmt_execute($simpan)) {

                    mysqli_stmt_close($simpan);
                    mysqli_stmt_close($cek_kelas);
                    mysqli_stmt_close($cek_tahun);

                    $_SESSION['pesan_kelas'] = "Data kelas berhasil ditambahkan!";
                    $_SESSION['jenis_pesan_kelas'] = "success";

                    header("Location: kelas.php");
                    exit;

                } else {
                    $pesan = "Data kelas gagal disimpan.";
                    mysqli_stmt_close($simpan);
                }
            }

            mysqli_stmt_close($cek_kelas);
        }

        mysqli_stmt_close($cek_tahun);
    }

    /* AMBIL ULANG DATA TAHUN AJARAN */
    $query_tahun = mysqli_query(
        $koneksi,
        "SELECT id, nama, status_aktif
         FROM t_tahun_ajaran
         ORDER BY id DESC"
    );

    if (!$query_tahun) {
        die("Gagal mengambil data tahun ajaran: " . mysqli_error($koneksi));
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tambah Data Kelas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container-fluid">
    <div class="row min-vh-100">

        <!-- SIDEBAR -->
        <aside class="col-md-3 col-lg-2 bg-dark text-white p-3">
            <h4 class="text-center mb-4">Admin Panel</h4>

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
                <a href="laporan.php" class="nav-link text-white">Laporan</a>
                <a href="abautme.php" class="nav-link text-white">About Me</a>
                <hr class="text-secondary">
                <a href="logout.php" class="nav-link text-danger">Logout</a>
            </div>
        </aside>

        <!-- KONTEN UTAMA -->
        <main class="col-md-9 col-lg-10 p-3 p-md-4">

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                <div>
                    <h2 class="fw-bold mb-1">Tambah Data Kelas</h2>
                    <p class="text-secondary mb-0">Silakan isi formulir data kelas dengan lengkap.</p>
                </div>
                <a href="kelas.php" class="btn btn-outline-secondary">Kembali ke Data Kelas</a>
            </div>

            <!-- NOTIFIKASI -->
            <?php if ($pesan !== "") { ?>
                <div class="alert alert-<?= htmlspecialchars($jenis_pesan) ?> alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($pesan) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
                </div>
            <?php } ?>

            <!-- FORMULIR -->
            <div class="row justify-content-center">
                <div class="col-12 col-xl-10">

                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white p-3">
                            <h5 class="fw-bold text-primary mb-1">Formulir Kelas</h5>
                            <small class="text-secondary">Isi informasi kelas dan tahun ajaran.</small>
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
                                            placeholder="Contoh: RPL 1"
                                            required
                                        >
                                    </div>

                                    <div class="col-md-6">
                                        <label for="tingkat" class="form-label fw-semibold">
                                            Tingkat <span class="text-danger">*</span>
                                        </label>
                                        <select name="tingkat" id="tingkat" class="form-select" required>
                                            <option value="">-- Pilih Tingkat --</option>
                                            <?php foreach (["X", "XI", "XII"] as $pilihan) { ?>
                                                <option value="<?= $pilihan ?>"
                                                    <?= $tingkat === $pilihan ? "selected" : "" ?>>
                                                    <?= $pilihan ?>
                                                </option>
                                            <?php } ?>
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
                                            required
                                        >
                                    </div>

                                    <div class="col-md-6">
                                        <label for="tahun_ajaran_id" class="form-label fw-semibold">
                                            Tahun Ajaran <span class="text-danger">*</span>
                                        </label>
                                        <select name="tahun_ajaran_id" id="tahun_ajaran_id" class="form-select" required>
                                            <option value="">-- Pilih Tahun Ajaran --</option>
                                            <?php while ($tahun = mysqli_fetch_assoc($query_tahun)) { ?>
                                                <?php if ((int)$tahun['status_aktif'] === 1) { ?>
                                                    <option
                                                        value="<?= (int)$tahun['id'] ?>"
                                                        <?= (string)$tahun_ajaran_id === (string)$tahun['id'] ? "selected" : "" ?>
                                                    >
                                                        <?= htmlspecialchars($tahun['nama']) ?>
                                                    </option>
                                                <?php } ?>
                                            <?php } ?>
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="status_aktif" class="form-label fw-semibold">
                                            Status <span class="text-danger">*</span>
                                        </label>
                                        <select name="status_aktif" id="status_aktif" class="form-select" required>
                                            <option value="1" <?= $status_aktif === "1" ? "selected" : "" ?>>Aktif</option>
                                            <option value="0" <?= $status_aktif === "0" ? "selected" : "" ?>>Tidak Aktif</option>
                                        </select>
                                    </div>

                                </div>

                                <hr class="my-4">

                                <div class="d-flex flex-wrap justify-content-end gap-2">
                                    <a href="kelas.php" class="btn btn-secondary">Batal</a>
                                    <button type="submit" name="simpan" class="btn btn-primary px-4">
                                        Simpan Data Kelas
                                    </button>
                                </div>

                                <p class="text-secondary small mt-3 mb-0">
                                    <span class="text-danger">*</span> Kolom wajib diisi.
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
