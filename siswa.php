
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

$pesan = "";

// PROSES HAPUS DATA SISWA
if (isset($_POST['hapus'])) {
    $id = (int) ($_POST['id'] ?? 0);

    if ($id <= 0) {
        header("Location: siswa.php?pesan=gagal_hapus");
        exit;
    }

    mysqli_report(MYSQLI_REPORT_OFF);

    $cek = mysqli_query($koneksi, "SELECT id FROM t_siswa WHERE id = $id");

    if (!$cek || mysqli_num_rows($cek) === 0) {
        header("Location: siswa.php?pesan=gagal_hapus");
        exit;
    }

    mysqli_begin_transaction($koneksi);

    $hapusPelanggaran = mysqli_query(
        $koneksi,
        "DELETE FROM t_pelanggaran_siswa WHERE siswa_id = $id"
    );

    $hapusKelas = false;

    if ($hapusPelanggaran) {
        $hapusKelas = mysqli_query(
            $koneksi,
            "DELETE FROM t_kelas_siswa WHERE siswa_id = $id"
        );
    }

    $hapusSiswa = false;

    if ($hapusPelanggaran && $hapusKelas) {
        $hapusSiswa = mysqli_query(
            $koneksi,
            "DELETE FROM t_siswa WHERE id = $id"
        );
    }

    if ($hapusPelanggaran && $hapusKelas && $hapusSiswa) {
        mysqli_commit($koneksi);
        header("Location: siswa.php?pesan=hapus_berhasil");
        exit;
    } else {
        mysqli_rollback($koneksi);
        header("Location: siswa.php?pesan=gagal_hapus");
        exit;
    }
}

// PROSES UPDATE DATA SISWA
if (isset($_POST['update'])) {
    $id = (int) ($_POST['id'] ?? 0);
    $nisn = trim($_POST['nisn'] ?? '');
    $nip = trim($_POST['nip'] ?? '');
    $nama = trim($_POST['nama'] ?? '');
    $jenis_kelamin = $_POST['jenis_kelamin'] ?? '';
    $tanggal_lahir = $_POST['tanggal_lahir'] ?? '';
    $status_aktif = (int) ($_POST['status_aktif'] ?? -1);

    if (
        $id <= 0 ||
        $nama === '' ||
        $tanggal_lahir === '' ||
        !in_array($jenis_kelamin, ['L', 'P'], true) ||
        !in_array($status_aktif, [0, 1], true)
    ) {
        $pesan = "Data tidak valid. Periksa kembali semua isian.";
    } elseif (!preg_match('/^[0-9]{10}$/', $nisn)) {
        $pesan = "NISN wajib tepat 10 digit angka.";
    } elseif (!preg_match('/^[0-9]{10}$/', $nip)) {
        $pesan = "NIS wajib tepat 10 digit angka.";
    } else {
        $stmt = mysqli_prepare($koneksi, "
            UPDATE t_siswa
            SET nisn = ?, nip = ?, nama = ?,
                jenis_kelamin = ?, tanggal_lahir = ?,
                status_aktif = ?
            WHERE id = ?
        ");

        if ($stmt) {
            mysqli_stmt_bind_param(
                $stmt,
                "sssssii",
                $nisn,
                $nip,
                $nama,
                $jenis_kelamin,
                $tanggal_lahir,
                $status_aktif,
                $id
            );

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                header("Location: siswa.php?pesan=edit_berhasil");
                exit;
            } else {
                $pesan = "Gagal mengubah data siswa: " .
                    mysqli_stmt_error($stmt);
                mysqli_stmt_close($stmt);
            }
        } else {
            $pesan = "Gagal menyiapkan perubahan data.";
        }
    }
}

// AMBIL DATA EDIT
$dataEdit = null;

if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $idEdit = (int) $_GET['edit'];

    $stmtEdit = mysqli_prepare(
        $koneksi,
        "SELECT * FROM t_siswa WHERE id = ?"
    );

    mysqli_stmt_bind_param($stmtEdit, "i", $idEdit);
    mysqli_stmt_execute($stmtEdit);
    $resultEdit = mysqli_stmt_get_result($stmtEdit);
    $dataEdit = mysqli_fetch_assoc($resultEdit);
    mysqli_stmt_close($stmtEdit);

    if (!$dataEdit) {
        $pesan = "Data siswa tidak ditemukan.";
    } elseif (isset($_POST['update'])) {
        $dataEdit['nisn'] = $_POST['nisn'] ?? '';
        $dataEdit['nip'] = $_POST['nip'] ?? '';
        $dataEdit['nama'] = $_POST['nama'] ?? '';
        $dataEdit['jenis_kelamin'] = $_POST['jenis_kelamin'] ?? '';
        $dataEdit['tanggal_lahir'] = $_POST['tanggal_lahir'] ?? '';
        $dataEdit['status_aktif'] = $_POST['status_aktif'] ?? 1;
    }
}

