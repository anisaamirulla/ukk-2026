<?php
require_once __DIR__ . '/config/koneksi.php';

if (isset($_GET['hapus'])) {
    $id = (int) $_GET['hapus'];

    mysqli_query($koneksi, "DELETE FROM t_wali_kelas WHERE id = $id");

    header("Location: wali_kelas.php");
    exit;
}

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
    JOIN t_tahun_ajaran ta ON wk.tahun_ajaran_id = ta.id
    JOIN t_kelas k ON wk.kelas_id = k.id
    JOIN t_guru g ON wk.guru_id = g.id
    ORDER BY wk.id DESC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Data Wali Kelas</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h1 class="h3 mb-1">Data Wali Kelas</h1>

            <p class="text-secondary mb-0">
                Kelola data wali kelas sekolah
            </p>
        </div>

        <div class="d-flex gap-2">

            <a href="dashboard.php" class="btn btn-secondary">
                Kembali ke Dashboard
            </a>

            <a href="tambah_wali_kelas.php" class="btn btn-primary">
                + Tambah Wali Kelas
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
                            <th>Tahun Ajaran</th>
                            <th>Kelas</th>
                            <th>NIP</th>
                            <th>Nama Guru</th>
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

                            <td>
                                <?= htmlspecialchars($row['tahun_ajaran']) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($row['nama_kelas']) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($row['nip']) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($row['nama_guru']) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($row['tanggal_mulai']) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($row['tanggal_selesai']) ?>
                            </td>

                            <td>
                                <?php if ($row['status_aktif'] == 1): ?>

                                    <span class="badge bg-success">
                                        Aktif
                                    </span>

                                <?php else: ?>

                                    <span class="badge bg-secondary">
                                        Tidak Aktif
                                    </span>

                                <?php endif; ?>
                            </td>

                            <td>

                                <a
                                    href="tambah_wali_kelas.php?edit=<?= $row['id'] ?>"
                                    class="btn btn-warning btn-sm"
                                >
                                    Edit
                                </a>

                                <a
                                    href="wali_kelas.php?hapus=<?= $row['id'] ?>"
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
                                Belum ada data wali kelas.
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