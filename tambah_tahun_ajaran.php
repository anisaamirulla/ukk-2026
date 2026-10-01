<?php
require_once __DIR__ . '/config/koneksi.php';

$edit = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;

$row = [
    'nama' => '',
    'tanggal_mulai' => '',
    'tanggal_selesai' => '',
    'status_aktif' => 1
];

if ($edit > 0) {

    $query = mysqli_query(
        $koneksi,
        "SELECT * FROM t_tahun_ajaran WHERE id = $edit"
    );

    if ($query && mysqli_num_rows($query) > 0) {
        $row = mysqli_fetch_assoc($query);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id = (int) ($_POST['id'] ?? 0);

    $nama = mysqli_real_escape_string(
        $koneksi,
        $_POST['nama'] ?? ''
    );

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

        mysqli_query(
            $koneksi,
            "UPDATE t_tahun_ajaran SET
                nama = '$nama',
                tanggal_mulai = '$tanggal_mulai',
                tanggal_selesai = '$tanggal_selesai',
                status_aktif = $status_aktif,
                updated_at = CURRENT_TIMESTAMP
             WHERE id = $id"
        );

    } else {

        mysqli_query(
            $koneksi,
            "INSERT INTO t_tahun_ajaran
                (nama, tanggal_mulai, tanggal_selesai, status_aktif)
             VALUES
                ('$nama', '$tanggal_mulai', '$tanggal_selesai', $status_aktif)"
        );
    }

    header("Location: tahun_ajaran.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        <?= $edit > 0 ? 'Edit Tahun Ajaran' : 'Tambah Tahun Ajaran' ?>
    </title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container py-4">

    <div class="mb-4">
        <h1 class="h3 mb-1">
            <?= $edit > 0 ? 'Edit Tahun Ajaran' : 'Tambah Data Tahun Ajaran' ?>
        </h1>

        <p class="text-secondary mb-0">
            <?= $edit > 0
                ? 'Perbarui informasi tahun ajaran.'
                : 'Masukkan informasi tahun ajaran baru.'
            ?>
        </p>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">

            <form method="post">

                <input
                    type="hidden"
                    name="id"
                    value="<?= $edit ?>"
                >

                <div class="row g-3">

                    <div class="col-md-6">

                        <label class="form-label">
                            Nama Tahun Ajaran
                        </label>

                        <input
                            type="text"
                            name="nama"
                            class="form-control"
                            placeholder="Contoh: Tahun Ajaran 2026/2027"
                            value="<?= htmlspecialchars($row['nama']) ?>"
                            required
                        >

                    </div>

                    <div class="col-md-6">

                        <label class="form-label">
                            Status Aktif
                        </label>

                        <select
                            name="status_aktif"
                            class="form-select"
                            required
                        >

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

                        <label class="form-label">
                            Tanggal Mulai
                        </label>

                        <input
                            type="date"
                            name="tanggal_mulai"
                            class="form-control"
                            value="<?= htmlspecialchars($row['tanggal_mulai']) ?>"
                            required
                        >

                    </div>

                    <div class="col-md-6">

                        <label class="form-label">
                            Tanggal Selesai
                        </label>

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

                    <a
                        href="dashboard.php"
                        class="btn btn-secondary"
                    >
                        Kembali ke Dashboard
                    </a>

                    <a
                        href="tahun_ajaran.php"
                        class="btn btn-outline-secondary"
                    >
                        Batal
                    </a>

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        <?= $edit > 0 ? 'Simpan Perubahan' : 'Tambah Data' ?>
                    </button>

                </div>

            </form>

        </div>
    </div>

</div>

</body>
</html>