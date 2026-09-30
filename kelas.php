
<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit;
}

require_once 'config/koneksi.php';

if (($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

function aman($nilai) {
    return htmlspecialchars((string)($nilai ?? ''), ENT_QUOTES, 'UTF-8');
}

$pesan = '';
$edit = null;

// TAMBAH DAN EDIT KELAS
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';

    if ($aksi === 'simpan') {
        $id = (int)($_POST['id'] ?? 0);
        $nama = trim($_POST['nama'] ?? '');
        $tingkat = trim($_POST['tingkat'] ?? '');
        $jurusan = trim($_POST['jurusan'] ?? '');
        $status = (int)($_POST['status_aktif'] ?? 1);

        if ($nama === '') {
            $pesan = 'Nama kelas wajib diisi.';
        } elseif (!in_array($status, [0, 1], true)) {
            $pesan = 'Status kelas tidak valid.';
        } else {
            if ($id > 0) {
                $stmt = $koneksi->prepare(
                    "UPDATE t_kelas
                     SET nama=?, tingkat=?, jurusan=?, status_aktif=?
                     WHERE id=?"
                );
                $stmt->bind_param(
                    "sssii", $nama, $tingkat,
                    $jurusan, $status, $id
                );
            } else {
                $stmt = $koneksi->prepare(
                    "INSERT INTO t_kelas
                     (nama, tingkat, jurusan, status_aktif)
                     VALUES (?, ?, ?, ?)"
                );
                $stmt->bind_param(
                    "sssi", $nama, $tingkat,
                    $jurusan, $status
                );
            }

            if ($stmt->execute()) {
                $stmt->close();
                header("Location: kelas.php?berhasil=1");
                exit;
            } else {
                $pesan = 'Gagal menyimpan data kelas.';
                $stmt->close();
            }
        }
    }

    // HAPUS KELAS
    if ($aksi === 'hapus') {
        $id = (int)($_POST['id'] ?? 0);

        $stmt = $koneksi->prepare(
            "DELETE FROM t_kelas WHERE id=?"
        );
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            $stmt->close();
            header("Location: kelas.php?terhapus=1");
            exit;
        } else {
            $pesan = 'Kelas tidak dapat dihapus karena mungkin masih digunakan.';
            $stmt->close();
        }
    }
}

