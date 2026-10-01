<?php
require_once __DIR__ . '/config/koneksi.php';

$edit = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;

$row = [
    'nama' => '',
    'deksripsi' => '',
    'status_aktif' => 1
];

if ($edit > 0) {

    $query = mysqli_query(
        $koneksi,
        "SELECT * FROM t_pelanggaran_kategori WHERE id = $edit"
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

    $deksripsi = mysqli_real_escape_string(
        $koneksi,
        $_POST['deksripsi'] ?? ''
    );

    $status_aktif = (int) ($_POST['status_aktif'] ?? 0);

    if ($id > 0) {

        mysqli_query($koneksi, "
            UPDATE t_pelanggaran_kategori SET
                nama = '$nama',
                deksripsi = '$deksripsi',
                status_aktif = $status_aktif,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = $id
        ");

    } else {

        mysqli_query($koneksi, "
            INSERT INTO t_pelanggaran_kategori
            (
                nama,
                deksripsi,
                status_aktif
            )
            VALUES
            (
                '$nama',
                '$deksripsi',
                $status_aktif
            )
        ");
    }

    header("Location: kategori_pelanggaran.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Tambah Kategori Pelanggaran</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

</head>

<body class="bg-light">

<div class="container py-4">

    <div class="mb-4">

        <h1 class="h3 mb-1">

            <?= $edit > 0
                ? 'Edit Kategori Pelanggaran'
                : 'Tambah Data Kategori Pelanggaran'
            ?>

        </h1>

        <p class="text-secondary mb-0">
            Masukkan informasi kategori pelanggaran.
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
                            Nama Kategori
                        </label>

                        <input
                            type="text"
                            name="nama"
                            class="form-control"
                            placeholder="Contoh: Terlambat"
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

                    <div class="col-12">

                        <label class="form-label">
                            Deskripsi
                        </label>

                        <textarea
                            name="deksripsi"
                            class="form-control"
                            rows="4"
                            placeholder="Masukkan deskripsi kategori"
                            required
                        ><?= htmlspecialchars($row['deksripsi']) ?></textarea>

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
                        href="kategori_pelanggaran.php"
                        class="btn btn-outline-secondary"
                    >
                        Batal
                    </a>

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        <?= $edit > 0
                            ? 'Simpan Perubahan'
                            : 'Tambah Data'
                        ?>
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

</body>

</html>