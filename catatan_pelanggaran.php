
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

/* AMBIL ID GURU YANG LOGIN */
$guru_id = 0;

if ($role === 'guru') {

    $stmtGuru = mysqli_prepare(
        $koneksi,
        "SELECT id
         FROM t_guru
         WHERE user_id = ?
         LIMIT 1"
    );

    if ($stmtGuru) {
        mysqli_stmt_bind_param($stmtGuru, "i", $_SESSION['id']);
        mysqli_stmt_execute($stmtGuru);

        $hasilGuru = mysqli_stmt_get_result($stmtGuru);
        $dataGuru = mysqli_fetch_assoc($hasilGuru);

        if ($dataGuru) {
            $guru_id = (int) $dataGuru['id'];
        }

        mysqli_stmt_close($stmtGuru);
    }
}

/* PROSES SIMPAN TINDAKAN */
if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['update_tindakan'])
) {

    if ($role !== 'guru' || $guru_id <= 0) {
        exit("Anda tidak memiliki akses untuk mengubah tindakan ini.");
    }

    $idPelanggaran = (int)($_POST['id_pelanggaran'] ?? 0);
    $tindakan = trim($_POST['tindakan'] ?? '');
    $status = trim($_POST['status'] ?? '');
    $cariRedirect = trim($_POST['cari'] ?? '');

    $daftarStatus = [
        'Belum Diproses',
        'Diproses',
        'Selesai'
    ];

    if ($idPelanggaran <= 0) {
        header("Location: catatan_pelanggaran.php?pesan=gagal_tindakan");
        exit;
    }

    if ($tindakan === '' || !in_array($status, $daftarStatus, true)) {
        header("Location: catatan_pelanggaran.php?pesan=gagal_tindakan");
        exit;
    }

    $stmtUpdate = mysqli_prepare(
        $koneksi,
        "UPDATE t_pelanggaran_siswa
         SET tindakan = ?, status = ?
         WHERE id = ?
           AND guru_id = ?
           AND status <> 'Selesai'"
    );

    if (!$stmtUpdate) {
        die("Gagal menyiapkan proses: " . mysqli_error($koneksi));
    }

    mysqli_stmt_bind_param(
        $stmtUpdate,
        "ssii",
        $tindakan,
        $status,
        $idPelanggaran,
        $guru_id
    );

    if (mysqli_stmt_execute($stmtUpdate)) {
        mysqli_stmt_close($stmtUpdate);

        header(
            "Location: catatan_pelanggaran.php?pesan=tindakan_berhasil"
            . ($cariRedirect !== '' ? "&cari=" . urlencode($cariRedirect) : "")
        );
        exit;
    }

    mysqli_stmt_close($stmtUpdate);

    header("Location: catatan_pelanggaran.php?pesan=gagal_tindakan");
    exit;
}

/* PROSES AKSI SELESAI */
if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['aksi'])
    && $_POST['aksi'] === 'selesai'
) {

    if ($role !== 'guru' || $guru_id <= 0) {
        exit("Anda tidak memiliki akses untuk mengubah catatan ini.");
    }

    $idPelanggaran = (int) ($_POST['id_pelanggaran'] ?? 0);

    if ($idPelanggaran > 0) {

        $stmtSelesai = mysqli_prepare(
            $koneksi,
            "UPDATE t_pelanggaran_siswa
             SET status = 'Selesai'
             WHERE id = ?
               AND guru_id = ?
               AND status <> 'Selesai'"
        );

        if (!$stmtSelesai) {
            die("Gagal menyiapkan proses: " . mysqli_error($koneksi));
        }

        mysqli_stmt_bind_param(
            $stmtSelesai,
            "ii",
            $idPelanggaran,
            $guru_id
        );

        mysqli_stmt_execute($stmtSelesai);

        if (mysqli_stmt_affected_rows($stmtSelesai) > 0) {
            mysqli_stmt_close($stmtSelesai);

            $cariRedirect = trim($_POST['cari'] ?? '');

            header(
                "Location: catatan_pelanggaran.php?pesan=selesai"
                . ($cariRedirect !== '' ? "&cari=" . urlencode($cariRedirect) : "")
            );
            exit;
        }

        mysqli_stmt_close($stmtSelesai);

        header("Location: catatan_pelanggaran.php?pesan=gagal_selesai");
        exit;
    }
}