// AMBIL DATA UNTUK EDIT
if (isset($_GET['edit'])) {
    $idEdit = (int)$_GET['edit'];

    $stmt = $koneksi->prepare(
        "SELECT * FROM t_kelas WHERE id=?"
    );
    $stmt->bind_param("i", $idEdit);
    $stmt->execute();

    $edit = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// PENCARIAN KELAS
$cari = trim($_GET['cari'] ?? '');
$kata = '%' . $cari . '%';

$stmt = $koneksi->prepare(
    "SELECT * FROM t_kelas
     WHERE nama LIKE ? OR tingkat LIKE ? OR jurusan LIKE ?
     ORDER BY nama ASC"
);
$stmt->bind_param("sss", $kata, $kata, $kata);
$stmt->execute();

$query = $stmt->get_result();

if (isset($_GET['berhasil'])) {
    $pesan = 'Data kelas berhasil disimpan.';
}

if (isset($_GET['terhapus'])) {
    $pesan = 'Data kelas berhasil dihapus.';
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Data Kelas</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <style>
        body {
            background: #f4f7fb;
            font-family: Arial, sans-serif;
        }

        .container-fluid {
            padding: 24px;
        }

        .card {
            border: none;
            border-radius: 12px;
            margin-bottom: 22px;
        }

        .judul {
            font-weight: 700;
            color: #243247;
        }

        .table thead th {
            background: #0d6efd;
            color: white;
            white-space: nowrap;
        }

        .table td {
            vertical-align: middle;
        }

        .form-control, .form-select, .btn {
            border-radius: 7px;
        }
    </style>
</head>

<body>
<div class="container-fluid">

    <!-- JUDUL DAN TOMBOL KEMBALI -->
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h2 class="judul mb-1">Data Kelas</h2>
            <p class="text-muted mb-0">Kelola data kelas sekolah</p>
        </div>

        <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="badge bg-primary fs-6">
                Total: <?= $query->num_rows ?> kelas ditemukan
            </span>

            <a href="dashboard.php"
               class="btn btn-secondary"
               onclick="if (window.parent !== window) {
                   window.parent.location.href = 'dashboard.php';
                   return false;
               }">
                &larr; Kembali ke Dashboard
            </a>
        </div>
    </div>

    <?php if ($pesan !== '') { ?>
        <div class="alert alert-info alert-dismissible fade show">
            <?= aman($pesan) ?>
            <button type="button" class="btn-close"
                    data-bs-dismiss="alert"></button>
        </div>
    <?php } ?>

    <!-- FORM TAMBAH DAN EDIT -->
    <div class="card shadow-sm">
        <div class="card-body p-4">
            <h5 class="mb-3">
                <?= $edit ? 'Edit Data Kelas' : 'Tambah Data Kelas' ?>
            </h5>

            <form method="POST" action="kelas.php">
                <input type="hidden" name="aksi" value="simpan">
                <input type="hidden" name="id"
                       value="<?= aman($edit['id'] ?? 0) ?>">

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Nama Kelas *</label>
                        <input type="text" name="nama"
                               class="form-control" required
                               placeholder="Contoh: X RPL 1"
                               value="<?= aman($edit['nama'] ?? '') ?>">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Tingkat</label>
                        <input type="text" name="tingkat"
                               class="form-control"
                               placeholder="Contoh: X, XI, XII"
                               value="<?= aman($edit['tingkat'] ?? '') ?>">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Jurusan</label>
                        <input type="text" name="jurusan"
                               class="form-control"
                               placeholder="Contoh: RPL"
                               value="<?= aman($edit['jurusan'] ?? '') ?>">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Status</label>
                        <select name="status_aktif" class="form-select">
                            <option value="1"
                                <?= (int)($edit['status_aktif'] ?? 1) === 1 ? 'selected' : '' ?>>
                                Aktif
                            </option>
                            <option value="0"
                                <?= isset($edit['status_aktif']) &&
                                    (int)$edit['status_aktif'] === 0
                                    ? 'selected' : '' ?>>
                                Nonaktif
                            </option>
                        </select>
                    </div>
                </div>

                <div class="mt-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <?= $edit ? 'Simpan Perubahan' : 'Tambah Kelas' ?>
                    </button>

                    <?php if ($edit) { ?>
                        <a href="kelas.php" class="btn btn-secondary">
                            Batal Edit
                        </a>
                    <?php } ?>
                </div>
            </form>
        </div>
    </div>

    <!-- TABEL KELAS -->
    <div class="card shadow-sm">
        <div class="card-body p-4">

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
                <h5 class="mb-0">Daftar Kelas</h5>

                <form method="GET" action="kelas.php"
                      class="d-flex gap-2">
                    <input type="search" name="cari"
                           class="form-control"
                           placeholder="Cari nama / tingkat / jurusan..."
                           value="<?= aman($cari) ?>">

                    <button class="btn btn-primary">Cari</button>

                    <a href="kelas.php" class="btn btn-secondary">
                        Reset
                    </a>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Kelas</th>
                            <th>Tingkat</th>
                            <th>Jurusan</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php if ($query->num_rows > 0) { ?>
                        <?php $no = 1; ?>

                        <?php while ($data = $query->fetch_assoc()) { ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= aman($data['nama']) ?></td>
                                <td><?= aman($data['tingkat']) ?></td>
                                <td><?= aman($data['jurusan']) ?></td>
                                <td>
                                    <?php if ((int)$data['status_aktif'] === 1) { ?>
                                        <span class="badge bg-success">Aktif</span>
                                    <?php } else { ?>
                                        <span class="badge bg-secondary">Nonaktif</span>
                                    <?php } ?>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="kelas.php?edit=<?= (int)$data['id'] ?>"
                                           class="btn btn-warning btn-sm">
                                            Edit
                                        </a>

                                        <form method="POST" action="kelas.php"
                                              onsubmit="return confirm('Yakin ingin menghapus kelas ini?')">
                                            <input type="hidden" name="aksi"
                                                   value="hapus">
                                            <input type="hidden" name="id"
                                                   value="<?= (int)$data['id'] ?>">

                                            <button type="submit"
                                                    class="btn btn-danger btn-sm">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
                    <?php } else { ?>
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                Data kelas tidak ditemukan.
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$stmt->close();
?>