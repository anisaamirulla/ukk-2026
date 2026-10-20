
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
    header("Location: siswa.php");
    exit;
}

$id = (int) $_GET['id'];
$pesan = "";

// Ambil data siswa
$query = mysqli_query($koneksi, "SELECT * FROM t_siswa WHERE id = $id");

if (!$query || mysqli_num_rows($query) == 0) {
    exit("Data siswa tidak ditemukan.");
}

$siswa = mysqli_fetch_assoc($query);

// Proses update data
if (isset($_POST['update'])) {

    $nisn = mysqli_real_escape_string($koneksi, trim($_POST['nisn']));
    $nip = mysqli_real_escape_string($koneksi, trim($_POST['nip']));
    $nama = mysqli_real_escape_string($koneksi, trim($_POST['nama']));
    $jenis_kelamin = mysqli_real_escape_string($koneksi, $_POST['jenis_kelamin']);
    $tanggal_lahir = mysqli_real_escape_string($koneksi, $_POST['tanggal_lahir']);
    $status_aktif = (int) $_POST['status_aktif'];

    if (
        $nisn == "" ||
        $nip == "" ||
        $nama == "" ||
        $jenis_kelamin == "" ||
        $tanggal_lahir == ""
    ) {
        $pesan = "Semua data wajib diisi.";
    } elseif (!preg_match('/^[0-9]{10}$/', $nisn)) {
        $pesan = "NISN harus terdiri dari tepat 10 digit angka.";
    } elseif (!in_array($jenis_kelamin, ['L', 'P'], true)) {
        $pesan = "Jenis kelamin tidak valid.";
    } elseif (!in_array($status_aktif, [0, 1], true)) {
        $pesan = "Status siswa tidak valid.";
    } else {

        $update = mysqli_query($koneksi, "
            UPDATE t_siswa SET
                nisn = '$nisn',
                nip = '$nip',
                nama = '$nama',
                jenis_kelamin = '$jenis_kelamin',
                tanggal_lahir = '$tanggal_lahir',
                status_aktif = $status_aktif
            WHERE id = $id
        ");

        if ($update) {
            header("Location: siswa.php?pesan=edit_berhasil");
            exit;
        } else {
            $pesan = "Gagal mengubah data: " . mysqli_error($koneksi);
        }
    }

    // Tampilkan kembali input yang sudah diisi
    $siswa['nisn'] = $_POST['nisn'];
    $siswa['nip'] = $_POST['nip'];
    $siswa['nama'] = $_POST['nama'];
    $siswa['jenis_kelamin'] = $_POST['jenis_kelamin'];
    $siswa['tanggal_lahir'] = $_POST['tanggal_lahir'];
    $siswa['status_aktif'] = $status_aktif;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Data Siswa</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>

<body class="bg-light">

<div class="container py-5">

    <div class="card shadow-sm border-0 mx-auto" style="max-width: 700px;">

        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Edit Data Siswa</h4>
        </div>

        <div class="card-body p-4">

            <?php if ($pesan != "") { ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($pesan); ?>
                </div>
            <?php } ?>

            <form method="POST">

                <div class="mb-3">
                    <label class="form-label">NISN</label>
                    <input type="text"
                           name="nisn"
                           class="form-control"
                           value="<?= htmlspecialchars($siswa['nisn']); ?>"
                           maxlength="10"
                           minlength="10"
                           pattern="[0-9]{10}"
                           inputmode="numeric"
                           oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);"
                           title="NISN harus tepat 10 digit angka"
                           required>
                </div>

                <div class="mb-3">
                    <label class="form-label">NIP</label>
                    <input type="text" name="nip" class="form-control"
                           value="<?= htmlspecialchars($siswa['nip']); ?>"
                           required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Nama Siswa</label>
                    <input type="text" name="nama" class="form-control"
                           value="<?= htmlspecialchars($siswa['nama']); ?>"
                           required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Jenis Kelamin</label>
                    <select name="jenis_kelamin" class="form-select" required>
                        <option value="">-- Pilih Jenis Kelamin --</option>

                        <option value="L"
                            <?= $siswa['jenis_kelamin'] == 'L' ? 'selected' : ''; ?>>
                            L
                        </option>

                        <option value="P"
                            <?= $siswa['jenis_kelamin'] == 'P' ? 'selected' : ''; ?>>
                            P
                        </option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir"
                           class="form-control"
                           value="<?= htmlspecialchars($siswa['tanggal_lahir']); ?>"
                           required>
                </div>

                <div class="mb-4">
                    <label class="form-label">Status Siswa</label>
                    <select name="status_aktif" class="form-select" required>
                        <option value="1"
                            <?= (int)$siswa['status_aktif'] === 1 ? 'selected' : ''; ?>>
                            Aktif
                        </option>

                        <option value="0"
                            <?= (int)$siswa['status_aktif'] === 0 ? 'selected' : ''; ?>>
                            Tidak Aktif
                        </option>
                    </select>
                </div>

                <button type="submit" name="update" class="btn btn-primary">
                    Simpan Perubahan
                </button>

                <a href="siswa.php" class="btn btn-secondary">
                    Kembali
                </a>

            </form>

        </div>
    </div>
</div>

</body>
</html>
