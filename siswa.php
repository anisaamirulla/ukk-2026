
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
    $pesan = 'Data siswa berhasil disimpan.';
} elseif (($_GET['pesan'] ?? '') === 'hapus') {
    $pesan = 'Data siswa berhasil dihapus.';
}

// HAPUS DATA
if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && ($_POST['aksi'] ?? '') === 'hapus'
) {
    $id = (int)($_POST['id'] ?? 0);

    $stmt = $koneksi->prepare(
        "DELETE FROM t_siswa WHERE id = ?"
    );
    $stmt->bind_param('i', $id);

    if ($stmt->execute()) {
        $stmt->close();
        header('Location: siswa.php?pesan=hapus');
        exit;
    }

    $pesan = 'Data gagal dihapus. Data mungkin sedang digunakan.';
    $stmt->close();
}

// PENCARIAN DATA
$cari = trim($_GET['cari'] ?? '');

if ($cari !== '') {
    $kata = "%{$cari}%";

    $stmt = $koneksi->prepare(
        "SELECT * FROM t_siswa
         WHERE nama LIKE ? OR nisn LIKE ? OR nip LIKE ?
         ORDER BY id DESC"
    );

    $stmt->bind_param('sss', $kata, $kata, $kata);
    $stmt->execute();
    $dataSiswa = $stmt->get_result();
} else {
    $dataSiswa = $koneksi->query(
        "SELECT * FROM t_siswa ORDER BY id DESC"
    );
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Data Siswa</title>

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

        .kartu {
            background-color: #fff;
            padding: 22px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(91, 66, 47, .08);
        }

        h3 {
            color: #33271f;
        }

        /* Header tabel */
        .table thead th {
            background-color: #684832;
            color: #fff;
            border-color: #765941;
            white-space: nowrap;
        }

        .table td {
            vertical-align: middle;
        }

        .table-hover tbody tr:hover td {
            background-color: #f5eee6;
        }

        /* Tombol utama */
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

        /* Tombol edit */
        .btn-warning {
            background-color: #e5d9c8;
            border-color: #e5d9c8;
            color: #493424;
        }

        .btn-warning:hover {
            background-color: #d6c5ae;
            border-color: #d6c5ae;
            color: #493424;
        }

        /* Tombol reset */
        .btn-outline-secondary {
            color: #5b422f;
            border-color: #b8a694;
        }

        .btn-outline-secondary:hover {
            background-color: #e5d9c8;
            border-color: #e5d9c8;
            color: #493424;
        }

        /* Kolom pencarian */
        .form-control {
            border-color: #dedede;
            border-radius: 6px;
        }

        .form-control:focus {
            border-color: #9c8065;
            box-shadow: 0 0 0 .2rem rgba(91, 66, 47, .15);
        }

        @media (max-width: 768px) {
            .halaman {
                padding: 12px;
            }

            .kartu {
                padding: 14px;
            }
        }
    </style>
</head>

<body>
<div class="container-fluid halaman">

    <div class="d-flex justify-content-between align-items-center
                flex-wrap gap-2 mb-4">

        <div>
            <h3 class="fw-bold mb-1">Data Siswa</h3>
            <p class="text-secondary mb-0">
                Kelola data siswa sekolah
            </p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="dashboard.php"
               class="btn btn-secondary"
               onclick="if (window.parent !== window) {
                   window.parent.location.href='dashboard.php';
                   return false;
               }">
                ← Kembali ke Dashboard
            </a>

            <a href="tambah_siswa.php" class="btn btn-primary">
                + Tambah Siswa
            </a>
        </div>
    </div>

    <?php if ($pesan !== ''): ?>
        <div class="alert alert-info">
            <?= aman($pesan) ?>
        </div>
    <?php endif; ?>

    <div class="kartu">

        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-5">
                <input type="text"
                       name="cari"
                       class="form-control"
                       placeholder="Cari nama, NISN, atau NIP..."
                       value="<?= aman($cari) ?>">
            </div>

            <div class="col-auto">
                <button type="submit" class="btn btn-primary">
                    Cari
                </button>

                <a href="siswa.php" class="btn btn-outline-secondary">
                    Reset
                </a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>NISN</th>
                        <th>NIP</th>
                        <th>Nama Siswa</th>
                        <th>Jenis Kelamin</th>
                        <th>Tanggal Lahir</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                <?php if ($dataSiswa && $dataSiswa->num_rows > 0): ?>
                    <?php $no = 1; ?>

                    <?php while ($s = $dataSiswa->fetch_assoc()): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= aman($s['nisn']) ?></td>
                            <td><?= aman($s['nip']) ?></td>
                            <td><?= aman($s['nama']) ?></td>

                            <td>
                                <?php
                                if ($s['jenis_kelamin'] === 'L') {
                                    echo 'Laki-laki';
                                } elseif ($s['jenis_kelamin'] === 'P') {
                                    echo 'Perempuan';
                                } else {
                                    echo '-';
                                }
                                ?>
                            </td>

                            <td><?= aman($s['tanggal_lahir']) ?></td>

                            <td>
                                <?php if ($s['status_aktif'] === 'aktif'): ?>
                                    <span class="badge bg-success">Aktif</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Nonaktif</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <div class="d-flex gap-1">
                                    <a href="tambah_siswa.php?id=<?= (int)$s['id'] ?>"
                                       class="btn btn-warning btn-sm">
                                        Edit
                                    </a>

                                    <form method="POST"
                                          onsubmit="return confirm('Yakin ingin menghapus data siswa ini?')">
                                        <input type="hidden"
                                               name="aksi"
                                               value="hapus">

                                        <input type="hidden"
                                               name="id"
                                               value="<?= (int)$s['id'] ?>">

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
                        <td colspan="8" class="text-center text-secondary">
                            Data siswa belum tersedia.
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