
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

// AMBIL DATA UNTUK EDIT
if ($id > 0) {
    $stmt = $koneksi->prepare(
        "SELECT * FROM t_siswa WHERE id = ?"
    );
    $stmt->bind_param('i', $id);
    $stmt->execute();

    $hasil = $stmt->get_result();
    $edit = $hasil->fetch_assoc();
    $stmt->close();

    if (!$edit) {
        header('Location: siswa.php');
        exit;
    }
}

// PROSES TAMBAH DAN EDIT
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $nisn = trim($_POST['nisn'] ?? '');
    $nip = trim($_POST['nip'] ?? '');
    $nama = trim($_POST['nama'] ?? '');
    $jk = $_POST['jenis_kelamin'] ?? '';
    $tanggal = $_POST['tanggal_lahir'] ?? '';
    $alamat = trim($_POST['alamat'] ?? '');
    $status = $_POST['status_aktif'] ?? 'aktif';

    if ($nama === '') {
        $pesan = 'Nama siswa wajib diisi.';
    } elseif (!in_array($jk, ['', 'L', 'P'], true)) {
        $pesan = 'Jenis kelamin tidak valid.';
    } elseif (!in_array($status, ['aktif', 'nonaktif'], true)) {
        $pesan = 'Status tidak valid.';
    } else {
        if ($id > 0) {
            $stmt = $koneksi->prepare(
                "UPDATE t_siswa SET
                    nisn = ?,
                    nip = ?,
                    nama = ?,
                    jenis_kelamin = ?,
                    tanggal_lahir = NULLIF(?, ''),
                    alamat = ?,
                    status_aktif = ?
                 WHERE id = ?"
            );

            $stmt->bind_param(
                'sssssssi',
                $nisn,
                $nip,
                $nama,
                $jk,
                $tanggal,
                $alamat,
                $status,
                $id
            );
        } else {
            $stmt = $koneksi->prepare(
                "INSERT INTO t_siswa
                    (nisn, nip, nama, jenis_kelamin,
                     tanggal_lahir, alamat, status_aktif)
                 VALUES (?, ?, ?, ?, NULLIF(?, ''), ?, ?)"
            );

            $stmt->bind_param(
                'sssssss',
                $nisn,
                $nip,
                $nama,
                $jk,
                $tanggal,
                $alamat,
                $status
            );
        }

        if ($stmt->execute()) {
            $stmt->close();
            header('Location: siswa.php?pesan=sukses');
            exit;
        } else {
            $pesan = 'Gagal menyimpan data: ' . $stmt->error;
            $stmt->close();
        }
    }

    // PERTAHANKAN ISIAN JIKA TERJADI ERROR
    $edit = [
        'id' => $id,
        'nisn' => $nisn,
        'nip' => $nip,
        'nama' => $nama,
        'jenis_kelamin' => $jk,
        'tanggal_lahir' => $tanggal,
        'alamat' => $alamat,
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

    <title><?= $isEdit ? 'Edit Data Siswa' : 'Tambah Data Siswa' ?></title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <!-- Pengaturan tampilan langsung di file ini -->
    <style>
        body {
            background-color: #f5f5f5;
            font-family: Arial, sans-serif;
            color: #33271f;
        }

        .halaman {
            padding: 25px;
        }

        .form-card {
            background-color: #fff;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(91, 66, 47, .08);
        }

        h3 {
            color: #33271f;
        }

        .form-label {
            font-weight: 600;
            color: #33271f;
        }

        /* Tombol utama cokelat */
        .btn-primary {
            background-color: #5b422f;
            border-color: #5b422f;
            color: #fff;
        }

        .btn-primary:hover,
        .btn-primary:focus {
            background-color: #493424;
            border-color: #493424;
            color: #fff;
        }

        /* Tombol kembali */
        .btn-secondary {
            background-color: #77716b;
            border-color: #77716b;
            color: #fff;
        }

        .btn-secondary:hover {
            background-color: #625d58;
            border-color: #625d58;
            color: #fff;
        }

        /* Tombol batal */
        .btn-outline-secondary {
            color: #5b422f;
            border-color: #b8a694;
        }

        .btn-outline-secondary:hover {
            background-color: #e5d9c8;
            border-color: #e5d9c8;
            color: #493424;
        }

        /* Input dan pilihan */
        .form-control,
        .form-select {
            border-color: #dedede;
            border-radius: 6px;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #9c8065;
            box-shadow: 0 0 0 .2rem rgba(91, 66, 47, .15);
        }

        @media (max-width: 768px) {
            .halaman {
                padding: 12px;
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
            <h3 class="fw-bold mb-1">
                <?= $isEdit ? 'Edit Data Siswa' : 'Tambah Data Siswa' ?>
            </h3>

            <p class="text-secondary mb-0">
                <?= $isEdit
                    ? 'Perbarui informasi siswa.'
                    : 'Masukkan informasi siswa baru.' ?>
            </p>
        </div>

        <a href="dashboard.php"
           class="btn btn-secondary"
           onclick="if (window.parent !== window) {
               window.parent.location.href='dashboard.php';
               return false;
           }">
            ← Kembali ke Dashboard
        </a>
    </div>

    <?php if ($pesan !== ''): ?>
        <div class="alert alert-danger">
            <?= aman($pesan) ?>
        </div>
    <?php endif; ?>

    <div class="form-card">

        <form method="POST"
              action="tambah_siswa.php<?= $isEdit ? '?id=' . (int)$edit['id'] : '' ?>">

            <input type="hidden"
                   name="id"
                   value="<?= (int)($edit['id'] ?? 0) ?>">

            <div class="row g-3">

                <div class="col-md-6">
                    <label class="form-label">NISN</label>
                    <input type="text"
                           name="nisn"
                           class="form-control"
                           value="<?= aman($edit['nisn'] ?? '') ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">NIP</label>
                    <input type="text"
                           name="nip"
                           class="form-control"
                           value="<?= aman($edit['nip'] ?? '') ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Nama Siswa *</label>
                    <input type="text"
                           name="nama"
                           class="form-control"
                           required
                           value="<?= aman($edit['nama'] ?? '') ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Jenis Kelamin</label>
                    <select name="jenis_kelamin" class="form-select">
                        <option value="">Pilih jenis kelamin</option>

                        <option value="L"
                            <?= ($edit['jenis_kelamin'] ?? '') === 'L' ? 'selected' : '' ?>>
                            Laki-laki
                        </option>

                        <option value="P"
                            <?= ($edit['jenis_kelamin'] ?? '') === 'P' ? 'selected' : '' ?>>
                            Perempuan
                        </option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Tanggal Lahir</label>
                    <input type="date"
                           name="tanggal_lahir"
                           class="form-control"
                           value="<?= aman($edit['tanggal_lahir'] ?? '') ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Status Aktif</label>
                    <select name="status_aktif" class="form-select">
                        <option value="aktif"
                            <?= ($edit['status_aktif'] ?? 'aktif') === 'aktif' ? 'selected' : '' ?>>
                            Aktif
                        </option>

                        <option value="nonaktif"
                            <?= ($edit['status_aktif'] ?? '') === 'nonaktif' ? 'selected' : '' ?>>
                            Nonaktif
                        </option>
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label">Alamat</label>
                    <textarea name="alamat"
                              class="form-control"
                              rows="3"><?= aman($edit['alamat'] ?? '') ?></textarea>
                </div>

            </div>

            <div class="d-flex justify-content-end flex-wrap gap-2 mt-4">

                <a href="dashboard.php"
                   class="btn btn-secondary"
                   onclick="if (window.parent !== window) {
                       window.parent.location.href='dashboard.php';
                       return false;
                   }">
                    ← Kembali ke Dashboard
                </a>

                <a href="siswa.php" class="btn btn-outline-secondary">
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