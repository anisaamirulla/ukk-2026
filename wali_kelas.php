```php
<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    echo "Anda tidak memiliki akses";
    exit;
}

require_once __DIR__ . "/config/koneksi.php";

/* HAPUS DATA */
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];

    $stmt = mysqli_prepare(
        $koneksi,
        "DELETE FROM t_wali_kelas WHERE id = ?"
    );

    if (!$stmt) {
        die("Gagal menyiapkan penghapusan: " . mysqli_error($koneksi));
    }

    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    header("Location: wali_kelas.php?pesan=hapus");
    exit;
}

/* AMBIL DATA WALI KELAS */
$data = mysqli_query($koneksi, "
    SELECT
        wk.id,
        ta.nama AS tahun_ajaran,
        k.nama AS nama_kelas,
        g.nip,
        g.nama AS nama_guru,
        wk.tanggal_mulai,
        wk.tanggal_selesai,
        wk.status_aktif
    FROM t_wali_kelas wk
    LEFT JOIN t_tahun_ajaran ta ON wk.tahun_ajaran_id = ta.id
    LEFT JOIN t_kelas k ON wk.kelas_id = k.id
    LEFT JOIN t_guru g ON wk.guru_id = g.id
    ORDER BY wk.id DESC
");

if (!$data) {
    die("Gagal mengambil data wali kelas: " . mysqli_error($koneksi));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Data Wali Kelas</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>

<body class="bg-light">

<div class="container-fluid">
    <div class="row">

        <!-- SIDEBAR -->
        <div class="col-md-3 col-lg-2 bg-dark min-vh-100 p-3">

            <h4 class="text-white mb-4">Admin UKK</h4>

            <ul class="nav flex-column gap-1">

                <li class="nav-item">
                    <a href="dashboard.php" class="nav-link text-white rounded">
                        Dashboard
                    </a>
                </li>

                <li class="nav-item">
                    <a href="siswa.php" class="nav-link text-white rounded">
                        Data Siswa
                    </a>
                </li>

                <li class="nav-item">
                    <a href="guru.php" class="nav-link text-white rounded">
                        Data Guru
                    </a>
                </li>

                <li class="nav-item">
                    <a href="kelas.php" class="nav-link text-white rounded">
                        Data Kelas
                    </a>
                </li>

                <li class="nav-item">
                    <a href="tahun_ajaran.php" class="nav-link text-white rounded">
                        Tahun Ajaran
                    </a>
                </li>

                <li class="nav-item">
                    <a href="penempatan_siswa.php" class="nav-link text-white rounded">
                        Penempatan Siswa
                    </a>
                </li>

                <li class="nav-item">
                    <a href="wali_kelas.php"
                       class="nav-link active bg-primary text-white rounded">
                        Wali Kelas
                    </a>
                </li>

                <li class="nav-item">
                    <a href="kategori_pelanggaran.php" class="nav-link text-white rounded">
                        Kategori Pelanggaran
                    </a>
                </li>

                <li class="nav-item">
                    <a href="jenis_pelanggaran.php" class="nav-link text-white rounded">
                        Jenis Pelanggaran
                    </a>
                </li>

                <li class="nav-item">
                    <a href="cetak_export.php" class="nav-link text-white rounded">
                        Cetak / Export
                    </a>
                </li>

                <li class="nav-item">
                    <a href="logout.php" class="nav-link text-danger rounded">
                        Logout
                    </a>
                </li>

            </ul>
        </div>

        <!-- KONTEN -->
        <div class="col-md-9 col-lg-10 p-4">

            <div class="d-flex justify-content-between align-items-center mb-4">

                <div>
                    <h2 class="fw-bold mb-1">Data Wali Kelas</h2>

                    <p class="text-secondary mb-2">
                        Daftar data wali kelas sekolah
                    </p>

                    <a href="dashboard.php" class="btn btn-secondary btn-sm">
                        Kembali ke Dashboard
                    </a>
                </div>

                <a href="tambah_wali_kelas.php" class="btn btn-primary">
                    + Tambah Wali Kelas
                </a>

            </div>

            <!-- PESAN -->
            <?php if (isset($_GET['pesan']) && $_GET['pesan'] === 'berhasil') { ?>
                <div class="alert alert-success">
                    Data wali kelas berhasil disimpan.
                </div>
            <?php } ?>

            <?php if (isset($_GET['pesan']) && $_GET['pesan'] === 'hapus') { ?>
                <div class="alert alert-success">
                    Data wali kelas berhasil dihapus.
                </div>
            <?php } ?>

            <!-- TABEL -->
            <div class="card border-0 shadow-sm">
                <div class="card-body">

                    <div class="table-responsive">

                        <table class="table align-middle">

                            <thead>
                                <tr>
                                    <th class="table-primary">No</th>
                                    <th class="table-primary">Tahun Ajaran</th>
                                    <th class="table-primary">Kelas</th>
                                    <th class="table-primary">NIP</th>
                                    <th class="table-primary">Nama Guru</th>
                                    <th class="table-primary">Tanggal Mulai</th>
                                    <th class="table-primary">Tanggal Selesai</th>
                                    <th class="table-primary">Status</th>
                                    <th class="table-primary">Aksi</th>
                                </tr>
                            </thead>

                            <tbody>
                            <?php
                            $no = 1;

                            if (mysqli_num_rows($data) > 0) {
                                while ($row = mysqli_fetch_assoc($data)) {
                            ?>
                                <tr>
                                    <td><?= $no++; ?></td>

                                    <td>
                                        <?= htmlspecialchars($row['tahun_ajaran'] ?? '-'); ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($row['nama_kelas'] ?? '-'); ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($row['nip'] ?? '-'); ?>
                                    </td>

                                    <td>
                                        <strong>
                                            <?= htmlspecialchars($row['nama_guru'] ?? '-'); ?>
                                        </strong>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($row['tanggal_mulai'] ?? '-'); ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($row['tanggal_selesai'] ?? '-'); ?>
                                    </td>

                                    <td>
                                        <?php if ((int)$row['status_aktif'] === 1) { ?>
                                            <span class="badge bg-success">Aktif</span>
                                        <?php } else { ?>
                                            <span class="badge bg-secondary">Tidak Aktif</span>
                                        <?php } ?>
                                    </td>

                                    <td>
                                        <a
                                            href="tambah_wali_kelas.php?edit=<?= (int)$row['id']; ?>"
                                            class="btn btn-warning btn-sm">
                                            Edit
                                        </a>

                                        <a
                                            href="wali_kelas.php?hapus=<?= (int)$row['id']; ?>"
                                            class="btn btn-danger btn-sm"
                                            onclick="return confirm('Yakin ingin menghapus data wali kelas ini?')">
                                            Hapus
                                        </a>
                                    </td>
                                </tr>
                            <?php
                                }
                            } else {
                            ?>
                                <tr>
                                    <td colspan="9" class="text-center">
                                        Belum ada data wali kelas.
                                    </td>
                                </tr>
                            <?php } ?>
                            </tbody>

                        </table>

                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

</body>
</html>
```