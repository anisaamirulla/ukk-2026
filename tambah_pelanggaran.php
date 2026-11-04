
<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

$role = strtolower(trim($_SESSION['role'] ?? ''));

if (!in_array($role, ['admin', 'guru'], true)) {
    exit("Anda tidak memiliki akses");
}

require_once __DIR__ . "/config/koneksi.php";

$pesan = "";

/* AMBIL DATA SISWA BESERTA KELAS AKTIF */
$querySiswa = mysqli_query(
    $koneksi,
    "SELECT
        ks.siswa_id,
        s.nama AS nama_siswa,
        ks.kelas_id,
        k.nama AS nama_kelas,
        ks.tahun_ajaran_id,
        ta.nama AS nama_tahun_ajaran
     FROM t_kelas_siswa ks
     INNER JOIN t_siswa s ON s.id = ks.siswa_id
     INNER JOIN t_kelas k ON k.id = ks.kelas_id
     INNER JOIN t_tahun_ajaran ta ON ta.id = ks.tahun_ajaran_id
     WHERE ks.status_aktif = 1
     ORDER BY s.nama ASC"
);

if (!$querySiswa) {
    die("Gagal mengambil data siswa: " . mysqli_error($koneksi));
}

/* AMBIL DATA JENIS PELANGGARAN */
$queryPelanggaran = mysqli_query(
    $koneksi,
    "SELECT id, pelanggaran_kategori_id, nama, poin
     FROM t_pelanggaran
     WHERE status_aktif = 1
     ORDER BY nama ASC"
);

if (!$queryPelanggaran) {
    die("Gagal mengambil jenis pelanggaran: " . mysqli_error($koneksi));
}

/* AMBIL DATA GURU */
$queryGuru = mysqli_query(
    $koneksi,
    "SELECT id, nama
     FROM t_guru
     WHERE status_aktif = 1
     ORDER BY nama ASC"
);

if (!$queryGuru) {
    die("Gagal mengambil data guru: " . mysqli_error($koneksi));
}

