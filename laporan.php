
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

// MENGAMBIL NAMA SISWA UNTUK DROPDOWN
$daftar_nama = mysqli_query(
    $koneksi,
    "SELECT DISTINCT nama_siswa
     FROM t_pelanggaran_siswa
     WHERE nama_siswa IS NOT NULL AND nama_siswa != ''
     ORDER BY nama_siswa ASC"
);

if (!$daftar_nama) {
    die("Gagal mengambil daftar nama siswa: " . mysqli_error($koneksi));
}

$cari = isset($_GET['cari']) ? trim($_GET['cari']) : '';
$query = null;

// MENGAMBIL SEMUA LAPORAN MILIK SISWA YANG DIPILIH
if ($cari !== '') {
    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT * FROM t_pelanggaran_siswa
         WHERE nama_siswa = ?
         ORDER BY tanggal DESC"
    );

    mysqli_stmt_bind_param($stmt, "s", $cari);
    mysqli_stmt_execute($stmt);
    $query = mysqli_stmt_get_result($stmt);

    if (!$query) {
        die("Gagal mengambil laporan.");
    }
}

// EXPORT HASIL PENCARIAN KE CSV YANG BISA DIBUKA DI EXCEL
if (isset($_GET['export']) && $_GET['export'] === 'excel') {
    if ($cari === '' || !$query) {
        exit("Silakan pilih nama siswa terlebih dahulu.");
    }

    header("Content-Type: text/csv; charset=UTF-8");
    header(
        "Content-Disposition: attachment; filename=laporan_pelanggaran.csv"
    );

    $output = fopen("php://output", "w");
    fwrite($output, "\xEF\xBB\xBF");

    fputcsv($output, [
        "No",
        "Nama Siswa",
        "Kelas",
        "Pelanggaran",
        "Nama Guru",
        "Tanggal",
        "Poin",
        "Status"
    ], ";");

    $no_export = 1;

    while ($data = mysqli_fetch_assoc($query)) {
        fputcsv($output, [
            $no_export++,
            $data['nama_siswa'] ?? '',
            $data['nama_kelas'] ?? '',
            $data['nama_pelanggaran'] ?? '',
            $data['nama_guru'] ?? '',
            $data['tanggal'] ?? '',
            $data['poin'] ?? '',
            $data['status'] ?? ''
        ], ";");
    }

    fclose($output);
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Laporan Pelanggaran</title>

    <!-- BOOTSTRAP 5.3 -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>

<body class="bg-light">

<div class="container-fluid">
    <div class="row">

        <!-- SIDEBAR KODE BAWAAN -->
        <div class="col-md-3 col-lg-2 bg-dark min-vh-100 p-3">

            <h4 class="text-white mb-4">Sistem Pelanggaran</h4>

            <ul class="nav nav-pills flex-column">

                <li class="nav-item mb-2">
                    <a href="dashboard.php" class="nav-link text-white">
                        Dashboard
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a href="siswa.php" class="nav-link text-white">
                        Data Siswa
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a href="guru.php" class="nav-link text-white">
                        Data Guru
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a href="kelas.php" class="nav-link text-white">
                        Kelas
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a href="tahun_ajaran.php" class="nav-link text-white">
                        Tahun Ajaran
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a href="penempatan_siswa.php" class="nav-link text-white">
                        Penempatan Siswa
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a href="wali_kelas.php" class="nav-link text-white">
                        Wali Kelas
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a href="kategori_pelanggaran.php" class="nav-link text-white">
                        Kategori Pelanggaran
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a href="jenis_pelanggaran.php" class="nav-link text-white">
                        Jenis Pelanggaran
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a href="laporan.php" class="nav-link active">
                        Laporan
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a href="abautme.php" class="nav-link text-white">
                        About Me
                    </a>
                </li>

                <hr class="text-secondary">

                <li class="nav-item">
                    <a href="logout.php" class="nav-link text-danger">
                        Logout
                    </a>
                </li>

            </ul>
        </div>

        <!-- KONTEN UTAMA -->
        <main class="col-md-9 col-lg-10 p-4">

            <div class="mb-4">
                <h3 class="fw-bold mb-1">Laporan Pelanggaran Siswa</h3>

                <p class="text-secondary mb-0">
                    Pilih nama siswa untuk melihat seluruh laporan pelanggarannya.
                </p>
            </div>

            <!-- FORM PILIH NAMA -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">

                    <form method="GET" action="laporan.php" class="mb-4">

                        <label for="cari" class="form-label fw-semibold">
                            Pilih Nama Siswa
                        </label>

                        <div class="row g-2">

                            <div class="col-md-8">
                                <select
                                    name="cari"
                                    id="cari"
                                    class="form-select"
                                    required
                                >
                                    <option value="">
                                        -- Pilih Nama Siswa --
                                    </option>

                                    <?php
                                    while ($nama = mysqli_fetch_assoc($daftar_nama)):
                                    ?>
                                        <option
                                            value="<?= htmlspecialchars($nama['nama_siswa']); ?>"
                                            <?= $cari === $nama['nama_siswa'] ? 'selected' : ''; ?>
                                        >
                                            <?= htmlspecialchars($nama['nama_siswa']); ?>
                                        </option>
                                    <?php endwhile; ?>

                                </select>
                            </div>

                            <div class="col-auto">
                                <button type="submit" class="btn btn-primary">
                                    Cari Laporan
                                </button>
                            </div>

                        </div>
                    </form>

                    <!-- TABEL HANYA MUNCUL SETELAH NAMA DIPILIH -->
                    <?php if ($cari !== ''): ?>

                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">

                            <div>
                                <h5 class="mb-1">Hasil Laporan Siswa</h5>

                                <p class="text-secondary mb-0">
                                    Nama siswa:
                                    <strong><?= htmlspecialchars($cari); ?></strong>
                                </p>
                            </div>

                            <?php if (mysqli_num_rows($query) > 0): ?>
                                <div class="d-flex gap-2">

                                    <button
                                        type="button"
                                        onclick="window.print()"
                                        class="btn btn-success"
                                    >
                                        Cetak
                                    </button>

                                    <a
                                        href="laporan.php?cari=<?= urlencode($cari); ?>&export=excel"
                                        class="btn btn-primary"
                                    >
                                        Export Excel
                                    </a>

                                </div>
                            <?php endif; ?>

                        </div>

                        <div class="table-responsive">

                            <table class="table table-bordered table-hover align-middle">

                                <thead class="table-primary">
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Siswa</th>
                                        <th>Kelas</th>
                                        <th>Pelanggaran</th>
                                        <th>Nama Guru</th>
                                        <th>Tanggal</th>
                                        <th>Poin</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php
                                    if (mysqli_num_rows($query) > 0):
                                        $no = 1;

                                        while ($data = mysqli_fetch_assoc($query)):
                                    ?>
                                        <tr>
                                            <td><?= $no++; ?></td>

                                            <td>
                                                <?= htmlspecialchars($data['nama_siswa'] ?? ''); ?>
                                            </td>

                                            <td>
                                                <?= htmlspecialchars($data['nama_kelas'] ?? ''); ?>
                                            </td>

                                            <td>
                                                <?= htmlspecialchars($data['nama_pelanggaran'] ?? ''); ?>
                                            </td>

                                            <td>
                                                <?= htmlspecialchars($data['nama_guru'] ?? ''); ?>
                                            </td>

                                            <td>
                                                <?= htmlspecialchars($data['tanggal'] ?? ''); ?>
                                            </td>

                                            <td>
                                                <?= htmlspecialchars($data['poin'] ?? ''); ?>
                                            </td>

                                            <td>
                                                <?= htmlspecialchars($data['status'] ?? ''); ?>
                                            </td>
                                        </tr>

                                    <?php
                                        endwhile;
                                    else:
                                    ?>
                                        <tr>
                                            <td colspan="8" class="text-center text-secondary py-4">
                                                Tidak ada laporan untuk siswa ini.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>

                            </table>

                        </div>

                        <a href="laporan.php" class="btn btn-secondary">
                            Kembali ke Pencarian
                        </a>

                    <?php endif; ?>

                </div>
            </div>

        </main>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
