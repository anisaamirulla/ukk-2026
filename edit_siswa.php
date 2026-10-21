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

$stmt = mysqli_prepare($koneksi, "SELECT * FROM t_siswa WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$siswa = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$siswa) {
    exit("Data siswa tidak ditemukan.");
}

if (isset($_POST['update'])) {
    $nisn = trim($_POST['nisn'] ?? '');
    $nip = trim($_POST['nip'] ?? '');
    $nama = trim($_POST['nama'] ?? '');
    $jenis_kelamin = $_POST['jenis_kelamin'] ?? '';
    $tanggal_lahir = $_POST['tanggal_lahir'] ?? '';
    $status_aktif = (int) ($_POST['status_aktif'] ?? -1);

    if ($nisn === '' || $nip === '' || $nama === '' ||
        $jenis_kelamin === '' || $tanggal_lahir === '') {
        $pesan = "Semua data wajib diisi.";
    } elseif (!preg_match('/^[0-9]{10}$/', $nisn)) {
        $pesan = "NISN harus tepat 10 digit angka.";
    } elseif (!in_array($jenis_kelamin, ['L', 'P'], true)) {
        $pesan = "Jenis kelamin tidak valid.";
    } elseif (!in_array($status_aktif, [0, 1], true)) {
        $pesan = "Status siswa tidak valid.";
    } else {
        $stmt = mysqli_prepare($koneksi, "
            UPDATE t_siswa
            SET nisn=?, nip=?, nama=?, jenis_kelamin=?,
                tanggal_lahir=?, status_aktif=?
            WHERE id=?
        ");

        mysqli_stmt_bind_param(
            $stmt, "sssssii",
            $nisn, $nip, $nama, $jenis_kelamin,
            $tanggal_lahir, $status_aktif, $id
        );

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            header("Location: siswa.php?pesan=edit_berhasil");
            exit;
        }

        $pesan = "Gagal menyimpan perubahan.";
        mysqli_stmt_close($stmt);
    }

    $siswa['nisn'] = $nisn;
    $siswa['nip'] = $nip;
    $siswa['nama'] = $nama;
    $siswa['jenis_kelamin'] = $jenis_kelamin;
    $siswa['tanggal_lahir'] = $tanggal_lahir;
    $siswa['status_aktif'] = $status_aktif;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Data Siswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f4f6f9;
        }

        .container {
            max-width: 1000px;
        }

        .card {
            border: 0;
        }

        .card-header {
            background: #0d6efd;
            color: white;
        }
    </style>
</head>
<body>

<div class="container py-4">
    <h2 class="fw-bold">Edit Data Siswa</h2>
    <p class="text-muted">Perbarui informasi data siswa.</p>

    <a href="siswa.php" class="btn btn-secondary btn-sm mb-3">
        Kembali ke Data Siswa
    </a>

    <?php if ($pesan !== '') { ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($pesan, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php } ?>

    <div class="card shadow-sm">
        <div class="card-header p-3">
            <h5 class="mb-0">Form Edit Data Siswa</h5>
        </div>

        <div class="card-body p-4">
            <form method="POST">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">NISN</label>
                        <input type="text" name="nisn"
                            class="form-control"
                            value="<?= htmlspecialchars($siswa['nisn'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                            maxlength="10" pattern="[0-9]{10}" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">NIP</label>
                        <input type="text" name="nip"
                            class="form-control"
                            value="<?= htmlspecialchars($siswa['nip'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                            required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nama Siswa</label>
                        <input type="text" name="nama"
                            class="form-control"
                            value="<?= htmlspecialchars($siswa['nama'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                            required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Jenis Kelamin</label>
                        <select name="jenis_kelamin" class="form-select" required>
                            <option value="L" <?= ($siswa['jenis_kelamin'] ?? '') === 'L' ? 'selected' : ''; ?>>
                                Laki-laki
                            </option>
                            <option value="P" <?= ($siswa['jenis_kelamin'] ?? '') === 'P' ? 'selected' : ''; ?>>
                                Perempuan
                            </option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir"
                            class="form-control"
                            value="<?= htmlspecialchars($siswa['tanggal_lahir'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                            required>
                    </div>

                    <div class="col-md-6 mb-4">
                        <label class="form-label">Status Siswa</label>
                        <select name="status_aktif" class="form-select" required>
                            <option value="1" <?= (int)($siswa['status_aktif'] ?? 0) === 1 ? 'selected' : ''; ?>>
                                Aktif
                            </option>
                            <option value="0" <?= (int)($siswa['status_aktif'] ?? 0) === 0 ? 'selected' : ''; ?>>
                                Tidak Aktif
                            </option>
                        </select>
                    </div>
                </div>

                <button type="submit" name="update" class="btn btn-primary">
                    Simpan Perubahan
                </button>

                <a href="siswa.php" class="btn btn-secondary">
                    Batal
                </a>
            </form>
        </div>
    </div>
</div>

</body>
</html>