/* PROSES SIMPAN PELANGGARAN */
if (isset($_POST['simpan'])) {
    $siswa_id = (int)($_POST['siswa_id'] ?? 0);
    $pelanggaran_id = (int)($_POST['pelanggaran_id'] ?? 0);
    $guru_id = (int)($_POST['guru_id'] ?? 0);
    $tanggal = trim($_POST['tanggal'] ?? '');
    $keterangan = trim($_POST['keterangan'] ?? '');
    $tindakan = trim($_POST['tindakan'] ?? '');
    $status = trim($_POST['status'] ?? 'Diproses');

    if (
        $siswa_id <= 0 ||
        $pelanggaran_id <= 0 ||
        $guru_id <= 0 ||
        $tanggal === ''
    ) {
        $pesan = "Semua kolom wajib bertanda bintang harus diisi.";
    } elseif (!in_array($status, ['Diproses', 'Selesai'], true)) {
        $pesan = "Status pelanggaran tidak valid.";
    } else {

        /* AMBIL PENEMPATAN SISWA */
        $stmtSiswa = mysqli_prepare(
            $koneksi,
            "SELECT
                ks.tahun_ajaran_id,
                ks.kelas_id,
                s.nama AS nama_siswa,
                k.nama AS nama_kelas
             FROM t_kelas_siswa ks
             INNER JOIN t_siswa s ON s.id = ks.siswa_id
             INNER JOIN t_kelas k ON k.id = ks.kelas_id
             WHERE ks.siswa_id = ?
               AND ks.status_aktif = 1
             ORDER BY ks.id DESC
             LIMIT 1"
        );

        if (!$stmtSiswa) {
            die("Query siswa gagal: " . mysqli_error($koneksi));
        }

        mysqli_stmt_bind_param($stmtSiswa, "i", $siswa_id);
        mysqli_stmt_execute($stmtSiswa);
        $hasilSiswa = mysqli_stmt_get_result($stmtSiswa);
        $siswa = mysqli_fetch_assoc($hasilSiswa);
        mysqli_stmt_close($stmtSiswa);

        /* AMBIL JENIS PELANGGARAN */
        $stmtPelanggaran = mysqli_prepare(
            $koneksi,
            "SELECT id, pelanggaran_kategori_id, nama, poin
             FROM t_pelanggaran
             WHERE id = ? AND status_aktif = 1
             LIMIT 1"
        );

        if (!$stmtPelanggaran) {
            die("Query pelanggaran gagal: " . mysqli_error($koneksi));
        }

        mysqli_stmt_bind_param($stmtPelanggaran, "i", $pelanggaran_id);
        mysqli_stmt_execute($stmtPelanggaran);
        $hasilPelanggaran = mysqli_stmt_get_result($stmtPelanggaran);
        $pelanggaran = mysqli_fetch_assoc($hasilPelanggaran);
        mysqli_stmt_close($stmtPelanggaran);

        /* AMBIL GURU */
        $stmtGuru = mysqli_prepare(
            $koneksi,
            "SELECT id, nama
             FROM t_guru
             WHERE id = ? AND status_aktif = 1
             LIMIT 1"
        );

        if (!$stmtGuru) {
            die("Query guru gagal: " . mysqli_error($koneksi));
        }

        mysqli_stmt_bind_param($stmtGuru, "i", $guru_id);
        mysqli_stmt_execute($stmtGuru);
        $hasilGuru = mysqli_stmt_get_result($stmtGuru);
        $guru = mysqli_fetch_assoc($hasilGuru);
        mysqli_stmt_close($stmtGuru);

        if (!$siswa) {
            $pesan = "Siswa belum memiliki penempatan kelas yang aktif.";
        } elseif (!$pelanggaran) {
            $pesan = "Jenis pelanggaran tidak ditemukan.";
        } elseif (!$guru) {
            $pesan = "Data guru tidak ditemukan.";
        } else {

            $tahun_ajaran_id = (int)$siswa['tahun_ajaran_id'];
            $kelas_id = (int)$siswa['kelas_id'];
            $nama_siswa = $siswa['nama_siswa'];
            $nama_kelas = $siswa['nama_kelas'];

            $nama_pelanggaran = $pelanggaran['nama'];
            $pelanggaran_kategori_id = (int)$pelanggaran['pelanggaran_kategori_id'];
            $poin = (int)$pelanggaran['poin'];

            $nama_guru = $guru['nama'];

            $stmtSimpan = mysqli_prepare(
                $koneksi,
                "INSERT INTO t_pelanggaran_siswa (
                    tahun_ajaran_id,
                    siswa_id,
                    nama_siswa,
                    kelas_id,
                    nama_kelas,
                    pelanggaran_id,
                    nama_pelanggaran,
                    pelanggaran_kategori_id,
                    guru_id,
                    nama_guru,
                    tanggal,
                    keterangan,
                    poin,
                    tindakan,
                    status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );

            if ($stmtSimpan) {

                /* PERBAIKAN: 15 TIPE DATA UNTUK 15 VARIABEL */
                mysqli_stmt_bind_param(
                    $stmtSimpan,
                    "iisisisiisssiss",
                    $tahun_ajaran_id,
                    $siswa_id,
                    $nama_siswa,
                    $kelas_id,
                    $nama_kelas,
                    $pelanggaran_id,
                    $nama_pelanggaran,
                    $pelanggaran_kategori_id,
                    $guru_id,
                    $nama_guru,
                    $tanggal,
                    $keterangan,
                    $poin,
                    $tindakan,
                    $status
                );

                if (mysqli_stmt_execute($stmtSimpan)) {
                    mysqli_stmt_close($stmtSimpan);
                    header("Location: catatan_pelanggaran.php?pesan=berhasil");
                    exit;
                } else {
                    $pesan = "Gagal menyimpan catatan: " . mysqli_stmt_error($stmtSimpan);
                    mysqli_stmt_close($stmtSimpan);
                }

            } else {
                $pesan = "Query penyimpanan gagal: " . mysqli_error($koneksi);
            }
        }
    }
}

$nama = htmlspecialchars(
    $_SESSION['name'] ?? 'Pengguna',
    ENT_QUOTES,
    'UTF-8'
);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Tambah Pelanggaran - Sistem Informasi Pelanggaran Siswa</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>

<body class="bg-light overflow-hidden">

