
<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

$role = strtolower(trim($_SESSION['role'] ?? ''));

if (!in_array($role, ['admin', 'guru'])) {
    exit("Anda tidak memiliki akses");
}

include "config/koneksi.php";

$cari = trim($_GET['cari'] ?? '');

if ($cari !== '') {
    $sql = "SELECT id, nama_siswa, nama_kelas,
                   nama_pelanggaran, nama_guru,
                   tanggal, keterangan, poin, tindakan, status
            FROM t_pelanggaran_siswa
            WHERE nama_siswa LIKE ?
               OR nama_kelas LIKE ?
               OR nama_pelanggaran LIKE ?
               OR nama_guru LIKE ?
            ORDER BY tanggal DESC, id DESC";

    $stmt = mysqli_prepare($koneksi, $sql);
    $kata = "%".$cari."%";

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
        "SELECT id, nama_siswa, nama_kelas,
                nama_pelanggaran, nama_guru,
                tanggal, keterangan, poin, tindakan, status
         FROM t_pelanggaran_siswa
         ORDER BY tanggal DESC, id DESC"
    );
}

if (!$data) {
    exit("Gagal mengambil data riwayat.");
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Riwayat Pelanggaran</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">
</head>

<body>

<div class="container-fluid">
    <div class="row">

        <aside class="col-md-3 col-lg-2 bg-dark min-vh-100 p-3">

            <h4 class="text-white mb-4">Sistem Pelanggaran</h4>

            <ul class="nav nav-pills flex-column">

                <li class="nav-item mb-2">
                    <a href="dashboard.php" class="nav-link text-white">
                        Dashboard
                    </a>
                </li>

                <?php if ($role === 'admin'): ?>

                    <li class="nav-item mb-2">
                        <a href="siswa.php" class="nav-link text-white">Data Siswa</a>
                    </li>

                    <li class="nav-item mb-2">
                        <a href="guru.php" class="nav-link text-white">Data Guru</a>
                    </li>

                    <li class="nav-item mb-2">
                        <a href="kelas.php" class="nav-link text-white">Kelas</a>
                    </li>

                    <li class="nav-item mb-2">
                        <a href="tahun_ajaran.php" class="nav-link text-white">Tahun Ajaran</a>
                    </li>

                    <li class="nav-item mb-2">
                        <a href="penempatan_siswa.php" class="nav-link text-white">Penempatan Siswa</a>
                    </li>

                    <li class="nav-item mb-2">
                        <a href="wali_kelas.php" class="nav-link text-white">Wali Kelas</a>
                    </li>

                    <li class="nav-item mb-2">
                        <a href="kategori_pelanggaran.php" class="nav-link text-white">Kategori Pelanggaran</a>
                    </li>

                    <li class="nav-item mb-2">
                        <a href="jenis_pelanggaran.php" class="nav-link text-white">Jenis Pelanggaran</a>
                    </li>

                    <li class="nav-item mb-2">
                        <a href="laporan.php" class="nav-link text-white">Laporan</a>
                    </li>

                <?php endif; ?>

                <li class="nav-item mb-2">
                    <a href="catatan_pelanggaran.php" class="nav-link text-white">
                        Catatan Pelanggaran
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a href="tindakan.php" class="nav-link text-white">
                        Tindakan
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a href="riwayat.php" class="nav-link active">
                        Riwayat
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a href="rekap_poin.php" class="nav-link text-white">
                        Rekap Poin
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a href="about_me.php" class="nav-link text-white">
                        About Me
                    </a>
                </li>

                <hr class="text-secondary">

                <li class="nav-item">
                    <a href="logout.php" class="nav-link text-danger">Logout</a>
                </li>

            </ul>
        </aside>

        <main class="col-md-9 col-lg-10 p-4">

            <h2>Riwayat Pelanggaran Siswa</h2>

            <p>
                Selamat datang,
                <strong><?= htmlspecialchars($_SESSION['name'] ?? 'Pengguna'); ?></strong>
            </p>

            <div class="card shadow-sm">
                <div class="card-body">

                    <form method="GET" class="row g-2 mb-4">

                        <div class="col-md-6">
                            <input
                                type="text"
                                name="cari"
                                class="form-control"
                                placeholder="Cari nama siswa, kelas, pelanggaran, atau guru"
                                value="<?= htmlspecialchars($cari); ?>">
                        </div>

                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary">
                                Cari
                            </button>

                            <a href="riwayat.php" class="btn btn-secondary">
                                Reset
                            </a>
                        </div>

                    </form>

                    <div class="table-responsive">

                        <table class="table table-bordered table-striped table-hover">

                            <thead class="table-dark">
                                <tr>
                                    <th>No.</th>
                                    <th>Nama Siswa</th>
                                    <th>Kelas</th>
                                    <th>Pelanggaran</th>
                                    <th>Guru</th>
                                    <th>Tanggal</th>
                                    <th>Keterangan</th>
                                    <th>Poin</th>
                                    <th>Tindakan</th>
                                    <th>Status</th>
                                </tr>
                            </thead>

                            <tbody>

                            <?php if (mysqli_num_rows($data) > 0): ?>

                                <?php $no = 1; ?>

                                <?php while ($row = mysqli_fetch_assoc($data)): ?>

                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td><?= htmlspecialchars($row['nama_siswa']); ?></td>
                                        <td><?= htmlspecialchars($row['nama_kelas']); ?></td>
                                        <td><?= htmlspecialchars($row['nama_pelanggaran']); ?></td>
                                        <td><?= htmlspecialchars($row['nama_guru']); ?></td>
                                        <td><?= htmlspecialchars($row['tanggal']); ?></td>
                                        <td><?= htmlspecialchars($row['keterangan']); ?></td>
                                        <td>
                                            <span class="badge text-bg-danger">
                                                <?= (int)$row['poin']; ?> poin
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($row['tindakan']); ?></td>
                                        <td>
                                            <?php if ($row['status'] === 'Selesai'): ?>
                                                <span class="badge text-bg-success">Selesai</span>
                                            <?php elseif ($row['status'] === 'Diproses'): ?>
                                                <span class="badge text-bg-primary">Diproses</span>
                                            <?php else: ?>
                                                <span class="badge text-bg-warning">
                                                    <?= htmlspecialchars($row['status']); ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>

                                <?php endwhile; ?>

                            <?php else: ?>

                                <tr>
                                    <td colspan="10" class="text-center">
                                        Belum ada riwayat pelanggaran.
                                    </td>
                                </tr>

                            <?php endif; ?>

                            </tbody>
                        </table>

                    </div>

                    <p class="text-secondary mb-0">
                        Total riwayat: <strong><?= mysqli_num_rows($data); ?></strong> data
                    </p>

                </div>
            </div>

        </main>

    </div>
</div>

</body>
</html>