$cari = trim($_GET['cari'] ?? '');

/* AMBIL DATA SESUAI ROLE */

/* ADMIN DAPAT MELIHAT SEMUA CATATAN */
if ($role === 'admin') {

    if ($cari !== '') {

        $stmt = mysqli_prepare(
            $koneksi,
            "SELECT *
             FROM t_pelanggaran_siswa
             WHERE nama_siswa LIKE ?
                OR nama_kelas LIKE ?
                OR nama_pelanggaran LIKE ?
                OR nama_guru LIKE ?
             ORDER BY tanggal DESC, id DESC"
        );

        if (!$stmt) {
            die("Gagal menyiapkan query: " . mysqli_error($koneksi));
        }

        $kata = "%" . $cari . "%";

        mysqli_stmt_bind_param(
            $stmt,
            "ssss",
            $kata,
            $kata,
            $kata,
            $kata
        );

        mysqli_stmt_execute($stmt);
        $data = mysqli_stmt_get_result($stmt);

    } else {

        $data = mysqli_query(
            $koneksi,
            "SELECT *
             FROM t_pelanggaran_siswa
             ORDER BY tanggal DESC, id DESC"
        );
    }

/* GURU HANYA MELIHAT CATATAN YANG DICATAT OLEH DIRINYA */
} else {

    if ($cari !== '') {

        $stmt = mysqli_prepare(
            $koneksi,
            "SELECT ps.*
             FROM t_pelanggaran_siswa ps
             INNER JOIN t_guru g ON ps.guru_id = g.id
             WHERE g.user_id = ?
               AND ps.status <> 'Selesai'
               AND (
                    ps.nama_siswa LIKE ?
                    OR ps.nama_kelas LIKE ?
                    OR ps.nama_pelanggaran LIKE ?
                    OR ps.nama_guru LIKE ?
               )
             ORDER BY ps.tanggal DESC, ps.id DESC"
        );

        if (!$stmt) {
            die("Gagal menyiapkan query: " . mysqli_error($koneksi));
        }

        $kata = "%" . $cari . "%";

        mysqli_stmt_bind_param(
            $stmt,
            "issss",
            $_SESSION['id'],
            $kata,
            $kata,
            $kata,
            $kata
        );

        mysqli_stmt_execute($stmt);
        $data = mysqli_stmt_get_result($stmt);

    } else {

        $stmt = mysqli_prepare(
            $koneksi,
            "SELECT ps.*
             FROM t_pelanggaran_siswa ps
             INNER JOIN t_guru g ON ps.guru_id = g.id
             WHERE g.user_id = ?
               AND ps.status <> 'Selesai'
             ORDER BY ps.tanggal DESC, ps.id DESC"
        );

        if (!$stmt) {
            die("Gagal menyiapkan query: " . mysqli_error($koneksi));
        }

        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $_SESSION['id']
        );

        mysqli_stmt_execute($stmt);
        $data = mysqli_stmt_get_result($stmt);
    }
}

if (!$data) {
    die("Gagal mengambil data: " . mysqli_error($koneksi));
}

$totalData = mysqli_num_rows($data);

$nama = htmlspecialchars(
    $_SESSION['name'] ?? 'Pengguna',
    ENT_QUOTES,
    'UTF-8'
);

/* PEMERIKSAAN WALI KELAS UNTUK MENU SIDEBAR */
$isWaliKelas = false;