<div class="container-fluid vh-100 overflow-hidden">
    <div class="row h-100 flex-nowrap">

        <!-- SIDEBAR -->
        <aside class="col-12 col-md-3 col-lg-2 bg-dark vh-100 overflow-auto position-sticky top-0 p-3">

            <div class="text-white mb-4 pt-3 pb-3 border-bottom border-secondary">
                <h5 class="fw-bold mb-2">
                    <i class="bi bi-shield-check me-2"></i>
                    Sistem Pelanggaran
                </h5>
                <small class="text-secondary">Panel <?= strtoupper($role); ?></small>
            </div>

            <div class="bg-secondary bg-opacity-25 rounded-3 p-3 mb-4">
                <div class="d-flex align-items-center gap-3">
                    <img src="logo.png" alt="Logo Aplikasi" width="44" height="44" class="rounded-circle bg-white p-1">

                    <div class="text-white text-break">
                        <div class="fw-semibold"><?= $nama; ?></div>
                        <small class="text-secondary"><?= strtoupper($role); ?></small>
                    </div>
                </div>
            </div>

            <div class="text-secondary small fw-semibold mb-3">MENU UTAMA</div>

            <ul class="nav nav-pills flex-column gap-2">
                <li class="nav-item">
                    <a href="dashboard.php" class="nav-link text-white rounded-2 py-2 px-3">Dashboard</a>
                </li>

                <?php if ($role === 'admin'): ?>
                    <li class="nav-item"><a href="siswa.php" class="nav-link text-white rounded-2 py-2 px-3">Data Siswa</a></li>
                    <li class="nav-item"><a href="guru.php" class="nav-link text-white rounded-2 py-2 px-3">Data Guru</a></li>
                    <li class="nav-item"><a href="kelas.php" class="nav-link text-white rounded-2 py-2 px-3">Kelas</a></li>
                    <li class="nav-item"><a href="tahun_ajaran.php" class="nav-link text-white rounded-2 py-2 px-3">Tahun Ajaran</a></li>
                    <li class="nav-item"><a href="penempatan_siswa.php" class="nav-link text-white rounded-2 py-2 px-3">Penempatan Siswa</a></li>
                    <li class="nav-item"><a href="wali_kelas.php" class="nav-link text-white rounded-2 py-2 px-3">Wali Kelas</a></li>
                    <li class="nav-item"><a href="kategori_pelanggaran.php" class="nav-link text-white rounded-2 py-2 px-3">Kategori Pelanggaran</a></li>
                    <li class="nav-item"><a href="jenis_pelanggaran.php" class="nav-link text-white rounded-2 py-2 px-3">Jenis Pelanggaran</a></li>
                    <li class="nav-item"><a href="laporan.php" class="nav-link text-white rounded-2 py-2 px-3">Laporan</a></li>
                <?php endif; ?>

                <li class="nav-item">
                    <a href="catatan_pelanggaran.php" class="nav-link active rounded-2 py-2 px-3">
                        Catatan Pelanggaran
                    </a>
                </li>

                <li class="nav-item"><a href="tindakan.php" class="nav-link text-white rounded-2 py-2 px-3">Tindakan</a></li>
                <li class="nav-item"><a href="riwayat.php" class="nav-link text-white rounded-2 py-2 px-3">Riwayat</a></li>
                <li class="nav-item"><a href="rekap_poin.php" class="nav-link text-white rounded-2 py-2 px-3">Rekap Poin</a></li>
                <li class="nav-item"><a href="abautme.php" class="nav-link text-white rounded-2 py-2 px-3">About Me</a></li>
            </ul>

            <hr class="border-secondary my-4">

            <a href="logout.php" class="nav-link text-danger rounded-2 py-2 px-3">
                <i class="bi bi-box-arrow-left me-2"></i>Logout
            </a>
        </aside>

        <!-- KONTEN FORM -->
        <main class="col-12 col-md-9 col-lg-10 vh-100 overflow-auto p-3 p-lg-5">

            <div class="mb-4 pb-4 border-bottom">
                <h2 class="fw-bold mb-2">Tambah Pelanggaran Siswa</h2>
                <p class="text-secondary mb-3">
                    Masukkan catatan pelanggaran siswa pada formulir berikut.
                </p>

                <a href="catatan_pelanggaran.php" class="btn btn-secondary btn-sm">
                    Kembali ke Catatan Pelanggaran
                </a>
            </div>

            <?php if ($pesan !== ''): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($pesan, ENT_QUOTES, 'UTF-8'); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="row justify-content-center">
                <div class="col-12 col-xl-10">

                    <div class="card border-0 shadow-sm rounded-3">

                        <div class="card-header bg-white p-3">
                            <h5 class="text-primary fw-bold mb-1">Formulir Catatan Pelanggaran</h5>
                            <small class="text-secondary">
                                Lengkapi informasi pelanggaran siswa.
                            </small>
                        </div>

                        <div class="card-body p-4">

                            <form method="POST">

                                <div class="row g-3">

                                    <div class="col-md-6">
                                        <label for="siswa_id" class="form-label">
                                            Nama Siswa <span class="text-danger">*</span>
                                        </label>

                                        <select name="siswa_id" id="siswa_id" class="form-select" required>
                                            <option value="">-- Pilih Siswa --</option>

                                            <?php while ($siswa = mysqli_fetch_assoc($querySiswa)): ?>
                                                <option value="<?= (int)$siswa['siswa_id']; ?>">
                                                    <?= htmlspecialchars($siswa['nama_siswa'], ENT_QUOTES, 'UTF-8'); ?>
                                                    - <?= htmlspecialchars($siswa['nama_kelas'], ENT_QUOTES, 'UTF-8'); ?>
                                                    (<?= htmlspecialchars($siswa['nama_tahun_ajaran'], ENT_QUOTES, 'UTF-8'); ?>)
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="pelanggaran_id" class="form-label">
                                            Jenis Pelanggaran <span class="text-danger">*</span>
                                        </label>

                                        <select name="pelanggaran_id" id="pelanggaran_id" class="form-select" required>
                                            <option value="">-- Pilih Pelanggaran --</option>

                                            <?php while ($pelanggaran = mysqli_fetch_assoc($queryPelanggaran)): ?>
                                                <option value="<?= (int)$pelanggaran['id']; ?>">
                                                    <?= htmlspecialchars($pelanggaran['nama'], ENT_QUOTES, 'UTF-8'); ?>
                                                    - <?= (int)$pelanggaran['poin']; ?> poin
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="guru_id" class="form-label">
                                            Guru Pencatat <span class="text-danger">*</span>
                                        </label>

                                        <select name="guru_id" id="guru_id" class="form-select" required>
                                            <option value="">-- Pilih Guru --</option>

                                            <?php while ($guru = mysqli_fetch_assoc($queryGuru)): ?>
                                                <option value="<?= (int)$guru['id']; ?>">
                                                    <?= htmlspecialchars($guru['nama'], ENT_QUOTES, 'UTF-8'); ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="tanggal" class="form-label">
                                            Tanggal Pelanggaran <span class="text-danger">*</span>
                                        </label>

                                        <input
                                            type="date"
                                            name="tanggal"
                                            id="tanggal"
                                            class="form-control"
                                            value="<?= date('Y-m-d'); ?>"
                                            required
                                        >
                                    </div>

                                    <div class="col-12">
                                        <label for="keterangan" class="form-label">Keterangan</label>

                                        <textarea
                                            name="keterangan"
                                            id="keterangan"
                                            class="form-control"
                                            rows="3"
                                            placeholder="Masukkan keterangan pelanggaran"
                                        ></textarea>
                                    </div>

                                    <div class="col-12">
                                        <label for="tindakan" class="form-label">Tindakan</label>

                                        <textarea
                                            name="tindakan"
                                            id="tindakan"
                                            class="form-control"
                                            rows="3"
                                            placeholder="Masukkan tindakan yang diberikan"
                                        ></textarea>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="status" class="form-label">
                                            Status <span class="text-danger">*</span>
                                        </label>

                                        <select name="status" id="status" class="form-select" required>
                                            <option value="Diproses">Diproses</option>
                                            <option value="Selesai">Selesai</option>
                                        </select>
                                    </div>

                                </div>

                                <hr class="my-4">

                                <div class="d-flex justify-content-end gap-2">
                                    <a href="catatan_pelanggaran.php" class="btn btn-secondary">
                                        Batal
                                    </a>

                                    <button type="submit" name="simpan" class="btn btn-primary">
                                        Simpan Pelanggaran
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

            <div class="text-secondary small mt-5 pt-3 border-top">
                Sistem Informasi Pelanggaran Siswa
            </div>

        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
