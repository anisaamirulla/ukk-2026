<?php
require_once __DIR__ . '/config/koneksi.php';

if (isset($_GET['hapus'])) {
    $id = (int) $_GET['hapus'];

    mysqli_query($koneksi, "DELETE FROM t_tahun_ajaran WHERE id = $id");

    header("Location: tahun_ajaran.php");
    exit;
}

$data = mysqli_query(
    $koneksi,
    "SELECT * FROM t_tahun_ajaran ORDER BY id DESC"
);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Tahun Ajaran</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Data Tahun Ajaran</h1>
            <p class="text-secondary mb-0">
                Kelola data tahun ajaran sekolah
            </p>
        </div>

        <div class="d-flex gap-2">
            <a href="dashboard.php" class="btn btn-secondary">
                Kembali ke Dashboard
            </a>

            <a href="tambah_tahun_ajaran.php" class="btn btn-primary">
                + Tambah Tahun Ajaran
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
                                <?= htmlspecialchars($row['nama']) ?>
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
                                    href="tambah_tahun_ajaran.php?edit=<?= $row['id'] ?>"
                                    class="btn btn-warning btn-sm"
                                >
                                    Edit
                                </a>

                                <a
                                    href="tahun_ajaran.php?hapus=<?= $row['id'] ?>"
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
                            <td colspan="6" class="text-center text-secondary">
                                Belum ada data tahun ajaran.
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