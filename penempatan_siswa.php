<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

if (strtolower(trim($_SESSION['role'] ?? '')) !== 'admin') {
    exit("Anda tidak memiliki akses");
}

require_once __DIR__ . "/config/koneksi.php";

function aman($nilai) {
    return htmlspecialchars((string)($nilai ?? ''), ENT_QUOTES, 'UTF-8');
}

// PROSES HAPUS PENEMPATAN
if (isset($_GET['hapus'])) {
    $id_hapus = (int) $_GET['hapus'];

    $hapus = mysqli_query(
        $koneksi,
        "DELETE FROM t_kelas_siswa WHERE id = $id_hapus"
    );

    if ($hapus && mysqli_affected_rows($koneksi) > 0) {
        header("Location: penempatan_siswa.php?pesan=hapus");
        exit;
    }

    header("Location: penempatan_siswa.php?pesan=gagal_hapus");
    exit;
}

// AMBIL DATA PENEMPATAN DARI DATABASE
$query_penempatan = mysqli_query($koneksi, "
    SELECT
        ks.id,
        ks.siswa_id,
        s.nisn,
        s.nama AS nama_siswa,
        ks.kelas_id,
        k.nama AS nama_kelas,
        ks.tahun_ajaran_id,
        ta.nama AS nama_tahun_ajaran,
        ks.tanggal_mulai,
        ks.tanggal_selesai,
        ks.status_aktif
    FROM t_kelas_siswa ks
    LEFT JOIN t_siswa s
        ON ks.siswa_id = s.id
    LEFT JOIN t_kelas k
        ON ks.kelas_id = k.id
    LEFT JOIN t_tahun_ajaran ta
        ON ks.tahun_ajaran_id = ta.id
    ORDER BY ks.id DESC
");

if (!$query_penempatan) {
    die("Gagal mengambil data penempatan: " . mysqli_error($koneksi));
}

// SIMPAN HASIL QUERY
$data_penempatan = [];

while ($row = mysqli_fetch_assoc($query_penempatan)) {
    $data_penempatan[] = $row;
}

// NOTIFIKASI
$pesan_url = $_GET['pesan'] ?? '';

$notifikasi = [
    'berhasil' => [
        'success',
        'Penempatan siswa berhasil ditambahkan.'
    ],
    'edit' => [
        'success',
        'Penempatan siswa berhasil diperbarui.'
    ],
    'hapus' => [
        'success',
        'Penempatan siswa berhasil dihapus.'
    ],
    'gagal_hapus' => [
        'danger',
        'Data penempatan siswa gagal dihapus atau tidak ditemukan.'
    ]
];

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

    <title>Penempatan Siswa - Sistem Informasi Pelanggaran Siswa</title>

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

            <!-- PROFIL ADMIN -->
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

                        <div class="fw-semibold">
                            <?= $nama; ?>
                        </div>

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
                    <a href="siswa.php" class="nav-link text-white rounded-2 py-2 px-3">
                        Data Siswa
                    </a>
                </li>

                <li class="nav-item">
                    <a href="guru.php" class="nav-link text-white rounded-2 py-2 px-3">
                        Data Guru
                    </a>
                </li>

                <li class="nav-item">
                    <a href="kelas.php" class="nav-link text-white rounded-2 py-2 px-3">
                        Kelas
                    </a>
                </li>

                <li class="nav-item">
                    <a href="tahun_ajaran.php" class="nav-link text-white rounded-2 py-2 px-3">
                        Tahun Ajaran
                    </a>
                </li>

                <li class="nav-item">
                    <a href="penempatan_siswa.php" class="nav-link active rounded-2 py-2 px-3" aria-current="page">
                        Penempatan Siswa
                    </a>
                </li>

                <li class="nav-item">
                    <a href="wali_kelas.php" class="nav-link text-white rounded-2 py-2 px-3">
                        Wali Kelas
                    </a>
                </li>

                <li class="nav-item">
                    <a href="kategori_pelanggaran.php" class="nav-link text-white rounded-2 py-2 px-3">
                        Kategori Pelanggaran
                    </a>
                </li>

                <li class="nav-item">
                    <a href="jenis_pelanggaran.php" class="nav-link text-white rounded-2 py-2 px-3">
                        Jenis Pelanggaran
                    </a>
                </li>

                <li class="nav-item">
                    <a href="laporan.php" class="nav-link text-white rounded-2 py-2 px-3">
                        Laporan
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

        <!-- KONTEN UTAMA -->
        <main class="col-12 col-md-9 col-lg-10 p-3 p-lg-5">

            <!-- JUDUL HALAMAN -->
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-4 border-bottom">

                <div>

                    <h2 class="fw-bold mb-2">
                        Penempatan Siswa
                    </h2>

                    <p class="text-secondary mb-2">
                        Kelola data penempatan siswa pada Sistem Informasi Pelanggaran Siswa.
                    </p>

                    <a href="dashboard.php" class="btn btn-secondary btn-sm rounded-1">
                        Kembali ke Dashboard
                    </a>

                </div>

                <a href="tambah_penempatan_siswa.php" class="btn btn-primary rounded-2 px-4">
                    + Tambah Penempatan Siswa
                </a>

            </div>

            <!-- NOTIFIKASI -->
            <?php if (isset($notifikasi[$pesan_url])): ?>

                <div
                    class="alert alert-<?= aman($notifikasi[$pesan_url][0]); ?> alert-dismissible fade show"
                    role="alert"
                >

                    <?= aman($notifikasi[$pesan_url][1]); ?>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"
                        aria-label="Tutup"
                    ></button>

                </div>

            <?php endif; ?>

            <!-- TABEL PENEMPATAN SISWA -->
            <div class="card border-0 shadow-sm rounded-3">

                <div class="card-body p-3 p-lg-4">

                    <div class="table-responsive">

                        <table id="tabelPenempatan" class="table table-hover align-middle">

                            <thead>
                                <tr class="table-primary">
                                    <th>No</th>
                                    <th>NISN</th>
                                    <th>Nama Siswa</th>
                                    <th>Kelas</th>
                                    <th>Tahun Ajaran</th>
                                    <th>Tanggal Mulai</th>
                                    <th>Tanggal Selesai</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>

                            <tbody>

                                <?php $no = 1; ?>

                                <?php foreach ($data_penempatan as $row): ?>

                                    <tr>

                                        <td>
                                            <?= $no++; ?>
                                        </td>

                                        <td>
                                            <?= aman($row['nisn'] ?? '-'); ?>
                                        </td>

                                        <td>
                                            <strong>
                                                <?= aman($row['nama_siswa'] ?? '-'); ?>
                                            </strong>
                                        </td>

                                        <td>
                                            <?= aman($row['nama_kelas'] ?? '-'); ?>
                                        </td>

                                        <td>
                                            <?= aman($row['nama_tahun_ajaran'] ?? '-'); ?>
                                        </td>

                                        <td>
                                            <?= aman($row['tanggal_mulai'] ?? '-'); ?>
                                        </td>

                                        <td>
                                            <?= aman($row['tanggal_selesai'] ?? '-'); ?>
                                        </td>

                                        <td>

                                            <?php if ((int)($row['status_aktif'] ?? 0) === 1): ?>

                                                <span class="badge bg-success">
                                                    Aktif
                                                </span>

                                            <?php else: ?>

                                                <span class="badge bg-secondary">
                                                    Tidak Aktif
                                                </span>

                                            <?php endif; ?>

                                        </td>

                                        <td>
                                            <div class="d-flex gap-1">

                                                <a
                                                    href="edit_penempatan_siswa.php?id=<?= (int)$row['id']; ?>"
                                                    class="btn btn-warning btn-sm"
                                                >
                                                    Edit
                                                </a>

                                                <a
                                                    href="hapus_penempatan_siswa.php?id=<?= (int)$row['id']; ?>"
                                                    class="btn btn-danger btn-sm"
                                                    onclick="return confirm('Yakin ingin menghapus data penempatan siswa ini?')"
                                                >
                                                    Hapus
                                                </a>

                                            </div>
                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

            <!-- FOOTER -->
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

    $('#tabelPenempatan').DataTable({

        pageLength: 10,

        lengthMenu: [5, 10, 25, 50, 100],

        ordering: true,

        searching: true,

        language: {
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            infoEmpty: "Tidak ada data",
            infoFiltered: "(difilter dari _MAX_ total data)",
            zeroRecords: "Data tidak ditemukan",
            emptyTable: "Belum ada data penempatan siswa",

            paginate: {
                first: "Pertama",
                last: "Terakhir",
                next: "Berikutnya",
                previous: "Sebelumnya"
            }
        },

        columnDefs: [
            {
                targets: [0, 8],
                orderable: false,
                searchable: false
            }
        ]

    });

});
</script>

</body>
</html>