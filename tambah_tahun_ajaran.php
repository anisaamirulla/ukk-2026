
<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    echo "Anda tidak memiliki akses";
    exit;
}

require_once __DIR__ . "/config/koneksi.php";

$pesan = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $tanggal_mulai = $_POST['tanggal_mulai'] ?? '';
    $tanggal_selesai = $_POST['tanggal_selesai'] ?? '';
    $status_aktif = isset($_POST['status_aktif']) ? 1 : 0;

    if ($nama === '' || $tanggal_mulai === '' || $tanggal_selesai === '') {
        $pesan = "Semua data wajib diisi.";
    } elseif ($tanggal_selesai < $tanggal_mulai) {
        $pesan = "Tanggal selesai tidak boleh sebelum tanggal mulai.";
    } else {
        mysqli_begin_transaction($koneksi);

        try {
            // Jika tahun ajaran ini aktif, nonaktifkan tahun ajaran lainnya.
            if ($status_aktif === 1) {
                $nonaktifkan = mysqli_query(
                    $koneksi,
                    "UPDATE t_tahun_ajaran SET status_aktif = 0"
                );

                if (!$nonaktifkan) {
                    throw new Exception(mysqli_error($koneksi));
                }
            }

            $stmt = mysqli_prepare(
                $koneksi,
                "INSERT INTO t_tahun_ajaran
                (nama, tanggal_mulai, tanggal_selesai, status_aktif)
                VALUES (?, ?, ?, ?)"
            );

            if (!$stmt) {
                throw new Exception(mysqli_error($koneksi));
            }

            mysqli_stmt_bind_param(
                $stmt,
                "sssi",
                $nama,
                $tanggal_mulai,
                $tanggal_selesai,
                $status_aktif
            );

            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception(mysqli_stmt_error($stmt));
            }

            mysqli_stmt_close($stmt);
            mysqli_commit($koneksi);

            header("Location: tahun_ajaran.php?pesan=berhasil");
            exit;
        } catch (Exception $e) {
            mysqli_rollback($koneksi);
            $pesan = "Gagal menyimpan data tahun ajaran.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tambah Tahun Ajaran</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>

<body class="bg-light">

<div class="container-fluid">
    <div class="row">

        <!-- SIDEBAR -->
        <div class="col-md-3 col-lg-2 bg-dark min-vh-100 p-3">
            <h4 class="text-white mb-4">Admin UKK</h4>

            <ul class="nav flex-column gap-1">
                <li class="nav-item">
                    <a href="dashboard.php" class="nav-link text-white rounded">
                        Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="siswa.php" class="nav-link text-white rounded">
                        Data Siswa
                    </a>
                </li>
                <li class="nav-item">
                    <a href="guru.php" class="nav-link text-white rounded">
                        Data Guru
                    </a>
                </li>
                <li class="nav-item">
                    <a href="kelas.php" class="nav-link text-white rounded">
                        Data Kelas
                    </a>
                </li>
                <li class="nav-item">
                    <a href="tahun_ajaran.php"
                       class="nav-link active bg-primary text-white rounded">
                        Tahun Ajaran
                    </a>
                </li>
                <li class="nav-item">
                    <a href="penempatan_siswa.php" class="nav-link text-white rounded">
                        Penempatan Siswa
                    </a>
                </li>
                <li class="nav-item">
                    <a href="wali_kelas.php" class="nav-link text-white rounded">
                        Wali Kelas
                    </a>
                </li>
                <li class="nav-item">
                    <a href="kategori_pelanggaran.php" class="nav-link text-white rounded">
                        Kategori Pelanggaran
                    </a>
                </li>
                <li class="nav-item">
                    <a href="jenis_pelanggaran.php" class="nav-link text-white rounded">
                        Jenis Pelanggaran
                    </a>
                </li>
                <li class="nav-item">
                    <a href="cetak_export.php" class="nav-link text-white rounded">
                        Cetak / Export
                    </a>
                </li>
                <li class="nav-item">
                    <a href="logout.php" class="nav-link text-danger rounded">
                        Logout
                    </a>
                </li>
            </ul>
        </div>

        <!-- KONTEN -->
        <div class="col-md-9 col-lg-10 p-4">

            <div class="mb-4">
                <h2 class="fw-bold mb-1">Tambah Tahun Ajaran</h2>
                <p class="text-secondary mb-2">
                    Form tambah data tahun ajaran
                </p>
                <a href="tahun_ajaran.php" class="btn btn-secondary btn-sm">
                    Kembali
                </a>
            </div>

            <?php if ($pesan !== '') { ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($pesan); ?>
                </div>
            <?php } ?>

            <div class="card border-0 shadow-sm">
                <div class="card-body">

                    <form method="POST">

                        <div class="mb-3">
                            <label for="nama" class="form-label">
                                Nama Tahun Ajaran
                            </label>
                            <input type="text"
                                   name="nama"
                                   id="nama"
                                   class="form-control"
                                   placeholder="Contoh: 2026/2027"
                                   value="<?= htmlspecialchars($_POST['nama'] ?? ''); ?>"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label for="tanggal_mulai" class="form-label">
                                Tanggal Mulai
                            </label>
                            <input type="date"
                                   name="tanggal_mulai"
                                   id="tanggal_mulai"
                                   class="form-control"
                                   value="<?= htmlspecialchars($_POST['tanggal_mulai'] ?? ''); ?>"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label for="tanggal_selesai" class="form-label">
                                Tanggal Selesai
                            </label>
                            <input type="date"
                                   name="tanggal_selesai"
                                   id="tanggal_selesai"
                                   class="form-control"
                                   value="<?= htmlspecialchars($_POST['tanggal_selesai'] ?? ''); ?>"
                                   required>
                        </div>

                        <div class="form-check mb-4">
                            <input type="checkbox"
                                   name="status_aktif"
                                   id="status_aktif"
                                   class="form-check-input"
                                   value="1"
                                   <?= isset($_POST['status_aktif']) ? 'checked' : ''; ?>>
                            <label for="status_aktif" class="form-check-label">
                                Jadikan tahun ajaran aktif
                            </label>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            Simpan
                        </button>

                        <a href="tahun_ajaran.php" class="btn btn-secondary">
                            Batal
                        </a>

                    </form>

                </div>
            </div>

        </div>
    </div>
</div>

</body>
</html>
```