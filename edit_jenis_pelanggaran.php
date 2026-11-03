
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

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: jenis_pelanggaran.php");
    exit;
}

$id = (int) $_GET['id'];

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT * FROM t_pelanggaran WHERE id = ?"
);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$hasil = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($hasil);

if (!$data) {
    exit("Data jenis pelanggaran tidak ditemukan.");
}

$kategori = mysqli_query(
    $koneksi,
    "SELECT id, nama FROM t_pelanggaran_kategori
     WHERE status_aktif = 1 OR id = " . (int)$data['pelanggaran_kategori_id'] . "
     ORDER BY nama ASC"
);

if (!$kategori) {
    die("Gagal mengambil kategori: " . mysqli_error($koneksi));
}

$pesan = "";

if (isset($_POST['simpan'])) {
    $kode = trim($_POST['kode'] ?? '');
    $nama = trim($_POST['nama'] ?? '');
    $kategori_id = (int) ($_POST['pelanggaran_kategori_id'] ?? 0);
    $poin = filter_var($_POST['poin'] ?? '', FILTER_VALIDATE_INT);
    $deksripsi = trim($_POST['deksripsi'] ?? '');
    $status_aktif = filter_var(
        $_POST['status_aktif'] ?? '',
        FILTER_VALIDATE_INT
    );

    if ($kode == '' || $nama == '') {
        $pesan = "Kode dan nama pelanggaran wajib diisi.";
    } elseif ($poin === false || $poin < 0) {
        $pesan = "Poin harus berupa angka nol atau lebih.";
    } elseif (!in_array($status_aktif, [0, 1], true)) {
        $pesan = "Status tidak valid.";
    } else {
        $cek = mysqli_prepare(
            $koneksi,
            "SELECT id FROM t_pelanggaran_kategori WHERE id = ?"
        );
        mysqli_stmt_bind_param($cek, "i", $kategori_id);
        mysqli_stmt_execute($cek);
        $hasil_kategori = mysqli_stmt_get_result($cek);

        if (mysqli_num_rows($hasil_kategori) == 0) {
            $pesan = "Kategori pelanggaran tidak ditemukan.";
        } else {
            $update = mysqli_prepare(
                $koneksi,
                "UPDATE t_pelanggaran
                 SET kode = ?,
                     nama = ?,
                     pelanggaran_kategori_id = ?,
                     poin = ?,
                     deksripsi = ?,
                     status_aktif = ?
                 WHERE id = ?"
            );

            mysqli_stmt_bind_param(
                $update,
                "ssiisii",
                $kode,
                $nama,
                $kategori_id,
                $poin,
                $deksripsi,
                $status_aktif,
                $id
            );

            if (mysqli_stmt_execute($update)) {
                header("Location: jenis_pelanggaran.php?pesan=edit");
                exit;
            } else {
                $pesan = "Gagal memperbarui data. Pastikan kode tidak digunakan oleh data lain.";
            }
        }
    }

    $data['kode'] = $kode;
    $data['nama'] = $nama;
    $data['pelanggaran_kategori_id'] = $kategori_id;
    $data['poin'] = $_POST['poin'] ?? 0;
    $data['deksripsi'] = $deksripsi;
    $data['status_aktif'] = $status_aktif;
}

