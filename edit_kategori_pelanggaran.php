
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

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: kategori_pelanggaran.php");
    exit;
}

$id = (int) $_GET['id'];

$query = mysqli_prepare(
    $koneksi,
    "SELECT * FROM t_pelanggaran_kategori WHERE id = ?"
);

mysqli_stmt_bind_param($query, "i", $id);
mysqli_stmt_execute($query);

$hasil = mysqli_stmt_get_result($query);
$data = mysqli_fetch_assoc($hasil);

if (!$data) {
    exit("Data kategori tidak ditemukan.");
}

$pesan = "";

if (isset($_POST['simpan'])) {
    $nama = trim($_POST['nama'] ?? '');
    $deksripsi = trim($_POST['deksripsi'] ?? '');
    $status_aktif = (int) ($_POST['status_aktif'] ?? 0);

    if ($nama == '') {
        $pesan = "Nama kategori wajib diisi.";
    } elseif (!in_array($status_aktif, [0, 1], true)) {
        $pesan = "Status kategori tidak valid.";
    } else {
        $update = mysqli_prepare(
            $koneksi,
            "UPDATE t_pelanggaran_kategori
             SET nama = ?, deksripsi = ?, status_aktif = ?
             WHERE id = ?"
        );

        mysqli_stmt_bind_param(
            $update,
            "ssii",
            $nama,
            $deksripsi,
            $status_aktif,
            $id
        );

        if (mysqli_stmt_execute($update)) {
            header("Location: kategori_pelanggaran.php?pesan=edit");
            exit;
        } else {
            $pesan = "Gagal memperbarui data kategori.";
        }
    }

    $data['nama'] = $nama;
    $data['deksripsi'] = $deksripsi;
    $data['status_aktif'] = $status_aktif;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Kategori Pelanggaran</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>

<body class="bg-light">

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">

            <div class="card border-0 shadow-sm">

                <div class="card-header bg-warning py-3">
                    <h4 class="mb-0">Edit Kategori Pelanggaran</h4>
                </div>

                <div class="card-body p-4">

                    <?php if ($pesan != ''): ?>
                        <div class="alert alert-danger">
                            <?= htmlspecialchars($pesan); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">

                        <div class="mb-3">
                            <label for="nama" class="form-label">
                                Nama Kategori
                            </label>

                            <input type="text"
                                   name="nama"
                                   id="nama"
                                   class="form-control"
                                   value="<?= htmlspecialchars($data['nama'] ?? ''); ?>"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label for="deksripsi" class="form-label">
                                Deskripsi
                            </label>

                            <textarea name="deksripsi"
                                      id="deksripsi"
                                      class="form-control"
                                      rows="4"><?= htmlspecialchars($data['deksripsi'] ?? ''); ?></textarea>
                        </div>

                        <div class="mb-4">
                            <label for="status_aktif" class="form-label">
                                Status Kategori
                            </label>

                            <select name="status_aktif"
                                    id="status_aktif"
                                    class="form-select"
                                    required>

                                <option value="1"
                                    <?= ($data['status_aktif'] ?? 0) == 1 ? 'selected' : ''; ?>>
                                    Aktif
                                </option>

                                <option value="0"
                                    <?= ($data['status_aktif'] ?? 0) == 0 ? 'selected' : ''; ?>>
                                    Tidak Aktif
                                </option>

                            </select>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit"
                                    name="simpan"
                                    class="btn btn-primary">
                                Simpan Perubahan
                            </button>

                            <a href="kategori_pelanggaran.php"
                               class="btn btn-secondary">
                                Kembali
                            </a>
                        </div>

                    </form>

                </div>
            </div>

        </div>
    </div>
</div>

</body>
</html>
