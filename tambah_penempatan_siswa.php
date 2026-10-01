<?php
require_once __DIR__ . '/config/koneksi.php';

$edit = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;

$row = [
    'siswa_id' => '',
    'tahun_ajaran_id' => '',
    'kelas_id' => '',
    'tanggal_mulai' => '',
    'tanggal_selesai' => '',
    'status_aktif' => 1
];

if ($edit > 0) {

    $query = mysqli_query(
        $koneksi,
        "SELECT * FROM t_kelas_siswa WHERE id = $edit"
    );

    if ($query && mysqli_num_rows($query) > 0) {
        $row = mysqli_fetch_assoc($query);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id = (int) ($_POST['id'] ?? 0);
    $siswa_id = (int) ($_POST['siswa_id'] ?? 0);
    $tahun_ajaran_id = (int) ($_POST['tahun_ajaran_id'] ?? 0);
    $kelas_id = (int) ($_POST['kelas_id'] ?? 0);

    $tanggal_mulai = mysqli_real_escape_string(
        $koneksi,
        $_POST['tanggal_mulai'] ?? ''
    );

    $tanggal_selesai = mysqli_real_escape_string(
        $koneksi,
        $_POST['tanggal_selesai'] ?? ''
    );

    $status_aktif = (int) ($_POST['status_aktif'] ?? 0);

    if ($id > 0) {

        mysqli_query($koneksi, "
            UPDATE t_kelas_siswa SET
                siswa_id = $siswa_id,
                tahun_ajaran_id = $tahun_ajaran_id,
                kelas_id = $kelas_id,
                tanggal_mulai = '$tanggal_mulai',
                tanggal_selesai = '$tanggal_selesai',
                status_aktif = $status_aktif,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = $id
        ");

    } else {

        mysqli_query($koneksi, "
            INSERT INTO t_kelas_siswa
            (
                siswa_id,
                tahun_ajaran_id,
                kelas_id,
                tanggal_mulai,
                tanggal_selesai,
                status_aktif
            )
            VALUES
            (
                $siswa_id,
                $tahun_ajaran_id,
                $kelas_id,
                '$tanggal_mulai',
                '$tanggal_selesai',
                $status_aktif
            )
        ");
    }

    header("Location: penempatan_siswa.php");
    exit;
}

$siswa = mysqli_query(
    $koneksi,
    "SELECT id, nisn, nama FROM t_siswa ORDER BY nama ASC"
);

$kelas = mysqli_query(
    $koneksi,
    "SELECT id, nama FROM t_kelas ORDER BY nama ASC"
);

$tahun = mysqli_query(
    $koneksi,
    "SELECT id, nama FROM t_tahun_ajaran ORDER BY id DESC"
);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Tambah Penempatan Siswa</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container py-4">

    <div class="mb-4">
        <h1 class="h3 mb-1">
            <?= $edit > 0 ? 'Edit Penempatan Siswa' : 'Tambah Penempatan Siswa' ?>
        </h1>

        <p class="text-secondary mb-0">
            Masukkan informasi penempatan siswa.
        </p>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">

            <form method="post">

                <input type="hidden" name="id" value="<?= $edit ?>">

                <div class="row g-3">

                    <div class="col-md-6">
                        <label class="form-label">Siswa</label>

                        <select name="siswa_id" class="form-select" required>
                            <option value="">-- Pilih Siswa --</option>

                            <?php while ($s = mysqli_fetch_assoc($siswa)): ?>

                                <option
                                    value="<?= $s['id'] ?>"
                                    <?= $row['siswa_id'] == $s['id'] ? 'selected' : '' ?>
                                >
                                    <?= htmlspecialchars($s['nisn']) ?>
                                    - <?= htmlspecialchars($s['nama']) ?>
                                </option>

                            <?php endwhile; ?>

                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Tahun Ajaran</label>

                        <select name="tahun_ajaran_id" class="form-select" required>
                            <option value="">-- Pilih Tahun Ajaran --</option>

                            <?php while ($t = mysqli_fetch_assoc($tahun)): ?>

                                <option
                                    value="<?= $t['id'] ?>"
                                    <?= $row['tahun_ajaran_id'] == $t['id'] ? 'selected' : '' ?>
                                >
                                    <?= htmlspecialchars($t['nama']) ?>
                                </option>

                            <?php endwhile; ?>

                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Kelas</label>

                        <select name="kelas_id" class="form-select" required>
                            <option value="">-- Pilih Kelas --</option>

                            <?php while ($k = mysqli_fetch_assoc($kelas)): ?>

                                <option
                                    value="<?= $k['id'] ?>"
                                    <?= $row['kelas_id'] == $k['id'] ? 'selected' : '' ?>
                                >
                                    <?= htmlspecialchars($k['nama']) ?>
                                </option>

                            <?php endwhile; ?>

                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Status Aktif</label>

                        <select name="status_aktif" class="form-select" required>

                            <option value="1"
                                <?= $row['status_aktif'] == 1 ? 'selected' : '' ?>>
                                Aktif
                            </option>

                            <option value="0"
                                <?= $row['status_aktif'] == 0 ? 'selected' : '' ?>>
                                Tidak Aktif
                            </option>

                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Tanggal Mulai</label>

                        <input
                            type="date"
                            name="tanggal_mulai"
                            class="form-control"
                            value="<?= htmlspecialchars($row['tanggal_mulai']) ?>"
                            required
                        >
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Tanggal Selesai</label>

                        <input
                            type="date"
                            name="tanggal_selesai"
                            class="form-control"
                            value="<?= htmlspecialchars($row['tanggal_selesai']) ?>"
                            required
                        >
                    </div>

                </div>

                <div class="d-flex gap-2 mt-4">

                    <a href="dashboard.php" class="btn btn-secondary">
                        Kembali ke Dashboard
                    </a>

                    <a href="penempatan_siswa.php" class="btn btn-outline-secondary">
                        Batal
                    </a>

                    <button type="submit" class="btn btn-primary">
                        <?= $edit > 0 ? 'Simpan Perubahan' : 'Tambah Data' ?>
                    </button>

                </div>

            </form>

        </div>
    </div>

</div>

</body>
</html>