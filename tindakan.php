<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

$role = strtolower(trim($_SESSION['role'] ?? ''));

if (!in_array($role, ['admin', 'guru'], true)) {
    exit("Anda tidak memiliki akses");
}

require_once __DIR__ . "/config/koneksi.php";

$pesan = "";
$dataEdit = null;

/* PROSES UPDATE TINDAKAN */
if (isset($_POST['update'])) {

    // Hanya Guru yang boleh memberikan tindakan
    if ($role !== 'guru') {
        http_response_code(403);
        exit("Anda tidak memiliki akses");
    }

    $id = (int) ($_POST['id'] ?? 0);
    $tindakan = trim($_POST['tindakan'] ?? '');
    $status = trim($_POST['status'] ?? '');

    $daftarStatus = [
        'Belum Diproses',
        'Diproses',
        'Selesai'
    ];

    if ($id <= 0) {
        $pesan = "ID data tidak valid.";
    } elseif ($tindakan === '') {
        $pesan = "Tindakan wajib diisi.";
    } elseif (!in_array($status, $daftarStatus, true)) {
        $pesan = "Status tidak valid.";
    } else {
        $stmt = mysqli_prepare(
            $koneksi,
            "UPDATE t_pelanggaran_siswa
             SET tindakan = ?, status = ?
             WHERE id = ?"
        );

        if ($stmt) {
            mysqli_stmt_bind_param(
                $stmt,
                "ssi",
                $tindakan,
                $status,
                $id
            );

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                header("Location: tindakan.php?pesan=edit_berhasil");
                exit;
            } else {
                $pesan = "Gagal memperbarui tindakan.";
                mysqli_stmt_close($stmt);
            }
        } else {
            $pesan = "Query gagal: " . mysqli_error($koneksi);
        }
    }
}

/* AMBIL DATA EDIT */
if ($role === 'guru' && isset($_GET['edit']) && is_numeric($_GET['edit'])) {

    $idEdit = (int) $_GET['edit'];

    $stmtEdit = mysqli_prepare(
        $koneksi,
        "SELECT * FROM t_pelanggaran_siswa WHERE id = ?"
    );

    if ($stmtEdit) {
        mysqli_stmt_bind_param($stmtEdit, "i", $idEdit);
        mysqli_stmt_execute($stmtEdit);

        $resultEdit = mysqli_stmt_get_result($stmtEdit);
        $dataEdit = mysqli_fetch_assoc($resultEdit);

        mysqli_stmt_close($stmtEdit);
    }

    if (!$dataEdit) {
        $pesan = "Data tindakan tidak ditemukan.";
    }
}

/* AMBIL DATA TINDAKAN */
$query = mysqli_query(
    $koneksi,
    "SELECT id, nama_siswa, nama_kelas,
            nama_pelanggaran, nama_guru,
            tanggal, poin, tindakan, status
     FROM t_pelanggaran_siswa
     ORDER BY tanggal DESC, id DESC"
);

