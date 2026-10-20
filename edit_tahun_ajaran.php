
<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    exit("Anda tidak memiliki akses");
}

require_once __DIR__ . "/config/koneksi.php";

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: tahun_ajaran.php");
    exit;
}

$id = (int) $_GET['id'];
$pesan = "";

$query = mysqli_query($koneksi, "SELECT * FROM t_tahun_ajaran WHERE id = $id");

if (!$query || mysqli_num_rows($query) == 0) {
    header("Location: tahun_ajaran.php");
    exit;
}

$tahun = mysqli_fetch_assoc($query);

if (isset($_POST['simpan'])) {
    $nama = trim($_POST['nama']);
    $tanggal_mulai = $_POST['tanggal_mulai'];
    $tanggal_selesai = $_POST['tanggal_selesai'];
    $status_aktif = (int) $_POST['status_aktif'];

    if (
        $nama == "" ||
        $tanggal_mulai == "" ||
        $tanggal_selesai == ""
    ) {
        $pesan = "Semua data harus diisi.";
    } elseif ($tanggal_selesai < $tanggal_mulai) {
        $pesan = "Tanggal selesai tidak boleh sebelum tanggal mulai.";
    } else {
        $nama = mysqli_real_escape_string($koneksi, $nama);
        $tanggal_mulai = mysqli_real_escape_string($koneksi, $tanggal_mulai);
        $tanggal_selesai = mysqli_real_escape_string($koneksi, $tanggal_selesai);

        $update = mysqli_query(
            $koneksi,
            "UPDATE t_tahun_ajaran SET
                nama = '$nama',
                tanggal_mulai = '$tanggal_mulai',
                tanggal_selesai = '$tanggal_selesai',
                status_aktif = $status_aktif
             WHERE id = $id"
        );

        if ($update) {
            header("Location: tahun_ajaran.php?pesan=edit_berhasil");
            exit;
        } else {
            $pesan = "Gagal mengubah data: " . mysqli_error($koneksi);
        }
    }

    $tahun['nama'] = $_POST['nama'];
    $tahun['tanggal_mulai'] = $_POST['tanggal_mulai'];
    $tahun['tanggal_selesai'] = $_POST['tanggal_selesai'];
    $tahun['status_aktif'] = $_POST['status_aktif'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Tahun Ajaran</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-dark text-white p-3">
                    <h4 class="mb-0">Edit Tahun Ajaran</h4>
                </div>

                <div class="card-body p-4">

                    <?php if ($pesan != "") { ?>
                        <div class="alert alert-danger">
                            <?= htmlspecialchars($pesan); ?>
                        </div>
                    <?php } ?>

                    <form method="POST">

                        <div class="mb-3">
                            <label class="form-label">Nama Tahun Ajaran</label>
                            <input
                                type="text"
                                name="nama"
                                class="form-control"
                                value="<?= htmlspecialchars($tahun['nama']); ?>"
                                required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tanggal Mulai</label>
                            <input
                                type="date"
                                name="tanggal_mulai"
                                class="form-control"
                                value="<?= htmlspecialchars($tahun['tanggal_mulai']); ?>"
                                required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tanggal Selesai</label>
                            <input
                                type="date"
                                name="tanggal_selesai"
                                class="form-control"
                                value="<?= htmlspecialchars($tahun['tanggal_selesai']); ?>"
                                required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Status</label>
                            <select name="status_aktif" class="form-select" required>
                                <option value="1" <?= (int)$tahun['status_aktif'] === 1 ? 'selected' : ''; ?>>
                                    Aktif
                                </option>
                                <option value="0" <?= (int)$tahun['status_aktif'] === 0 ? 'selected' : ''; ?>>
                                    Tidak Aktif
                                </option>
                            </select>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" name="simpan" class="btn btn-primary">
                                Simpan Perubahan
                            </button>

                            <a href="tahun_ajaran.php" class="btn btn-secondary">
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
