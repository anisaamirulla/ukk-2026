
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

$keyword = trim($_GET['keyword'] ?? '');

$sql = "SELECT 
            nama_siswa,
            nama_kelas,
            COUNT(*) AS jumlah_pelanggaran,
            SUM(poin) AS total_poin
        FROM t_pelanggaran_siswa";

if ($keyword != '') {
    $sql .= " WHERE nama_siswa LIKE ?
              OR nama_kelas LIKE ?";
}

$sql .= " GROUP BY nama_siswa, nama_kelas
          ORDER BY total_poin DESC";

if ($keyword != '') {
    $stmt = mysqli_prepare($koneksi, $sql);
    $cari = "%".$keyword."%";
    mysqli_stmt_bind_param($stmt, "ss", $cari, $cari);
    mysqli_stmt_execute($stmt);
    $data = mysqli_stmt_get_result($stmt);
} else {
    $data = mysqli_query($koneksi, $sql);
}

if (!$data) {
    die("Query gagal: " . mysqli_error($koneksi));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rekap Poin Pelanggaran</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container-fluid">
    <div class="row">

        <div class="col-md-3 col-lg-2 bg-dark text-white min-vh-100 p-3">
            <h5 class="mb-4">Sistem Pelanggaran</h5>

            <div class="d-grid gap-2">
                <a href="dashboard.php" class="btn btn-outline-light text-start">
                    Dashboard
                </a>

                <?php if ($role == 'admin') { ?>
                    <a href="catatan_pelanggaran.php" class="btn btn-outline-light text-start">
                        Catatan Pelanggaran
                    </a>
                    <a href="tindakan.php" class="btn btn-outline-light text-start">
                        Tindakan
                    </a>
                    <a href="riwayat.php" class="btn btn-outline-light text-start">
                        Riwayat
                    </a>
                <?php } else { ?>
                    <a href="catatan_pelanggaran.php" class="btn btn-outline-light text-start">
                        Catatan Pelanggaran
                    </a>
                    <a href="tindakan.php" class="btn btn-outline-light text-start">
                        Tindakan
                    </a>
                    <a href="riwayat.php" class="btn btn-outline-light text-start">
                        Riwayat
                    </a>
                <?php } ?>

                <a href="rekap_poin.php" class="btn btn-primary text-start">
                    Rekap Poin
                </a>

                <a href="abautme.php" class="btn btn-outline-light text-start">
                    About Me
                </a>

                <a href="logout.php" class="btn btn-danger text-start mt-3">
                    Logout
                </a>
            </div>
        </div>

        <div class="col-md-9 col-lg-10 p-4">
            <h3>Rekap Poin Pelanggaran Siswa</h3>
            <p class="text-secondary">
                Daftar jumlah pelanggaran dan total poin setiap siswa.
            </p>

            <div class="card shadow-sm">
                <div class="card-body">

                    <form method="GET" class="row g-2 mb-3">
                        <div class="col-md-5">
                            <input
                                type="text"
                                name="keyword"
                                class="form-control"
                                placeholder="Cari nama siswa atau kelas..."
                                value="<?= htmlspecialchars($keyword) ?>">
                        </div>

                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary">
                                Cari
                            </button>
                            <a href="rekap_poin.php" class="btn btn-secondary">
                                Reset
                            </a>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>No</th>
                                    <th>Nama Siswa</th>
                                    <th>Kelas</th>
                                    <th>Jumlah Pelanggaran</th>
                                    <th>Total Poin</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php
                                $no = 1;

                                if (mysqli_num_rows($data) > 0) {
                                    while ($row = mysqli_fetch_assoc($data)) {
                                ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><?= htmlspecialchars($row['nama_siswa'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($row['nama_kelas'] ?? '') ?></td>
                                        <td><?= (int)$row['jumlah_pelanggaran'] ?></td>
                                        <td>
                                            <span class="badge bg-danger">
                                                <?= (int)$row['total_poin'] ?> poin
                                            </span>
                                        </td>
                                    </tr>
                                <?php
                                    }
                                } else {
                                ?>
                                    <tr>
                                        <td colspan="5" class="text-center">
                                            Data rekap poin belum tersedia.
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
