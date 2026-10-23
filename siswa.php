
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
    $nis = trim($_POST['nis'] ?? '');
    $nama_siswa = trim($_POST['nama'] ?? '');
    $jenis_kelamin = $_POST['jenis_kelamin'] ?? '';
    $tanggal_lahir = $_POST['tanggal_lahir'] ?? '';
    $alamat = trim($_POST['alamat'] ?? '');
    $status_aktif = $_POST['status_aktif'] ?? '';

    if ($id <= 0) {
        $pesan = "ID siswa tidak valid.";
    } elseif (!preg_match('/^[0-9]{10}$/', $nisn)) {
        $pesan = "NISN wajib tepat 10 digit angka.";
    } elseif (!preg_match('/^[0-9]{10}$/', $nis)) {
        $pesan = "NIS wajib tepat 10 digit angka.";
    } elseif ($nama_siswa === '') {
        $pesan = "Nama siswa wajib diisi.";
    } elseif (!in_array($jenis_kelamin, ['L', 'P'], true)) {
        $pesan = "Pilih jenis kelamin yang valid.";
    } elseif ($tanggal_lahir === '') {
        $pesan = "Tanggal lahir wajib diisi.";
    } elseif (!in_array($status_aktif, ['Aktif', 'Tidak Aktif'], true)) {
        $pesan = "Status siswa tidak valid.";
    } else {
        $stmt = mysqli_prepare(
            $koneksi,
            "UPDATE t_siswa
             SET nisn = ?, nis = ?, nama = ?,
                 jenis_kelamin = ?, tanggal_lahir = ?,
                 alamat = ?, status_aktif = ?
             WHERE id = ?"
        );

        if ($stmt) {
            mysqli_stmt_bind_param(
                $stmt,
                "sssssssi",
                $nisn,
                $nis,
                $nama_siswa,
                $jenis_kelamin,
                $tanggal_lahir,
                $alamat,
                $status_aktif,
                $id
            );

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                header("Location: siswa.php?pesan=edit_berhasil");
                exit;
            } else {
                $pesan = "Gagal mengubah data siswa. Pastikan NIS dan NISN tidak digunakan siswa lain.";
                mysqli_stmt_close($stmt);
            }
        } else {
            $pesan = "Query gagal: " . mysqli_error($koneksi);
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

    if ($stmtEdit) {
        mysqli_stmt_bind_param($stmtEdit, "i", $idEdit);
        mysqli_stmt_execute($stmtEdit);

        $resultEdit = mysqli_stmt_get_result($stmtEdit);
        $dataEdit = mysqli_fetch_assoc($resultEdit);

        mysqli_stmt_close($stmtEdit);
    }

    if (!$dataEdit) {
        $pesan = "Data siswa tidak ditemukan.";
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

$nama = htmlspecialchars(
    $_SESSION['name'] ?? 'Pengguna',
    ENT_QUOTES,
    'UTF-8'
);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Data Siswa - Sistem Informasi Pelanggaran Siswa</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/2.3.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container-fluid">
    <div class="row">

        <!-- SIDEBAR -->
        <aside class="col-12 col-md-3 col-lg-2 bg-dark min-vh-100 p-3">

            <div class="text-white mb-4 pt-3 pb-3 border-bottom border-secondary">
                <h5 class="fw-bold mb-2">
                    <i class="bi bi-shield-check me-2"></i>
                    Sistem Pelanggaran
                </h5>

                <small class="text-secondary">Panel Admin</small>
            </div>

            <div class="bg-secondary bg-opacity-25 rounded-3 p-3 mb-4">
                <div class="d-flex align-items-center gap-3">
                    <img
                        src="logo.png"
                        alt="Logo Aplikasi"
                        width="44"
                        height="44"
                        class="rounded-circle bg-white p-1"
                    >

                    <div class="text-white text-break">
                        <div class="fw-semibold"><?= $nama; ?></div>
                        <small class="text-secondary">ADMIN</small>
                    </div>
                </div>
            </div>

            <div class="text-secondary small fw-semibold mb-3">
                MENU UTAMA
            </div>

            <ul class="nav nav-pills flex-column gap-2">
                <li class="nav-item">
                    <a href="dashboard.php" class="nav-link text-white rounded-2 py-2 px-3">
                        Dashboard
                    </a>
                </li>

                <li class="nav-item">
                    <a href="siswa.php" class="nav-link active rounded-2 py-2 px-3" aria-current="page">
                        Data Siswa
                    </a>
                </li>

                <li class="nav-item">
                    <a href="guru.php" class="nav-link text-white rounded-2 py-2 px-3">Data Guru</a>
                </li>

                <li class="nav-item">
                    <a href="kelas.php" class="nav-link text-white rounded-2 py-2 px-3">Kelas</a>
                </li>

                <li class="nav-item">
                    <a href="tahun_ajaran.php" class="nav-link text-white rounded-2 py-2 px-3">Tahun Ajaran</a>
                </li>

                <li class="nav-item">
                    <a href="penempatan_siswa.php" class="nav-link text-white rounded-2 py-2 px-3">Penempatan Siswa</a>
                </li>

                <li class="nav-item">
                    <a href="wali_kelas.php" class="nav-link text-white rounded-2 py-2 px-3">Wali Kelas</a>
                </li>

                <li class="nav-item">
                    <a href="kategori_pelanggaran.php" class="nav-link text-white rounded-2 py-2 px-3">Kategori Pelanggaran</a>
                </li>

                <li class="nav-item">
                    <a href="jenis_pelanggaran.php" class="nav-link text-white rounded-2 py-2 px-3">Jenis Pelanggaran</a>
                </li>

                <li class="nav-item">
                    <a href="laporan.php" class="nav-link text-white rounded-2 py-2 px-3">Laporan</a>
                </li>

                <li class="nav-item">
                    <a href="abautme.php" class="nav-link text-white rounded-2 py-2 px-3">About Me</a>
                </li>
            </ul>

            <hr class="border-secondary my-4">

            <a href="logout.php" class="nav-link text-danger rounded-2 py-2 px-3">
                <i class="bi bi-box-arrow-left me-2"></i>
                Logout
            </a>

        </aside>

        <!-- KONTEN DATA SISWA -->
        <main class="col-12 col-md-9 col-lg-10 p-3 p-lg-5">

            <!-- JUDUL HALAMAN -->
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-4 border-bottom">
                <div>
                    <h2 class="fw-bold mb-2">
                        <?= $dataEdit !== null ? 'Edit Data Siswa' : 'Data Siswa'; ?>
                    </h2>

                    <p class="text-secondary mb-2">
                        <?= $dataEdit !== null
                            ? 'Silakan perbarui data siswa dengan lengkap.'
                            : 'Kelola data siswa pada Sistem Informasi Pelanggaran Siswa.'; ?>
                    </p>
                </div>

                <?php if ($dataEdit === null): ?>
                    <a href="tambah_siswa.php" class="btn btn-primary rounded-2 px-4">
                        + Tambah Siswa
                    </a>
                <?php endif; ?>
            </div>

            <!-- NOTIFIKASI -->
            <?php if (isset($_GET['pesan'])): ?>
                <?php if ($_GET['pesan'] === 'berhasil'): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        Data siswa berhasil ditambahkan.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php elseif ($_GET['pesan'] === 'edit_berhasil'): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        Data siswa berhasil diperbarui.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php elseif ($_GET['pesan'] === 'hapus_berhasil'): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        Data siswa berhasil dihapus.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php elseif ($_GET['pesan'] === 'gagal_hapus'): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        Data siswa gagal dihapus. Periksa hubungan data pada tabel terkait.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($pesan !== ''): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($pesan, ENT_QUOTES, 'UTF-8'); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- FORM EDIT SISWA -->
            <?php if ($dataEdit !== null): ?>

                <div class="row justify-content-center">
                    <div class="col-12 col-xl-10">

                        <div class="card border-0 shadow-sm rounded-3">

                            <div class="card-header bg-white p-3">
                                <h5 class="text-primary fw-bold mb-1">Formulir Siswa</h5>
                                <small class="text-secondary">
                                    Perbarui informasi siswa pada kolom yang tersedia.
                                </small>
                            </div>

                            <div class="card-body p-4">

                                <form method="POST" action="siswa.php?edit=<?= (int) $dataEdit['id']; ?>">

                                    <input type="hidden" name="id" value="<?= (int) $dataEdit['id']; ?>">

                                    <div class="row g-3">

                                        <div class="col-md-6">
                                            <label for="nisn" class="form-label">
                                                NISN <span class="text-danger">*</span>
                                            </label>

                                            <input
                                                type="text"
                                                name="nisn"
                                                id="nisn"
                                                class="form-control"
                                                placeholder="Masukkan 10 digit NISN"
                                                value="<?= htmlspecialchars($dataEdit['nisn'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                minlength="10"
                                                maxlength="10"
                                                pattern="[0-9]{10}"
                                                inputmode="numeric"
                                                oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,10)"
                                                required
                                            >
                                        </div>

                                        <div class="col-md-6">
                                            <label for="nis" class="form-label">
                                                NIS <span class="text-danger">*</span>
                                            </label>

                                            <input
                                                type="text"
                                                name="nis"
                                                id="nis"
                                                class="form-control"
                                                placeholder="Masukkan 10 digit NIS"
                                                value="<?= htmlspecialchars($dataEdit['nis'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                minlength="10"
                                                maxlength="10"
                                                pattern="[0-9]{10}"
                                                inputmode="numeric"
                                                oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,10)"
                                                required
                                            >
                                        </div>

                                        <div class="col-12">
                                            <label for="nama" class="form-label">
                                                Nama Siswa <span class="text-danger">*</span>
                                            </label>

                                            <input
                                                type="text"
                                                name="nama"
                                                id="nama"
                                                class="form-control"
                                                placeholder="Masukkan nama lengkap siswa"
                                                value="<?= htmlspecialchars($dataEdit['nama'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                required
                                            >
                                        </div>

                                        <div class="col-md-6">
                                            <label for="jenis_kelamin" class="form-label">
                                                Jenis Kelamin <span class="text-danger">*</span>
                                            </label>

                                            <select name="jenis_kelamin" id="jenis_kelamin" class="form-select" required>
                                                <option value="">-- Pilih Jenis Kelamin --</option>
                                                <option value="L" <?= ($dataEdit['jenis_kelamin'] ?? '') === 'L' ? 'selected' : ''; ?>>L</option>
                                                <option value="P" <?= ($dataEdit['jenis_kelamin'] ?? '') === 'P' ? 'selected' : ''; ?>>P</option>
                                            </select>
                                        </div>

                                        <div class="col-md-6">
                                            <label for="tanggal_lahir" class="form-label">
                                                Tanggal Lahir <span class="text-danger">*</span>
                                            </label>

                                            <input
                                                type="date"
                                                name="tanggal_lahir"
                                                id="tanggal_lahir"
                                                class="form-control"
                                                value="<?= htmlspecialchars($dataEdit['tanggal_lahir'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                required
                                            >
                                        </div>

                                        <div class="col-12">
                                            <label for="alamat" class="form-label">Alamat</label>

                                            <textarea
                                                name="alamat"
                                                id="alamat"
                                                class="form-control"
                                                rows="3"
                                                placeholder="Masukkan alamat siswa"
                                            ><?= htmlspecialchars($dataEdit['alamat'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                                        </div>

                                        <div class="col-md-6">
                                            <label for="status_aktif" class="form-label">
                                                Status <span class="text-danger">*</span>
                                            </label>

                                            <select name="status_aktif" id="status_aktif" class="form-select" required>
                                                <option value="Aktif" <?= ($dataEdit['status_aktif'] ?? '') === 'Aktif' ? 'selected' : ''; ?>>
                                                    Aktif
                                                </option>

                                                <option value="Tidak Aktif" <?= ($dataEdit['status_aktif'] ?? '') === 'Tidak Aktif' ? 'selected' : ''; ?>>
                                                    Tidak Aktif
                                                </option>
                                            </select>
                                        </div>

                                    </div>

                                    <hr class="my-4">

                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="siswa.php" class="btn btn-secondary">
                                            Batal
                                        </a>

                                        <button type="submit" name="update" class="btn btn-primary">
                                            Simpan Perubahan
                                        </button>
                                    </div>

                                    <p class="text-secondary small mt-3 mb-0">
                                        <span class="text-danger">*</span>
                                        Kolom bertanda bintang wajib diisi.
                                    </p>

                                </form>

                            </div>
                        </div>

                    </div>
                </div>

            <?php endif; ?>

            <!-- TABEL SISWA -->
            <?php if ($dataEdit === null): ?>

                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body p-3 p-lg-4">

                        <div class="table-responsive">
                            <table id="tabelSiswa" class="table table-hover align-middle">

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

                                    while ($siswa = mysqli_fetch_assoc($query)):
                                    ?>
                                        <tr>
                                            <td><?= $no++; ?></td>

                                            <td>
                                                <?= htmlspecialchars($siswa['nisn'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                            </td>

                                            <td>
                                                <?= htmlspecialchars($siswa['nis'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                            </td>

                                            <td>
                                                <strong>
                                                    <?= htmlspecialchars($siswa['nama'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                                </strong>
                                            </td>

                                            <td>
                                                <?= htmlspecialchars($siswa['jenis_kelamin'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                            </td>

                                            <td>
                                                <?= htmlspecialchars($siswa['tanggal_lahir'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                            </td>

                                            <td>
                                                <?php if (($siswa['status_aktif'] ?? '') === 'Aktif' || (string) ($siswa['status_aktif'] ?? '') === '1'): ?>
                                                    <span class="badge bg-success">Aktif</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Tidak Aktif</span>
                                                <?php endif; ?>
                                            </td>

                                            <td>
                                                <div class="d-flex gap-1">

                                                    <a
                                                        href="siswa.php?edit=<?= (int) $siswa['id']; ?>"
                                                        class="btn btn-warning btn-sm"
                                                    >
                                                        Edit
                                                    </a>

                                                    <form
                                                        method="POST"
                                                        action="siswa.php"
                                                        onsubmit="return confirm('Yakin ingin menghapus data siswa ini beserta riwayat pelanggarannya?');"
                                                    >
                                                        <input
                                                            type="hidden"
                                                            name="id"
                                                            value="<?= (int) $siswa['id']; ?>"
                                                        >

                                                        <button
                                                            type="submit"
                                                            name="hapus"
                                                            class="btn btn-danger btn-sm"
                                                        >
                                                            Hapus
                                                        </button>
                                                    </form>

                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>

                            </table>
                        </div>

                    </div>
                </div>

            <?php endif; ?>

            <div class="text-secondary small mt-5 pt-3 border-top">
                Sistem Informasi Pelanggaran Siswa
            </div>

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