if ($role === 'guru' && $guru_id > 0) {

    $cekWali = mysqli_prepare(
        $koneksi,
        "SELECT id
         FROM t_wali_kelas
         WHERE guru_id = ?
           AND status_aktif = 1
           AND tanggal_mulai <= CURDATE()
           AND (
               tanggal_selesai IS NULL
               OR tanggal_selesai >= CURDATE()
           )
         LIMIT 1"
    );

    if ($cekWali) {

        mysqli_stmt_bind_param($cekWali, "i", $guru_id);
        mysqli_stmt_execute($cekWali);

        $hasilWali = mysqli_stmt_get_result($cekWali);

        if (mysqli_num_rows($hasilWali) > 0) {
            $isWaliKelas = true;
        }

        mysqli_stmt_close($cekWali);
    }
}

/* MENENTUKAN LABEL ROLE */
if ($role === 'admin') {
    $labelRole = 'ADMIN';
} elseif ($isWaliKelas) {
    $labelRole = 'WALI KELAS';
} else {
    $labelRole = 'GURU';
}

/* MENU ADMIN */
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
    ['Catatan Pelanggaran', 'catatan_pelanggaran.php'],
    ['Tindakan', 'tindakan.php'],
    ['Riwayat', 'riwayat.php'],
    ['Rekap Poin', 'rekap_poin.php'],
    ['About Me', 'abautme.php']
];

/* MENU GURU BIASA */
$menuGuru = [
    ['Dashboard', 'dashboard.php'],
    ['Catatan Pelanggaran', 'catatan_pelanggaran.php'],
    ['Riwayat', 'riwayat.php'],
    ['Rekap Poin', 'rekap_poin.php'],
    ['About Me', 'abautme.php']
];

/* MENU WALI KELAS */
$menuWaliKelas = [
    ['Dashboard', 'dashboard.php'],
    ['Data Siswa di Kelasnya', 'walikelas/data_siswa.php'],
    ['Catat Pelanggaran', 'catatan_pelanggaran.php'],
    ['Riwayat Pelanggaran Siswa', 'riwayat.php'],
    ['Rekap Poin Siswa', 'rekap_poin.php'],
    ['Laporan Pelanggaran Kelas', 'walikelas/rekap_pelanggaran.php'],
    ['About Me', 'abautme.php']
];