// AMBIL DATA SISWA
$query = mysqli_query(
    $koneksi,
    "SELECT * FROM t_siswa ORDER BY id ASC"
);

if (!$query) {
    die("Gagal mengambil data siswa: " . mysqli_error($koneksi));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Data Siswa</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/2.3.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
</head>

<body class="bg-white">

<div class="container-fluid">
    <div class="row">

        <!-- SIDEBAR -->
        <aside class="col-12 col-md-3 col-lg-2 bg-dark min-vh-100 p-3">
            <div class="text-white mb-4">
                <h4 class="fw-bold mb-0">
                    <i class="bi bi-shield-check me-2"></i>Sistem Pelanggaran
                </h4>
                <small class="text-secondary">Panel Admin</small>
            </div>

            <div class="bg-secondary bg-opacity-25 rounded-3 p-3 mb-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-white text-dark rounded-circle p-2">
                        <i class="bi bi-person-fill fs-4"></i>
                    </div>
                    <div class="text-white">
                        <div class="fw-semibold">
                            <?= htmlspecialchars($_SESSION['name'] ?? 'Pengguna'); ?>
                        </div>
                        <small class="text-secondary">ADMIN</small>
                    </div>
                </div>
            </div>

            <div class="text-secondary small fw-semibold mb-2">MENU UTAMA</div>

            <ul class="nav nav-pills flex-column gap-1">
                <li class="nav-item"><a href="dashboard.php" class="nav-link text-white">Dashboard</a></li>
                <li class="nav-item"><a href="siswa.php" class="nav-link active">Data Siswa</a></li>
                <li class="nav-item"><a href="guru.php" class="nav-link text-white">Data Guru</a></li>
                <li class="nav-item"><a href="kelas.php" class="nav-link text-white">Kelas</a></li>
                <li class="nav-item"><a href="tahun_ajaran.php" class="nav-link text-white">Tahun Ajaran</a></li>
                <li class="nav-item"><a href="penempatan_siswa.php" class="nav-link text-white">Penempatan Siswa</a></li>
                <li class="nav-item"><a href="wali_kelas.php" class="nav-link text-white">Wali Kelas</a></li>
                <li class="nav-item"><a href="kategori_pelanggaran.php" class="nav-link text-white">Kategori Pelanggaran</a></li>
                <li class="nav-item"><a href="jenis_pelanggaran.php" class="nav-link text-white">Jenis Pelanggaran</a></li>
                <li class="nav-item"><a href="laporan.php" class="nav-link text-white">Laporan</a></li>
                <li class="nav-item"><a href="abautme.php" class="nav-link text-white">About Me</a></li>
            </ul>

            <hr class="border-secondary">

            <a href="logout.php" class="nav-link text-danger">
                <i class="bi bi-box-arrow-left me-2"></i>Logout
            </a>
        </aside>

        <!-- KONTEN UTAMA -->
        <main class="col-12 col-md-9 col-lg-10 p-3 p-lg-4">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold mb-1">Data Siswa</h2>
                    <p class="text-secondary mb-2">Daftar data siswa</p>
                    <a href="dashboard.php" class="btn btn-secondary btn-sm">Kembali ke Dashboard</a>
                </div>

                <a href="tambah_siswa.php" class="btn btn-primary">+ Tambah Siswa</a>
            </div>

            <!-- NOTIFIKASI -->
            <?php if (isset($_GET['pesan'])) { ?>

                <?php if ($_GET['pesan'] == 'berhasil') { ?>
                    <div class="alert alert-success alert-dismissible fade show mt-3">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        <strong>Berhasil!</strong> Data siswa berhasil ditambahkan.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>

                <?php } elseif ($_GET['pesan'] == 'edit_berhasil') { ?>
                    <div class="alert alert-success alert-dismissible fade show mt-3">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        <strong>Berhasil!</strong> Data siswa berhasil diperbarui.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>

                <?php } elseif ($_GET['pesan'] == 'hapus_berhasil') { ?>
                    <div class="alert alert-success alert-dismissible fade show mt-3">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        <strong>Berhasil!</strong> Data siswa berhasil dihapus.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>

                <?php } elseif ($_GET['pesan'] == 'gagal_hapus') { ?>
                    <div class="alert alert-danger alert-dismissible fade show mt-3">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <strong>Gagal!</strong> Data siswa gagal dihapus. Data mungkin masih digunakan oleh tabel lain.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php } ?>

            <?php } ?>

            <?php if ($pesan != "") { ?>
                <div class="alert alert-danger alert-dismissible fade show mt-3">
                    <?= htmlspecialchars($pesan, ENT_QUOTES, 'UTF-8'); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php } ?>

            <!-- FORM EDIT -->
            <?php if ($dataEdit !== null) { ?>
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Edit Data Siswa</h5>
                    </div>

                    <div class="card-body">
                        <form method="POST" action="siswa.php?edit=<?= (int)$dataEdit['id']; ?>">
                            <input type="hidden" name="id" value="<?= (int)$dataEdit['id']; ?>">

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">NISN</label>
                                    <input
                                        type="text"
                                        name="nisn"
                                        class="form-control"
                                        value="<?= htmlspecialchars($dataEdit['nisn'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                        minlength="10"
                                        maxlength="10"
                                        pattern="[0-9]{10}"
                                        inputmode="numeric"
                                        oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)"
                                        title="NISN harus tepat 10 digit angka"
                                        required
                                    >
                                    <div class="form-text">NISN harus tepat 10 digit angka.</div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">NIS</label>
                                    <input
                                        type="text"
                                        name="nip"
                                        class="form-control"
                                        value="<?= htmlspecialchars($dataEdit['nip'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                        minlength="10"
                                        maxlength="10"
                                        pattern="[0-9]{10}"
                                        inputmode="numeric"
                                        oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)"
                                        title="NIS harus tepat 10 digit angka"
                                        required
                                    >
                                    <div class="form-text">NIS harus tepat 10 digit angka.</div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Nama Siswa</label>
                                    <input
                                        type="text"
                                        name="nama"
                                        class="form-control"
                                        value="<?= htmlspecialchars($dataEdit['nama'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                        required
                                    >
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Jenis Kelamin</label>
                                    <select name="jenis_kelamin" class="form-select" required>
                                        <option value="L" <?= ($dataEdit['jenis_kelamin'] ?? '') == 'L' ? 'selected' : ''; ?>>L</option>
                                        <option value="P" <?= ($dataEdit['jenis_kelamin'] ?? '') == 'P' ? 'selected' : ''; ?>>P</option>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tanggal Lahir</label>
                                    <input
                                        type="date"
                                        name="tanggal_lahir"
                                        class="form-control"
                                        value="<?= htmlspecialchars($dataEdit['tanggal_lahir'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                        required
                                    >
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Status Siswa</label>
                                    <select name="status_aktif" class="form-select" required>
                                        <option value="1" <?= (int)($dataEdit['status_aktif'] ?? 0) === 1 ? 'selected' : ''; ?>>Aktif</option>
                                        <option value="0" <?= (int)($dataEdit['status_aktif'] ?? 0) === 0 ? 'selected' : ''; ?>>Tidak Aktif</option>
                                    </select>
                                </div>
                            </div>

                            <button type="submit" name="update" class="btn btn-primary">Simpan Perubahan</button>
                            <a href="siswa.php" class="btn btn-secondary">Batal</a>
                        </form>
                    </div>
                </div>
            <?php } ?>

            <!-- TABEL SISWA -->
            <?php if ($dataEdit === null) { ?>
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="tabelSiswa" class="table align-middle">
                                <thead>
                                    <tr class="table-primary">
                                        <th>No</th>
                                        <th>NISN</th>
                                        <th>NIS</th>
                                        <th>Nama Siswa</th>
                                        <th>Jenis Kelamin</th>
                                        <th>Tanggal Lahir</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php
                                    $no = 1;
                                    while ($siswa = mysqli_fetch_assoc($query)) {
                                    ?>
                                        <tr>
                                            <td><?= $no++; ?></td>
                                            <td><?= htmlspecialchars($siswa['nisn'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?= htmlspecialchars($siswa['nip'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><strong><?= htmlspecialchars($siswa['nama'] ?? '', ENT_QUOTES, 'UTF-8'); ?></strong></td>
                                            <td><?= htmlspecialchars($siswa['jenis_kelamin'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?= htmlspecialchars($siswa['tanggal_lahir'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td>
                                                <?php if ((int)($siswa['status_aktif'] ?? 0) === 1) { ?>
                                                    <span class="badge bg-success">Aktif</span>
                                                <?php } else { ?>
                                                    <span class="badge bg-secondary">Tidak Aktif</span>
                                                <?php } ?>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-1">
                                                    <a href="siswa.php?edit=<?= (int)$siswa['id']; ?>" class="btn btn-warning btn-sm">Edit</a>

                                                    <form method="POST" action="hapus_siswa.php" onsubmit="return confirm('Yakin ingin menghapus data siswa ini beserta riwayat pelanggarannya?');">
                                                        <input type="hidden" name="id" value="<?= (int)$siswa['id']; ?>">
                                                        <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php } ?>

                                    <?php if (mysqli_num_rows($query) === 0) { ?>
                                        <tr>
                                            <td colspan="8" class="text-center">Belum ada data siswa.</td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php } ?>

        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function () {
    if ($('#tabelSiswa').length) {
        $('#tabelSiswa').DataTable({
            pageLength: 10,
            lengthMenu: [5, 10, 25, 50, 100],
            ordering: true,
            searching: true,
            language: {
                search: "Cari:",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                infoEmpty: "Tidak ada data",
                zeroRecords: "Data tidak ditemukan",
                emptyTable: "Belum ada data siswa",
                paginate: {
                    first: "Pertama",
                    last: "Terakhir",
                    next: "Berikutnya",
                    previous: "Sebelumnya"
                }
            }
        });
    }
});
</script>

</body>
</html>
