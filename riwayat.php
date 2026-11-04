
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

/* AMBIL DATA RIWAYAT */
$data = mysqli_query(
    $koneksi,
    "SELECT id, nama_siswa, nama_kelas,
            nama_pelanggaran, nama_guru,
            tanggal, keterangan, poin, tindakan, status
     FROM t_pelanggaran_siswa
     ORDER BY tanggal DESC, id DESC"
);

if (!$data) {
    die("Gagal mengambil data riwayat: " . mysqli_error($koneksi));
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

    <title>Riwayat - Sistem Informasi Pelanggaran Siswa</title>

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
                    <a href="tindakan.php" class="nav-link text-white rounded-2 py-2 px-3">
                        Tindakan
                    </a>
                </li>

                <li class="nav-item">
                    <a href="riwayat.php" class="nav-link active rounded-2 py-2 px-3" aria-current="page">
                        Riwayat
                    </a>
                </li>

                <li class="nav-item">
                    <a href="rekap_poin.php" class="nav-link text-white rounded-2 py-2 px-3">
                        Rekap Poin
                    </a>
                </li>

                <li class="nav-item">
                    <a href="abautme.php" class="nav-link text-white rounded-2 py-2 px-3">
                        About Me
                    </a>
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
                    <h2 class="fw-bold mb-2">Riwayat Pelanggaran Siswa</h2>

                    <p class="text-secondary mb-2">
                        Menampilkan seluruh riwayat pelanggaran siswa.
                    </p>

                    <a href="dashboard.php" class="btn btn-secondary btn-sm">
                        Kembali ke Dashboard
                    </a>
                </div>

            </div>

            <!-- TABEL RIWAYAT -->
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-3 p-lg-4">

                    <div class="table-responsive">

                        <table id="tabelRiwayat" class="table table-hover align-middle">

                            <thead>
                                <tr class="table-primary">
                                    <th>No.</th>
                                    <th>Nama Siswa</th>
                                    <th>Kelas</th>
                                    <th>Pelanggaran</th>
                                    <th>Guru</th>
                                    <th>Tanggal</th>
                                    <th>Keterangan</th>
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

                                while ($row = mysqli_fetch_assoc($data)):
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
                                            <?= htmlspecialchars($row['keterangan'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                        </td>

                                        <td>
                                            <span class="badge bg-danger">
                                                <?= (int) ($row['poin'] ?? 0); ?> poin
                                            </span>
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

                    <p class="text-secondary small mt-3 mb-0">
                        Total riwayat:
                        <strong><?= mysqli_num_rows($data); ?></strong> data
                    </p>

                </div>
            </div>

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
    $('#tabelRiwayat').DataTable({
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
            emptyTable: "Belum ada riwayat pelanggaran",
            paginate: {
                first: "Pertama",
                last: "Terakhir",
                next: "Berikutnya",
                previous: "Sebelumnya"
            }
        }
    });
});
</script>

</body>
</html>