if (!$query) {
    die("Gagal mengambil data tindakan: " . mysqli_error($koneksi));
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

    <title>Tindakan - Sistem Informasi Pelanggaran Siswa</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/2.3.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
</head>

<body class="bg-light overflow-hidden">

<div class="container-fluid vh-100 overflow-hidden">
    <div class="row h-100 flex-nowrap">

        <!-- SIDEBAR -->
        <aside class="col-12 col-md-3 col-lg-2 bg-dark vh-100 overflow-auto position-sticky top-0 p-3">

            <div class="text-white mb-4 pt-3 pb-3 border-bottom border-secondary">
                <h5 class="fw-bold mb-2">
                    <i class="bi bi-shield-check me-2"></i>
                    Sistem Pelanggaran
                </h5>

                <small class="text-secondary">Panel <?= strtoupper($role); ?></small>
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
                        <small class="text-secondary"><?= strtoupper($role); ?></small>
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

                <?php if ($role === 'admin'): ?>

                    <li class="nav-item">
                        <a href="siswa.php" class="nav-link text-white rounded-2 py-2 px-3">Data Siswa</a>
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

                <?php endif; ?>

                <li class="nav-item">
                    <a href="catatan_pelanggaran.php" class="nav-link text-white rounded-2 py-2 px-3">
                        Catatan Pelanggaran
                    </a>
                </li>

                <li class="nav-item">
                    <a href="tindakan.php" class="nav-link active rounded-2 py-2 px-3" aria-current="page">
                        Tindakan
                    </a>
                </li>

                <li class="nav-item">
                    <a href="riwayat.php" class="nav-link text-white rounded-2 py-2 px-3">Riwayat</a>
                </li>

                <li class="nav-item">
                    <a href="rekap_poin.php" class="nav-link text-white rounded-2 py-2 px-3">Rekap Poin</a>
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

        <!-- KONTEN -->
        <main class="col-12 col-md-9 col-lg-10 vh-100 overflow-auto p-3 p-lg-5">

            <!-- JUDUL HALAMAN -->
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-4 border-bottom">

                <div>
                    <h2 class="fw-bold mb-2">
                        <?= $dataEdit !== null ? 'Beri Tindakan' : 'Tindakan Pelanggaran'; ?>
                    </h2>

                    <p class="text-secondary mb-2">
                        <?= $dataEdit !== null
                            ? 'Silakan isi atau perbarui tindakan dan status pelanggaran siswa.'
                            : 'Kelola tindakan dan status pelanggaran siswa.'; ?>
                    </p>

                    <?php if ($dataEdit === null): ?>
                        <!-- TOMBOL HANYA MUNCUL DI HALAMAN UTAMA -->
                        <a href="dashboard.php" class="btn btn-secondary btn-sm">
                            <i class="bi bi-arrow-left me-1"></i>
                            Kembali ke Dashboard
                        </a>
                    <?php endif; ?>

                </div>

            </div>

            <!-- NOTIFIKASI -->
            <?php if (isset($_GET['pesan']) && $_GET['pesan'] === 'edit_berhasil'): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    Tindakan berhasil diperbarui.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($pesan !== ''): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($pesan, ENT_QUOTES, 'UTF-8'); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- FORM BERI TINDAKAN KHUSUS GURU -->
            <?php if ($dataEdit !== null && $role === 'guru'): ?>

                <div class="row justify-content-center">
                    <div class="col-12 col-xl-10">

                        <div class="card border-0 shadow-sm rounded-3">

                            <div class="card-header bg-white p-3">
                                <h5 class="text-primary fw-bold mb-1">Formulir Tindakan</h5>
                                <small class="text-secondary">
                                    Isi tindakan dan status pelanggaran siswa.
                                </small>
                            </div>

                            <div class="card-body p-4">

                                <form method="POST" action="tindakan.php?edit=<?= (int) $dataEdit['id']; ?>">

                                    <input type="hidden" name="id" value="<?= (int) $dataEdit['id']; ?>">

                                    <div class="row g-3">

                                        <div class="col-md-6">
                                            <label class="form-label">Nama Siswa</label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                value="<?= htmlspecialchars($dataEdit['nama_siswa'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                readonly
                                            >
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Kelas</label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                value="<?= htmlspecialchars($dataEdit['nama_kelas'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                readonly
                                            >
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Pelanggaran</label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                value="<?= htmlspecialchars($dataEdit['nama_pelanggaran'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                readonly
                                            >
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Guru</label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                value="<?= htmlspecialchars($dataEdit['nama_guru'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                readonly
                                            >
                                        </div>

                                        <div class="col-12">
                                            <label for="tindakan" class="form-label">
                                                Tindakan <span class="text-danger">*</span>
                                            </label>

                                            <textarea
                                                name="tindakan"
                                                id="tindakan"
                                                class="form-control"
                                                rows="3"
                                                maxlength="255"
                                                required
                                            ><?= htmlspecialchars($dataEdit['tindakan'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                                        </div>

                                        <div class="col-md-6">
                                            <label for="status" class="form-label">
                                                Status <span class="text-danger">*</span>
                                            </label>

                                            <select name="status" id="status" class="form-select" required>
                                                <option value="">-- Pilih Status --</option>

                                                <?php
                                                $opsiStatus = [
                                                    'Belum Diproses',
                                                    'Diproses',
                                                    'Selesai'
                                                ];

                                                foreach ($opsiStatus as $opsi):
                                                ?>
                                                    <option
                                                        value="<?= htmlspecialchars($opsi, ENT_QUOTES, 'UTF-8'); ?>"
                                                        <?= ($dataEdit['status'] ?? '') === $opsi ? 'selected' : ''; ?>
                                                    >
                                                        <?= htmlspecialchars($opsi, ENT_QUOTES, 'UTF-8'); ?>
                                                    </option>
                                                <?php endforeach; ?>

                                            </select>
                                        </div>

                                    </div>

                                    <hr class="my-4">

                                    <div class="d-flex justify-content-end gap-2">

                                        <a href="tindakan.php" class="btn btn-secondary">
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

            <!-- TABEL TINDAKAN -->
            <?php if ($dataEdit === null): ?>

                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body p-3 p-lg-4">

                        <div class="table-responsive">

                            <table id="tabelTindakan" class="table table-hover align-middle">

                                <thead>
                                    <tr class="table-primary">
                                        <th>No</th>
                                        <th>Nama Siswa</th>
                                        <th>Kelas</th>
                                        <th>Pelanggaran</th>
                                        <th>Guru</th>
                                        <th>Tanggal</th>
                                        <th>Poin</th>
                                        <th>Tindakan</th>
                                        <th>Status</th>

                                        <?php if ($role === 'guru'): ?>
                                            <th>Aksi</th>
                                        <?php endif; ?>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php
                                    $no = 1;

                                    while ($row = mysqli_fetch_assoc($query)):
                                    ?>
                                        <tr>
                                            <td><?= $no++; ?></td>

                                            <td>
                                                <strong>
                                                    <?= htmlspecialchars($row['nama_siswa'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                                </strong>
                                            </td>

                                            <td>
                                                <?= htmlspecialchars($row['nama_kelas'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                            </td>

                                            <td>
                                                <?= htmlspecialchars($row['nama_pelanggaran'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                            </td>

                                            <td>
                                                <?= htmlspecialchars($row['nama_guru'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                            </td>

                                            <td>
                                                <?= htmlspecialchars($row['tanggal'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                            </td>

                                            <td>
                                                <?= (int) ($row['poin'] ?? 0); ?>
                                            </td>

                                            <td>
                                                <?= htmlspecialchars($row['tindakan'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                            </td>

                                            <td>
                                                <?php if (($row['status'] ?? '') === 'Selesai'): ?>
                                                    <span class="badge bg-success">Selesai</span>
                                                <?php elseif (($row['status'] ?? '') === 'Diproses'): ?>
                                                    <span class="badge bg-warning text-dark">Diproses</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">
                                                        <?= htmlspecialchars($row['status'] ?? 'Belum Diproses', ENT_QUOTES, 'UTF-8'); ?>
                                                    </span>
                                                <?php endif; ?>
                                            </td>

                                            <?php if ($role === 'guru'): ?>
                                                <td>
                                                    <a
                                                        href="tindakan.php?edit=<?= (int) $row['id']; ?>"
                                                        class="btn btn-primary btn-sm"
                                                    >
                                                        Beri Tindakan
                                                    </a>
                                                </td>
                                            <?php endif; ?>

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
    if ($('#tabelTindakan').length) {
        $('#tabelTindakan').DataTable({
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
                emptyTable: "Belum ada data tindakan",
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