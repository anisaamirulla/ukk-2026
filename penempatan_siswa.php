<?php
require_once __DIR__ . '/config/koneksi.php';

if (isset($_GET['hapus'])) {
    $id = (int) $_GET['hapus'];

    mysqli_query($koneksi, "DELETE FROM t_kelas_siswa WHERE id = $id");

    header("Location: penempatan_siswa.php");
    exit;
}

$data = mysqli_query($koneksi, "
    SELECT 
        ks.id,
        s.nisn,
        s.nama AS nama_siswa,
        k.nama AS nama_kelas,
        ta.nama AS tahun_ajaran,
        ks.tanggal_mulai,
        ks.tanggal_selesai,
        ks.status_aktif
    FROM t_kelas_siswa ks
    JOIN t_siswa s ON ks.siswa_id = s.id
    JOIN t_kelas k ON ks.kelas_id = k.id
    JOIN t_tahun_ajaran ta ON ks.tahun_ajaran_id = ta.id
    ORDER BY ks.id DESC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penempatan Siswa</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Penempatan Siswa</h1>
            <p class="text-secondary mb-0">
                Kelola penempatan siswa ke kelas
            </p>
        </div>

        <div class="d-flex gap-2">
            <a href="dashboard.php" class="btn btn-secondary">
                Kembali ke Dashboard
            </a>

            <a href="tambah_penempatan_siswa.php" class="btn btn-primary">
                + Tambah Penempatan
            </a>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">

            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle mb-0">

                    <thead class="table-primary">
                        <tr>
                            <th>No</th>
                            <th>NISN</th>
                            <th>Nama Siswa</th>
                            <th>Kelas</th>
                            <th>Tahun Ajaran</th>
                            <th>Tanggal Mulai</th>
                            <th>Tanggal Selesai</th>
                            <th>Status</th>
                            <th width="180">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php
                    $no = 1;

                    if (mysqli_num_rows($data) > 0):
                        while ($row = mysqli_fetch_assoc($data)):
                    ?>

                        <tr>
                            <td><?= $no++ ?></td>

                            <td><?= htmlspecialchars($row['nisn']) ?></td>

                            <td><?= htmlspecialchars($row['nama_siswa']) ?></td>

                            <td><?= htmlspecialchars($row['nama_kelas']) ?></td>

                            <td><?= htmlspecialchars($row['tahun_ajaran']) ?></td>

                            <td><?= htmlspecialchars($row['tanggal_mulai']) ?></td>

                            <td><?= htmlspecialchars($row['tanggal_selesai']) ?></td>

                            <td>
                                <?php if ($row['status_aktif'] == 1): ?>
                                    <span class="badge bg-success">Aktif</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Tidak Aktif</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <a
                                    href="tambah_penempatan_siswa.php?edit=<?= $row['id'] ?>"
                                    class="btn btn-warning btn-sm"
                                >
                                    Edit
                                </a>

                                <a
                                    href="penempatan_siswa.php?hapus=<?= $row['id'] ?>"
                                    class="btn btn-danger btn-sm"
                                    onclick="return confirm('Yakin ingin menghapus data ini?')"
                                >
                                    Hapus
                                </a>
                            </td>
                        </tr>

                    <?php
                        endwhile;
                    else:
                    ?>

                        <tr>
                            <td colspan="9" class="text-center text-secondary">
                                Belum ada data penempatan siswa.
                            </td>
                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>
            </div>

        </div>
    </div>

</div>

</body>
</html>