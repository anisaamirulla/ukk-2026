
<?php
session_start();
require_once 'config/koneksi.php';

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

function aman($nilai) {
    return htmlspecialchars((string)($nilai ?? ''), ENT_QUOTES, 'UTF-8');
}

$pesan = '';
$id = (int)($_GET['id'] ?? 0);
$edit = null;

// AMBIL DATA GURU UNTUK EDIT
if ($id > 0) {
    $stmt = $koneksi->prepare(
        "SELECT * FROM t_guru WHERE id = ?"
    );

    $stmt->bind_param('i', $id);
    $stmt->execute();

    $hasil = $stmt->get_result();
    $edit = $hasil->fetch_assoc();
    $stmt->close();

    if (!$edit) {
        header('Location: guru.php');
        exit;
    }
}

// PROSES TAMBAH DAN EDIT
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $nip = trim($_POST['nip'] ?? '');
    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $status = $_POST['status_aktif'] ?? 'aktif';

    if ($nama === '') {
        $pesan = 'Nama guru wajib diisi.';
    } elseif ($email !== '' &&
              !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $pesan = 'Format email tidak valid.';
    } elseif (!in_array($status, ['aktif', 'nonaktif'], true)) {
        $pesan = 'Status tidak valid.';
    } else {
        if ($id > 0) {
            $stmt = $koneksi->prepare(
                "UPDATE t_guru
                 SET nip = ?, nama = ?, email = ?, status_aktif = ?
                 WHERE id = ?"
            );

            $stmt->bind_param(
                'ssssi',
                $nip,
                $nama,
                $email,
                $status,
                $id
            );
        } else {
            $stmt = $koneksi->prepare(
                "INSERT INTO t_guru
                    (nip, nama, email, status_aktif)
                 VALUES (?, ?, ?, ?)"
            );

            $stmt->bind_param(
                'ssss',
                $nip,
                $nama,
                $email,
                $status
            );
        }

        if ($stmt->execute()) {
            $stmt->close();
            header('Location: guru.php?pesan=sukses');
            exit;
        } else {
            $pesan = 'Gagal menyimpan data guru: ' . $stmt->error;
            $stmt->close();
        }
    }

    // PERTAHANKAN ISIAN JIKA TERJADI ERROR
    $edit = [
        'id' => $id,
        'nip' => $nip,
        'nama' => $nama,
        'email' => $email,
        'status_aktif' => $status
    ];
}

$isEdit = (int)($edit['id'] ?? 0) > 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><?= $isEdit ? 'Edit Data Guru' : 'Tambah Data Guru' ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <style>
        body {
            background: #f5f5f5;
            font-family: Arial, sans-serif;
            color: #212529;
        }

        .halaman {
            padding: 25px;
            min-height: 100vh;
        }

        .judul-halaman {
            font-size: 26px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .deskripsi {
            color: #6c757d;
            margin-bottom: 0;
        }

        .form-card {
            background: #ffffff;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,.06);
        }

        .form-label {
            font-weight: 600;
            margin-bottom: 7px;
        }

        .form-control,
        .form-select {
            background: #ffffff;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            min-height: 38px;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #b5a18f;
            box-shadow: 0 0 0 .15rem rgba(91,66,47,.15);
        }

        .btn {
            border-radius: 4px;
            padding: 8px 14px;
        }

        .btn-primary {
            background: #5b422f;
            border-color: #5b422f;
            color: #ffffff;
        }

        .btn-primary:hover {
            background: #493424;
            border-color: #493424;
            color: #ffffff;
        }

        .btn-secondary {
            background: #6c757d;
            border-color: #6c757d;
            color: #ffffff;
        }

        @media (max-width: 768px) {
            .halaman {
                padding: 15px;
            }

            .form-card {
                padding: 18px;
            }
        }
    </style>
</head>

<body>
<div class="container-fluid halaman">

    <div class="d-flex justify-content-between align-items-center
                flex-wrap gap-2 mb-4">

        <div>
            <h3 class="judul-halaman">
                <?= $isEdit ? 'Edit Data Guru' : 'Tambah Data Guru' ?>
            </h3>

            <p class="deskripsi">
                <?= $isEdit
                    ? 'Perbarui informasi guru.'
                    : 'Masukkan informasi guru baru.' ?>
            </p>
        </div>

        <a href="guru.php" class="btn btn-secondary">
            ← Kembali ke Data Guru
        </a>
    </div>

    <?php if ($pesan !== ''): ?>
        <div class="alert alert-danger">
            <?= aman($pesan) ?>
        </div>
    <?php endif; ?>

    <div class="form-card">

        <form method="POST"
              action="tambah_guru.php<?= $isEdit ? '?id=' . (int)$edit['id'] : '' ?>">

            <input type="hidden"
                   name="id"
                   value="<?= (int)($edit['id'] ?? 0) ?>">

            <div class="row g-3">

                <div class="col-md-6">
                    <label class="form-label">NIP</label>
                    <input type="text"
                           name="nip"
                           class="form-control"
                           value="<?= aman($edit['nip'] ?? '') ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Nama Guru *</label>
                    <input type="text"
                           name="nama"
                           class="form-control"
                           required
                           value="<?= aman($edit['nama'] ?? '') ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email"
                           name="email"
                           class="form-control"
                           value="<?= aman($edit['email'] ?? '') ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Status Aktif</label>
                    <select name="status_aktif" class="form-select">
                        <option value="aktif"
                            <?= ($edit['status_aktif'] ?? 'aktif') === 'aktif'
                                ? 'selected' : '' ?>>
                            Aktif
                        </option>

                        <option value="nonaktif"
                            <?= ($edit['status_aktif'] ?? '') === 'nonaktif'
                                ? 'selected' : '' ?>>
                            Nonaktif
                        </option>
                    </select>
                </div>

            </div>

            <div class="d-flex justify-content-end flex-wrap gap-2 mt-4">

                <a href="guru.php" class="btn btn-secondary">
                    Batal
                </a>

                <button type="submit" class="btn btn-primary">
                    <?= $isEdit ? 'Simpan Perubahan' : 'Tambah Data' ?>
                </button>

            </div>

        </form>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>