// DATA TAMPILAN ADMIN
$namaAdmin = htmlspecialchars(
    $_SESSION['name'] ?? 'Pengguna',
    ENT_QUOTES,
    'UTF-8'
);

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

    <title>Edit Jenis Pelanggaran - Sistem Informasi Pelanggaran Siswa</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container-fluid">
    <div class="row">

        <!-- SIDEBAR ADMIN -->
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
                        <div class="fw-semibold"><?= $namaAdmin; ?></div>
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
                            class="nav-link <?= $item[0] === 'Jenis Pelanggaran' ? 'active' : 'text-white'; ?> rounded-2 py-2 px-3"
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

        <!-- KONTEN EDIT JENIS PELANGGARAN -->
        <main class="col-12 col-md-9 col-lg-10 p-3 p-lg-5">

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-4 border-bottom">
                <div>
                    <h2 class="fw-bold mb-2">Edit Jenis Pelanggaran</h2>
                    <p class="text-secondary mb-0">
                        Silakan perbarui data jenis pelanggaran dengan lengkap.
                    </p>
                </div>
            </div>

            <!-- PESAN ERROR -->
            <?php if ($pesan != ''): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($pesan, ENT_QUOTES, 'UTF-8'); ?>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"
                        aria-label="Tutup"
                    ></button>
                </div>
            <?php endif; ?>

            <!-- FORM EDIT -->
            <div class="row justify-content-center">
                <div class="col-12 col-xl-10">

                    <div class="card border-0 shadow-sm rounded-3">

                        <div class="card-header bg-white p-3">
                            <h5 class="text-primary fw-bold mb-1">
                                Formulir Jenis Pelanggaran
                            </h5>

                            <small class="text-secondary">
                                Perbarui informasi jenis pelanggaran pada kolom yang tersedia.
                            </small>
                        </div>

                        <div class="card-body p-4">

                            <form method="POST" action="">

                                <div class="row g-3">

                                    <div class="col-md-6">
                                        <label for="kode" class="form-label">
                                            Kode Pelanggaran <span class="text-danger">*</span>
                                        </label>

                                        <input
                                            type="text"
                                            name="kode"
                                            id="kode"
                                            class="form-control"
                                            placeholder="Masukkan kode pelanggaran"
                                            value="<?= htmlspecialchars($data['kode'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                            required
                                        >
                                    </div>

                                    <div class="col-md-6">
                                        <label for="nama" class="form-label">
                                            Nama Pelanggaran <span class="text-danger">*</span>
                                        </label>

                                        <input
                                            type="text"
                                            name="nama"
                                            id="nama"
                                            class="form-control"
                                            placeholder="Masukkan nama pelanggaran"
                                            value="<?= htmlspecialchars($data['nama'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                            required
                                        >
                                    </div>

                                    <div class="col-md-6">
                                        <label for="pelanggaran_kategori_id" class="form-label">
                                            Kategori Pelanggaran <span class="text-danger">*</span>
                                        </label>

                                        <select
                                            name="pelanggaran_kategori_id"
                                            id="pelanggaran_kategori_id"
                                            class="form-select"
                                            required
                                        >
                                            <option value="">-- Pilih Kategori --</option>

                                            <?php while ($k = mysqli_fetch_assoc($kategori)): ?>
                                                <option
                                                    value="<?= (int)$k['id']; ?>"
                                                    <?= (int)$data['pelanggaran_kategori_id'] == (int)$k['id'] ? 'selected' : ''; ?>
                                                >
                                                    <?= htmlspecialchars($k['nama'], ENT_QUOTES, 'UTF-8'); ?>
                                                </option>
                                            <?php endwhile; ?>

                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="poin" class="form-label">
                                            Poin Pelanggaran <span class="text-danger">*</span>
                                        </label>

                                        <input
                                            type="number"
                                            name="poin"
                                            id="poin"
                                            class="form-control"
                                            min="0"
                                            placeholder="Masukkan jumlah poin"
                                            value="<?= htmlspecialchars((string)($data['poin'] ?? 0), ENT_QUOTES, 'UTF-8'); ?>"
                                            required
                                        >
                                    </div>

                                    <div class="col-12">
                                        <label for="deksripsi" class="form-label">
                                            Deskripsi
                                        </label>

                                        <textarea
                                            name="deksripsi"
                                            id="deksripsi"
                                            class="form-control"
                                            rows="3"
                                            placeholder="Masukkan deskripsi pelanggaran"
                                        ><?= htmlspecialchars($data['deksripsi'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="status_aktif" class="form-label">
                                            Status <span class="text-danger">*</span>
                                        </label>

                                        <select
                                            name="status_aktif"
                                            id="status_aktif"
                                            class="form-select"
                                            required
                                        >
                                            <option
                                                value="1"
                                                <?= ($data['status_aktif'] ?? 0) == 1 ? 'selected' : ''; ?>
                                            >
                                                Aktif
                                            </option>

                                            <option
                                                value="0"
                                                <?= ($data['status_aktif'] ?? 0) == 0 ? 'selected' : ''; ?>
                                            >
                                                Tidak Aktif
                                            </option>
                                        </select>
                                    </div>

                                </div>

                                <hr class="my-4">

                                <div class="d-flex justify-content-end gap-2">
                                    <a href="jenis_pelanggaran.php" class="btn btn-secondary">
                                        Batal
                                    </a>

                                    <button type="submit" name="simpan" class="btn btn-primary">
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
