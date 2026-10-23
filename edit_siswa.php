
<?php
session_start();
require_once __DIR__ . "/config/koneksi.php";

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

$role = strtolower(trim($_SESSION['role'] ?? ''));

if ($role !== 'admin') {
    exit("Anda tidak memiliki akses");
}

$nama = htmlspecialchars($_SESSION['name'] ?? 'Pengguna', ENT_QUOTES, 'UTF-8');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: siswa.php");
    exit;
}

$id = (int) $_GET['id'];
$pesan = "";

$stmt = mysqli_prepare($koneksi, "SELECT * FROM t_siswa WHERE id = ?");

if (!$stmt) {
    exit("Query gagal: " . htmlspecialchars(mysqli_error($koneksi), ENT_QUOTES, 'UTF-8'));
}

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
    $namaSiswa = trim($_POST['nama'] ?? '');
    $jenis_kelamin = $_POST['jenis_kelamin'] ?? '';
    $tanggal_lahir = $_POST['tanggal_lahir'] ?? '';
    $alamat = trim($_POST['alamat'] ?? '');
    $status_aktif = $_POST['status_aktif'] ?? 'Aktif';

    if (!preg_match('/^[0-9]{10}$/', $nisn)) {
        $pesan = "NISN harus tepat 10 digit angka.";
    } elseif (!preg_match('/^[0-9]{10}$/', $nis)) {
        $pesan = "NIS harus tepat 10 digit angka.";
    } elseif ($namaSiswa === '') {
        $pesan = "Nama siswa wajib diisi.";
    } elseif (!in_array($jenis_kelamin, ['L', 'P'], true)) {
        $pesan = "Pilih jenis kelamin.";
    } elseif ($tanggal_lahir === '') {
        $pesan = "Tanggal lahir wajib diisi.";
    } elseif (!in_array($status_aktif, ['Aktif', 'Tidak Aktif'], true)) {
        $pesan = "Status tidak valid.";
    } else {
        $cek = mysqli_prepare(
            $koneksi,
            "SELECT id FROM t_siswa
             WHERE (nisn = ? OR nis = ?) AND id != ?"
        );

        if (!$cek) {
            $pesan = "Query pemeriksaan gagal: " . mysqli_error($koneksi);
        } else {
            mysqli_stmt_bind_param($cek, "ssi", $nisn, $nis, $id);
            mysqli_stmt_execute($cek);
            $hasilCek = mysqli_stmt_get_result($cek);

            if (mysqli_num_rows($hasilCek) > 0) {
                $pesan = "NISN atau NIS sudah digunakan siswa lain.";
            } else {
                $stmt = mysqli_prepare(
                    $koneksi,
                    "UPDATE t_siswa
                     SET nisn = ?, nis = ?, nama = ?,
                         jenis_kelamin = ?, tanggal_lahir = ?,
                         alamat = ?, status_aktif = ?
                     WHERE id = ?"
                );

                if (!$stmt) {
                    $pesan = "Query update gagal: " . mysqli_error($koneksi);
                } else {
                    mysqli_stmt_bind_param(
                        $stmt,
                        "sssssssi",
                        $nisn,
                        $nis,
                        $namaSiswa,
                        $jenis_kelamin,
                        $tanggal_lahir,
                        $alamat,
                        $status_aktif,
                        $id
                    );

                    if (mysqli_stmt_execute($stmt)) {
                        mysqli_stmt_close($stmt);
                        mysqli_stmt_close($cek);

                        header("Location: siswa.php?pesan=edit_berhasil");
                        exit;
                    }

                    $pesan = "Data gagal disimpan. Pastikan NIS dan NISN tidak digunakan sebelumnya.";
                    mysqli_stmt_close($stmt);
                }
            }

            mysqli_stmt_close($cek);
        }
    }

    $siswa['nisn'] = $nisn;
    $siswa['nis'] = $nis;
    $siswa['nama'] = $namaSiswa;
    $siswa['jenis_kelamin'] = $jenis_kelamin;
    $siswa['tanggal_lahir'] = $tanggal_lahir;
    $siswa['alamat'] = $alamat;
    $siswa['status_aktif'] = $status_aktif;
}

