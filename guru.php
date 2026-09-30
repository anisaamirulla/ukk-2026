
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

if (($_GET['pesan'] ?? '') === 'sukses') {
    $pesan = 'Data guru berhasil disimpan.';
} elseif (($_GET['pesan'] ?? '') === 'hapus') {
    $pesan = 'Data guru berhasil dihapus.';
}

// HAPUS DATA GURU
if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && ($_POST['aksi'] ?? '') === 'hapus') {

    $id = (int)($_POST['id'] ?? 0);

    $stmt = $koneksi->prepare("DELETE FROM t_guru WHERE id = ?");
    $stmt->bind_param('i', $id);

    if ($stmt->execute()) {
        $stmt->close();
        header('Location: guru.php?pesan=hapus');
        exit;
    }

    $pesan = 'Data gagal dihapus. Data mungkin sedang digunakan.';
    $stmt->close();
}

// PENCARIAN DATA GURU
$cari = trim($_GET['cari'] ?? '');

if ($cari !== '') {
    $kata = "%{$cari}%";

    $stmt = $koneksi->prepare(
        "SELECT * FROM t_guru
         WHERE nama LIKE ? OR nip LIKE ? OR email LIKE ?
         ORDER BY id DESC"
    );

    $stmt->bind_param('sss', $kata, $kata, $kata);
    $stmt->execute();
    $dataGuru = $stmt->get_result();
} else {
    $dataGuru = $koneksi->query(
        "SELECT * FROM t_guru ORDER BY id DESC"
    );
}

// TOTAL GURU
$hasilJumlah = $koneksi->query(
    "SELECT COUNT(*) AS total FROM t_guru"
);

$jumlahGuru = $hasilJumlah
    ? (int)$hasilJumlah->fetch_assoc()['total']
    : 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Data Guru</title>

    <!-- Bootstrap 5 -->
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

        .kartu {
            background: #ffffff;
            padding: 22px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .06);
        }

        /* Warna tombol utama cokelat */
        .btn {
            border-radius: 4px;
        }

        .btn-primary,
        .btn-cokelat {
            background: #604431;
            border-color: #604431;
            color: #ffffff;
        }

        .btn-primary:hover,
        .btn-primary:focus,
        .btn-cokelat:hover,
        .btn-cokelat:focus {
            background: #493424;
            border-color: #493424;
            color: #ffffff;
        }

        .btn-secondary {
            background: #6c757d;
            border-color: #6c757d;
            color: #ffffff;
        }

        .btn-warning {
            background: #e5d9c8;
            border-color: #e5d9c8;
            color: #493424;
        }

        .btn-warning:hover {
            background: #d6c5ae;
            border-color: #d6c5ae;
            color: #493424;
        }

        .btn-danger {
            background: #dc3545;
            border-color: #dc3545;
            color: #ffffff;
        }

        /* Badge total guru: cokelat, bukan biru */
        .badge-total {
            background: #604431;
            color: #ffffff;
            font-size: 13px;
            padding: 8px 12px;
            border-radius: 4px;
        }

        /* Kotak pencarian */
        .form-control {
            border-radius: 5px;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #b5a18f;
            box-shadow: 0 0 0 .15rem rgba(91, 66, 47, .15);
        }

        /* Tabel */
        .table {
            margin-bottom: 0;
        }

        .table th {
            background: #6b4b36;
            color: #ffffff;
            white-space: nowrap;
        }

        .table td {
            vertical-align: middle;
        }

        .table tbody tr:hover td {
            background: #f0e7dd;
        }

        /* Tampilan HP */
        @media (max-width: 768px) {
            .halaman {
                padding: 15px;
            }

            .kartu {
                padding: 12px;
            }

            .judul-halaman {
                font-size: 23px;
            }
        }
    </style>
</head>

<body>

<div class="container-fluid halaman">

    <!-- JUDUL HALAMAN -->
    <div class="d-flex justify-content-between align-items-center
                flex-wrap gap-2 mb-4">

        <div>
            <h3 class="judul-halaman">Data Guru</h3>
            <p class="deskripsi">Kelola data guru sekolah</p>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2">

            <span class="badge badge-total">
                Total: <?= $jumlahGuru ?> guru ditemukan
            </span>

            <a href="dashboard.php"
               class="btn btn-secondary"
               onclick="if (window.parent !== window) {
                   window.parent.location.href='dashboard.php';
                   return false;
               }">
                ← Kembali ke Dashboard
            </a>

        </div>
    </div>

    <!-- PESAN -->
    <?php if ($pesan !== ''): ?>
        <div class="alert alert-info">
            <?= aman($pesan) ?>
        </div>
    <?php endif; ?>

    <!-- TOMBOL TAMBAH GURU DIPISAH DARI TABEL -->
    <div class="mb-3">
        <a href="tambah_guru.php" class="btn btn-primary">
            + Tambah Guru
        </a>
    </div>

    <!-- KARTU TABEL GURU -->
    <div class="kartu">

        <!-- PENCARIAN -->
        <form method="GET"
              action="guru.php"
              class="d-flex justify-content-end flex-wrap gap-2 mb-3">

            <input type="text"
                   name="cari"
                   class="form-control"
                   style="max-width: 280px;"
                   placeholder="Cari nama, NIP, atau email..."
                   value="<?= aman($cari) ?>">

            <button type="submit" class="btn btn-primary">
                Cari
            </button>

            <a href="guru.php" class="btn btn-outline-secondary">
                Reset
            </a>

        </form>

        <!-- TABEL -->
        <div class="table-responsive">

            <table class="table table-bordered table-hover">

                <thead>
                    <tr>
                        <th>No.</th>
                        <th>NIP</th>
                        <th>Nama Guru</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>

                <?php if ($dataGuru && $dataGuru->num_rows > 0): ?>

                    <?php $no = 1; ?>

                    <?php while ($g = $dataGuru->fetch_assoc()): ?>

                        <tr>
                            <td><?= $no++ ?></td>

                            <td><?= aman($g['nip']) ?></td>

                            <td><?= aman($g['nama']) ?></td>

                            <td><?= aman($g['email']) ?></td>

                            <td>
                                <?php if ($g['status_aktif'] === 'aktif'): ?>
                                    <span class="badge bg-success">
                                        Aktif
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">
                                        Nonaktif
                                    </span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <div class="d-flex gap-1">

                                    <!-- EDIT -->
                                    <a href="tambah_guru.php?id=<?= (int)$g['id'] ?>"
                                       class="btn btn-warning btn-sm">
                                        Edit
                                    </a>

                                    <!-- HAPUS -->
                                    <form method="POST"
                                          action="guru.php"
                                          onsubmit="return confirm('Yakin ingin menghapus data guru ini?')">

                                        <input type="hidden"
                                               name="aksi"
                                               value="hapus">

                                        <input type="hidden"
                                               name="id"
                                               value="<?= (int)$g['id'] ?>">

                                        <button type="submit"
                                                class="btn btn-danger btn-sm">
                                            Hapus
                                        </button>

                                    </form>

                                </div>
                            </td>
                        </tr>

                    <?php endwhile; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="6"
                            class="text-center text-secondary py-4">
                            Data guru belum tersedia.
                        </td>
                    </tr>

                <?php endif; ?>

                </tbody>
            </table>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>