/* PILIH MENU */
if ($role === 'admin') {
    $menu = $menuAdmin;
} elseif ($isWaliKelas) {
    $menu = $menuWaliKelas;
} else {
    $menu = $menuGuru;
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Catatan Pelanggaran - Sistem Informasi Pelanggaran Siswa</title>

    <!-- BOOTSTRAP 5.3.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- BOOTSTRAP ICONS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- DATATABLES -->
    <link href="https://cdn.datatables.net/2.3.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">

</head>

<body class="bg-light overflow-hidden">

<div class="container-fluid vh-100 overflow-hidden">

    <div class="row h-100 flex-nowrap">

        <!-- SIDEBAR MENGIKUTI DASHBOARD -->
        <aside class="col-12 col-md-3 col-lg-2 bg-dark vh-100 overflow-auto position-sticky top-0 p-3">

            <!-- JUDUL SIDEBAR -->
            <div class="text-white mb-4 pt-3 pb-3 border-bottom border-secondary">

                <h5 class="fw-bold mb-2">
                    <i class="bi bi-shield-check me-2"></i>
                    Sistem Pelanggaran
                </h5>

                <small class="text-secondary">
                    Panel <?= $isWaliKelas && $role === 'guru' ? 'Wali Kelas' : ucfirst($role); ?>
                </small>

            </div>

            <!-- PROFIL PENGGUNA -->
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

                        <div class="fw-semibold">
                            <?= $nama; ?>
                        </div>

                        <small class="text-secondary">
                            <?= $labelRole; ?>
                        </small>

                    </div>

                </div>

            </div>

            <!-- MENU SIDEBAR TANPA ICON -->
            <div class="text-secondary small fw-semibold mb-3">
                MENU UTAMA
            </div>

            <ul class="nav nav-pills flex-column gap-2">

                <?php foreach ($menu as $item): ?>

                    <?php
                    $aktif = (
                        $item[0] === 'Catatan Pelanggaran'
                        || $item[0] === 'Catat Pelanggaran'
                    );
                    ?>

                    <li class="nav-item">

                        <a
                            href="<?= htmlspecialchars($item[1], ENT_QUOTES, 'UTF-8'); ?>"
                            class="nav-link <?= $aktif ? 'active' : 'text-white'; ?> rounded-2 py-2 px-3"
                            <?= $aktif ? 'aria-current="page"' : ''; ?>
                        >
                            <?= htmlspecialchars($item[0], ENT_QUOTES, 'UTF-8'); ?>
                        </a>

                    </li>

                <?php endforeach; ?>

            </ul>

            <hr class="border-secondary my-4">

            <!-- LOGOUT -->
            <a
                href="logout.php"
                class="nav-link text-danger rounded-2 py-2 px-3"
            >
                <i class="bi bi-box-arrow-left me-2"></i>
                Logout
            </a>

        </aside>

        <!-- KONTEN UTAMA -->
        <main class="col-12 col-md-9 col-lg-10 vh-100 overflow-auto p-3 p-lg-5">

            <!-- HEADER HALAMAN -->
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-4 border-bottom">

                <div>

                    <div class="text-secondary small mb-2">
                        Dashboard / Catatan Pelanggaran
                    </div>

                    <h2 class="fw-bold mb-2">
                        Catatan Pelanggaran Siswa
                    </h2>

                    <p class="text-secondary mb-0">
                        Kelola dan lihat catatan pelanggaran siswa.
                    </p>

                </div>

                <a
                    href="tambah_pelanggaran.php"
                    class="btn btn-primary rounded-2 px-4"
                >
                    <i class="bi bi-plus-lg me-2"></i>
                    Tambah Pelanggaran
                </a>

            </div>

            <!-- NOTIFIKASI -->
            <?php if (isset($_GET['pesan']) && $_GET['pesan'] === 'berhasil'): ?>

                <div class="alert alert-success alert-dismissible fade show rounded-3" role="alert">

                    Catatan pelanggaran berhasil ditambahkan.

                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>

                </div>

            <?php elseif (isset($_GET['pesan']) && $_GET['pesan'] === 'gagal'): ?>

                <div class="alert alert-danger alert-dismissible fade show rounded-3" role="alert">

                    Catatan pelanggaran gagal ditambahkan.

                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>

                </div>

            <?php elseif (isset($_GET['pesan']) && $_GET['pesan'] === 'tindakan_berhasil'): ?>

                <div class="alert alert-success alert-dismissible fade show rounded-3" role="alert">

                    Tindakan dan status pelanggaran berhasil diperbarui.

                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>

                </div>

            <?php elseif (isset($_GET['pesan']) && $_GET['pesan'] === 'gagal_tindakan'): ?>

                <div class="alert alert-danger alert-dismissible fade show rounded-3" role="alert">

                    Tindakan gagal disimpan. Periksa kembali data dan status yang dipilih.

                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>

                </div>

            <?php elseif (isset($_GET['pesan']) && $_GET['pesan'] === 'selesai'): ?>

                <div class="alert alert-success alert-dismissible fade show rounded-3" role="alert">

                    Status catatan pelanggaran berhasil diubah menjadi Selesai.

                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>

                </div>

            <?php elseif (isset($_GET['pesan']) && $_GET['pesan'] === 'gagal_selesai'): ?>

                <div class="alert alert-danger alert-dismissible fade show rounded-3" role="alert">

                    Status gagal diubah. Catatan mungkin sudah selesai atau bukan milik akun Anda.

                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>

                </div>

            <?php endif; ?>

            <!-- RINGKASAN DATA -->
            <div class="row g-3 mb-4">

                <div class="col-12 col-md-6 col-xl-4">

                    <div class="card border-0 shadow-sm rounded-3 h-100">

                        <div class="card-body p-4">

                            <div class="text-secondary small mb-2">
                                Total Catatan Pelanggaran
                            </div>

                            <div class="d-flex align-items-center gap-3">

                                <div class="bg-light rounded-3 p-3">
                                    <i class="bi bi-journal-text fs-3 text-dark"></i>
                                </div>

                                <div>

                                    <h3 class="fw-bold mb-0">
                                        <?= $totalData; ?>
                                    </h3>

                                    <small class="text-secondary">
                                        Data tercatat
                                    </small>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

            <!-- TABEL CATATAN PELANGGARAN -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">

                <div class="card-header bg-white border-0 p-4">

                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">

                        <div>

                            <h5 class="fw-bold mb-1">
                                Daftar Catatan Pelanggaran
                            </h5>

                            <p class="text-secondary small mb-0">
                                Daftar catatan pelanggaran siswa yang tercatat dalam sistem.
                            </p>

                        </div>

                        <span class="badge text-bg-dark rounded-pill px-3 py-2">
                            <?= $totalData; ?> Data
                        </span>

                    </div>

                </div>

                <div class="card-body p-4 pt-2">

                    <!-- FORM PENCARIAN -->
                    <form method="GET" class="row g-2 mb-4">

                        <div class="col-12 col-md-6 col-lg-5">

                            <input
                                type="text"
                                name="cari"
                                class="form-control"
                                placeholder="Cari nama siswa, kelas, pelanggaran, atau guru..."
                                value="<?= htmlspecialchars($cari, ENT_QUOTES, 'UTF-8'); ?>"
                            >

                        </div>

                        <div class="col-auto">

                            <button type="submit" class="btn btn-primary px-4">
                                Cari
                            </button>

                            <a
                                href="catatan_pelanggaran.php"
                                class="btn btn-secondary"
                            >
                                Reset
                            </a>

                        </div>

                    </form>

                    <!-- TABEL -->
                    <div class="table-responsive">

                        <table id="tabelPelanggaran" class="table table-hover table-bordered align-middle">

                            <thead class="table-light">

                                <tr>

                                    <th class="text-center">No</th>
                                    <th>Nama Siswa</th>
                                    <th>Kelas</th>
                                    <th>Jenis Pelanggaran</th>
                                    <th>Guru</th>
                                    <th>Tanggal</th>
                                    <th>Keterangan</th>
                                    <th>Poin</th>
                                    <th>Tindakan</th>
                                    <th>Status</th>

                                    <?php if ($role === 'guru'): ?>
                                        <th class="text-center">Aksi</th>
                                    <?php endif; ?>

                                </tr>

                            </thead>

                            <tbody>

                                <?php $no = 1; ?>

                                <?php while ($row = mysqli_fetch_assoc($data)): ?>

                                    <tr>

                                        <td class="text-center">
                                            <?= $no++; ?>
                                        </td>

                                        <td class="fw-semibold">
                                            <?= htmlspecialchars($row['nama_siswa'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($row['nama_kelas'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($row['nama_pelanggaran'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($row['nama_guru'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($row['tanggal'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($row['keterangan'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                        </td>

                                        <td class="text-center">

                                            <span class="badge text-bg-danger rounded-pill px-3 py-2">
                                                <?= (int)($row['poin'] ?? 0); ?> poin
                                            </span>

                                        </td>

                                        <td>
                                            <?= htmlspecialchars($row['tindakan'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                        </td>

                                        <td>

                                            <?php if (($row['status'] ?? '') === 'Diproses'): ?>

                                                <span class="badge text-bg-success rounded-pill">
                                                    Diproses
                                                </span>

                                            <?php else: ?>

                                                <span class="badge text-bg-secondary rounded-pill">
                                                    <?= htmlspecialchars($row['status'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                                </span>

                                            <?php endif; ?>

                                        </td>

                                        <?php if ($role === 'guru'): ?>

                                            <td class="text-center">

                                                <?php if (($row['status'] ?? '') !== 'Selesai'): ?>

                                                    <!-- TOMBOL BERI TINDAKAN -->
                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-primary mb-1"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#modalTindakan"
                                                        data-id="<?= (int)$row['id']; ?>"
                                                        data-siswa="<?= htmlspecialchars($row['nama_siswa'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                        data-kelas="<?= htmlspecialchars($row['nama_kelas'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                        data-pelanggaran="<?= htmlspecialchars($row['nama_pelanggaran'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                        data-guru="<?= htmlspecialchars($row['nama_guru'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                        data-tindakan="<?= htmlspecialchars($row['tindakan'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                        data-status="<?= htmlspecialchars($row['status'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                    >
                                                        <i class="bi bi-clipboard-check me-1"></i>
                                                        Beri Tindakan
                                                    </button>

                                                    <!-- TOMBOL SELESAI -->
                                                    <form method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mengubah status catatan ini menjadi Selesai?');">

                                                        <input
                                                            type="hidden"
                                                            name="aksi"
                                                            value="selesai"
                                                        >

                                                        <input
                                                            type="hidden"
                                                            name="id_pelanggaran"
                                                            value="<?= (int) $row['id']; ?>"
                                                        >

                                                        <input
                                                            type="hidden"
                                                            name="cari"
                                                            value="<?= htmlspecialchars($cari, ENT_QUOTES, 'UTF-8'); ?>"
                                                        >

                                                        <button
                                                            type="submit"
                                                            class="btn btn-sm btn-success"
                                                        >
                                                            <i class="bi bi-check-circle me-1"></i>
                                                            Selesai
                                                        </button>

                                                    </form>

                                                <?php else: ?>

                                                    <span class="badge text-bg-secondary">
                                                        Sudah Selesai
                                                    </span>

                                                <?php endif; ?>

                                            </td>

                                        <?php endif; ?>

                                    </tr>

                                <?php endwhile; ?>

                            </tbody>

                        </table>

                    </div>

                    <!-- INFORMASI TABEL -->
                    <div class="border-top pt-3 mt-3">

                        <p class="text-secondary small mb-0">
                            Menampilkan catatan pelanggaran sesuai hak akses akun yang sedang login.
                        </p>

                    </div>

                </div>

            </div>

            <!-- KEMBALI KE DASHBOARD -->
            <div class="d-flex justify-content-start mb-4">

                <a
                    href="dashboard.php"
                    class="btn btn-secondary rounded-2 px-4"
                >
                    <i class="bi bi-arrow-left me-2"></i>
                    Kembali ke Dashboard
                </a>

            </div>

            <!-- FOOTER -->
            <div class="text-secondary small mt-4 pt-3 border-top">

                <div class="d-flex flex-wrap justify-content-between gap-2">

                    <span>
                        Sistem Informasi Pelanggaran Siswa
                    </span>

                    <span>
                        Halaman Catatan Pelanggaran
                    </span>

                </div>

            </div>

        </main>

    </div>

</div>

<!-- MODAL BERI TINDAKAN -->
<?php if ($role === 'guru'): ?>

<div class="modal fade" id="modalTindakan" tabindex="-1" aria-labelledby="judulModalTindakan" aria-hidden="true">

    <div class="modal-dialog modal-lg modal-dialog-centered">

        <div class="modal-content">

            <div class="modal-header">

                <div>
                    <h5 class="modal-title text-primary fw-bold" id="judulModalTindakan">
                        Formulir Tindakan
                    </h5>

                    <small class="text-secondary">
                        Isi tindakan dan status pelanggaran siswa.
                    </small>
                </div>

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>

            </div>

            <div class="modal-body p-4">

                <form method="POST" action="catatan_pelanggaran.php<?= $cari !== '' ? '?cari=' . urlencode($cari) : ''; ?>">

                    <input type="hidden" name="update_tindakan" value="1">
                    <input type="hidden" name="id_pelanggaran" id="modal_id_pelanggaran">

                    <input
                        type="hidden"
                        name="cari"
                        value="<?= htmlspecialchars($cari, ENT_QUOTES, 'UTF-8'); ?>"
                    >

                    <div class="row g-3">

                        <div class="col-md-6">

                            <label class="form-label">Nama Siswa</label>

                            <input
                                type="text"
                                id="modal_nama_siswa"
                                class="form-control"
                                readonly
                            >

                        </div>

                        <div class="col-md-6">

                            <label class="form-label">Kelas</label>

                            <input
                                type="text"
                                id="modal_nama_kelas"
                                class="form-control"
                                readonly
                            >

                        </div>

                        <div class="col-md-6">

                            <label class="form-label">Pelanggaran</label>

                            <input
                                type="text"
                                id="modal_nama_pelanggaran"
                                class="form-control"
                                readonly
                            >

                        </div>

                        <div class="col-md-6">

                            <label class="form-label">Guru</label>

                            <input
                                type="text"
                                id="modal_nama_guru"
                                class="form-control"
                                readonly
                            >

                        </div>

                        <div class="col-12">

                            <label for="modal_tindakan" class="form-label">
                                Tindakan <span class="text-danger">*</span>
                            </label>

                            <textarea
                                name="tindakan"
                                id="modal_tindakan"
                                class="form-control"
                                rows="3"
                                maxlength="255"
                                required
                            ></textarea>

                        </div>

                        <div class="col-md-6">

                            <label for="modal_status" class="form-label">
                                Status <span class="text-danger">*</span>
                            </label>

                            <select
                                name="status"
                                id="modal_status"
                                class="form-select"
                                required
                            >

                                <option value="">-- Pilih Status --</option>
                                <option value="Belum Diproses">Belum Diproses</option>
                                <option value="Diproses">Diproses</option>
                                <option value="Selesai">Selesai</option>

                            </select>

                        </div>

                    </div>

                    <hr class="my-4">

                    <div class="d-flex justify-content-end gap-2">

                        <button
                            type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal"
                        >
                            Batal
                        </button>

                        <button
                            type="submit"
                            class="btn btn-primary"
                        >
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

<?php endif; ?>

<!-- JAVASCRIPT -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script src="https://cdn.datatables.net/2.3.4/js/dataTables.min.js"></script>

<script src="https://cdn.datatables.net/2.3.4/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function () {

    $('#tabelPelanggaran').DataTable({

        pageLength: 10,

        lengthMenu: [5, 10, 25, 50, 100],

        ordering: true,

        searching: true,

        language: {

            search: "Cari:",

            lengthMenu: "Tampilkan _MENU_ data",

            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",

            infoEmpty: "Tidak ada data",

            infoFiltered: "(disaring dari _MAX_ total data)",

            zeroRecords: "Data tidak ditemukan",

            emptyTable: "Belum ada catatan pelanggaran",

            paginate: {

                first: "Pertama",

                last: "Terakhir",

                next: "Berikutnya",

                previous: "Sebelumnya"

            }

        }

    });

});

/* MENGISI DATA FORMULIR MODAL */
const modalTindakan = document.getElementById('modalTindakan');

if (modalTindakan) {

    modalTindakan.addEventListener('show.bs.modal', function (event) {

        const tombol = event.relatedTarget;

        document.getElementById('modal_id_pelanggaran').value =
            tombol.getAttribute('data-id') || '';

        document.getElementById('modal_nama_siswa').value =
            tombol.getAttribute('data-siswa') || '';

        document.getElementById('modal_nama_kelas').value =
            tombol.getAttribute('data-kelas') || '';

        document.getElementById('modal_nama_pelanggaran').value =
            tombol.getAttribute('data-pelanggaran') || '';

        document.getElementById('modal_nama_guru').value =
            tombol.getAttribute('data-guru') || '';

        document.getElementById('modal_tindakan').value =
            tombol.getAttribute('data-tindakan') || '';

        document.getElementById('modal_status').value =
            tombol.getAttribute('data-status') || '';

    });

}
</script>

</body>
</html>