$menuAdmin = [
    ['Dashboard', 'dashboard.php'],
    ['Data Siswa', 'siswa.php'],
    ['Data Guru', 'guru.php'],
    ['Kelas', 'kelas.php'],
    ['Tahun Ajaran', 'tahun_ajaran.php'],
    ['Penempatan Siswa', 'penempatan_siswa.php'],
    ['Wali Kelas', 'wali_kelas.php'],
    ['Kategori Pelanggaran', 'kategori_pelanggaran.php'],
    ['Jenis Pelanggaran', 'jenis_pelanggaran.php'],
    ['Laporan', 'laporan.php'],
    ['About Me', 'abautme.php']
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Edit Data Siswa - Sistem Informasi Pelanggaran Siswa</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container-fluid">
    <div class="row">

        <!-- SIDEBAR SAMA DENGAN TAMBAH SISWA -->
        <aside class="col-12 col-md-3 col-lg-2 bg-dark min-vh-100 p-3">

            <div class="text-white mb-4 pt-3 pb-3 border-bottom border-secondary">
                <h5 class="fw-bold mb-2">
                    <i class="bi bi-shield-check me-2"></i>
                    Sistem Pelanggaran
                </h5>
                <small class="text-secondary">Panel Admin</small>
            </div>

            <div class="bg-secondary bg-opacity-25 rounded-3 p-3 mb-4">
                <div class="d-flex align-items-center gap-3">
                    <img
                        src="logo.png"
                        alt="Logo Aplikasi"
                        width="44"
                        height="44"
                        class="rounded-circle bg-white p-1"
                    >

                    <div class="text-white text-break">
                        <div class="fw-semibold"><?= $nama; ?></div>
                        <small class="text-secondary">ADMIN</small>
                    </div>
                </div>
            </div>

            <div class="text-secondary small fw-semibold mb-3">
                MENU UTAMA
            </div>

            <ul class="nav nav-pills flex-column gap-2">
                <?php foreach ($menuAdmin as $item): ?>
                    <li class="nav-item">
                        <a
                            href="<?= htmlspecialchars($item[1], ENT_QUOTES, 'UTF-8'); ?>"
                            class="nav-link <?= $item[0] === 'Data Siswa' ? 'active' : 'text-white'; ?> rounded-2 py-2 px-3"
                        >
                            <?= htmlspecialchars($item[0], ENT_QUOTES, 'UTF-8'); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>

            <hr class="border-secondary my-4">

            <a href="logout.php" class="nav-link text-danger rounded-2 py-2 px-3">
                <i class="bi bi-box-arrow-left me-2"></i>
                Logout
            </a>

        </aside>

        <!-- KONTEN EDIT SISWA -->
        <main class="col-12 col-md-9 col-lg-10 p-3 p-lg-5">

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-4 border-bottom">
                <div>
                    <h2 class="fw-bold mb-2">Edit Data Siswa</h2>
                    <p class="text-secondary mb-0">
                        Silakan perbarui data siswa dengan lengkap.
                    </p>
                </div>
            </div>

            <!-- PESAN ERROR -->
            <?php if ($pesan !== ''): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($pesan, ENT_QUOTES, 'UTF-8'); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
                </div>
            <?php endif; ?>

            <!-- FORMULIR SAMA DENGAN TAMBAH SISWA -->
            <div class="row justify-content-center">
                <div class="col-12 col-xl-10">

                    <div class="card border-0 shadow-sm rounded-3">

                        <div class="card-header bg-white p-3">
                            <h5 class="text-primary fw-bold mb-1">Formulir Siswa</h5>
                            <small class="text-secondary">
                                Perbarui informasi siswa pada kolom yang tersedia.
                            </small>
                        </div>

                        <div class="card-body p-4">

                            <form method="POST" action="">

                                <div class="row g-3">

                                    <div class="col-md-6">
                                        <label for="nisn" class="form-label">
                                            NISN <span class="text-danger">*</span>
                                        </label>
                                        <input
                                            type="text"
                                            name="nisn"
                                            id="nisn"
                                            class="form-control"
                                            placeholder="Masukkan 10 digit NISN"
                                            inputmode="numeric"
                                            minlength="10"
                                            maxlength="10"
                                            pattern="[0-9]{10}"
                                            oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,10)"
                                            value="<?= htmlspecialchars($siswa['nisn'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                            required
                                        >
                                    </div>

                                    <div class="col-md-6">
                                        <label for="nis" class="form-label">
                                            NIS <span class="text-danger">*</span>
                                        </label>
                                        <input
                                            type="text"
                                            name="nis"
                                            id="nis"
                                            class="form-control"
                                            placeholder="Masukkan 10 digit NIS"
                                            inputmode="numeric"
                                            minlength="10"
                                            maxlength="10"
                                            pattern="[0-9]{10}"
                                            oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,10)"
                                            value="<?= htmlspecialchars($siswa['nis'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                            required
                                        >
                                    </div>

                                    <div class="col-12">
                                        <label for="nama" class="form-label">
                                            Nama Siswa <span class="text-danger">*</span>
                                        </label>
                                        <input
                                            type="text"
                                            name="nama"
                                            id="nama"
                                            class="form-control"
                                            placeholder="Masukkan nama lengkap siswa"
                                            value="<?= htmlspecialchars($siswa['nama'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                            required
                                        >
                                    </div>

                                    <div class="col-md-6">
                                        <label for="jenis_kelamin" class="form-label">
                                            Jenis Kelamin <span class="text-danger">*</span>
                                        </label>
                                        <select name="jenis_kelamin" id="jenis_kelamin" class="form-select" required>
                                            <option value="">-- Pilih Jenis Kelamin --</option>
                                            <option value="L" <?= ($siswa['jenis_kelamin'] ?? '') === 'L' ? 'selected' : ''; ?>>L</option>
                                            <option value="P" <?= ($siswa['jenis_kelamin'] ?? '') === 'P' ? 'selected' : ''; ?>>P</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="tanggal_lahir" class="form-label">
                                            Tanggal Lahir <span class="text-danger">*</span>
                                        </label>
                                        <input
                                            type="date"
                                            name="tanggal_lahir"
                                            id="tanggal_lahir"
                                            class="form-control"
                                            value="<?= htmlspecialchars($siswa['tanggal_lahir'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                            required
                                        >
                                    </div>

                                    <div class="col-12">
                                        <label for="alamat" class="form-label">Alamat</label>
                                        <textarea
                                            name="alamat"
                                            id="alamat"
                                            class="form-control"
                                            rows="3"
                                            placeholder="Masukkan alamat siswa"
                                        ><?= htmlspecialchars($siswa['alamat'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="status_aktif" class="form-label">
                                            Status <span class="text-danger">*</span>
                                        </label>
                                        <select name="status_aktif" id="status_aktif" class="form-select" required>
                                            <option value="Aktif" <?= ($siswa['status_aktif'] ?? 'Aktif') === 'Aktif' ? 'selected' : ''; ?>>
                                                Aktif
                                            </option>
                                            <option value="Tidak Aktif" <?= ($siswa['status_aktif'] ?? '') === 'Tidak Aktif' ? 'selected' : ''; ?>>
                                                Tidak Aktif
                                            </option>
                                        </select>
                                    </div>

                                </div>

                                <hr class="my-4">

                                <div class="d-flex justify-content-end gap-2">
                                    <a href="siswa.php" class="btn btn-secondary">
                                        Batal
                                    </a>
                                    <button type="submit" name="update" class="btn btn-primary">
                                        Simpan Perubahan
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
