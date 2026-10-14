
<?php

session_start();

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

if ($_SESSION['role'] != 'admin') {
    echo "Anda tidak memiliki akses";
    exit;
}

require_once __DIR__ . "/config/koneksi.php";

$file = __DIR__ . "/data_penempatan.json";

if (!file_exists($file)) {
    file_put_contents($file, "[]", LOCK_EX);
}

$data_penempatan = json_decode(file_get_contents($file), true);

if (!is_array($data_penempatan)) {
    $data_penempatan = [];
}

/* HAPUS DATA */
if (isset($_GET['hapus'])) {
    $id = (int) $_GET['hapus'];

    $data_penempatan = array_values(array_filter(
        $data_penempatan,
        function ($row) use ($id) {
            return (int)($row['id'] ?? 0) !== $id;
        }
    ));

    file_put_contents(
        $file,
        json_encode(
            $data_penempatan,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
        ),
        LOCK_EX
    );

    header("Location: penempatan_siswa.php?pesan=hapus");
    exit;
}

/* AMBIL DATA SISWA, KELAS, DAN TAHUN AJARAN */
$query_siswa = mysqli_query(
    $koneksi,
    "SELECT id, nisn, nama FROM t_siswa ORDER BY nama ASC"
);

$query_kelas = mysqli_query(
    $koneksi,
    "SELECT id, nama FROM t_kelas ORDER BY nama ASC"
);

$query_tahun = mysqli_query(
    $koneksi,
    "SELECT id, nama FROM t_tahun_ajaran ORDER BY id DESC"
);

if (!$query_siswa || !$query_kelas || !$query_tahun) {
    die("Gagal mengambil data: " . mysqli_error($koneksi));
}

$siswa_map = [];

while ($row = mysqli_fetch_assoc($query_siswa)) {
    $siswa_map[(int)$row['id']] = $row;
}

$kelas_map = [];

while ($row = mysqli_fetch_assoc($query_kelas)) {
    $kelas_map[(int)$row['id']] = $row['nama'];
}

$tahun_map = [];

while ($row = mysqli_fetch_assoc($query_tahun)) {
    $tahun_map[(int)$row['id']] = $row['nama'];
}

/* URUTKAN DATA TERBARU */
usort($data_penempatan, function ($a, $b) {
    return (int)($b['id'] ?? 0) <=> (int)($a['id'] ?? 0);
});

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Penempatan Siswa</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

</head>

<body class="bg-light">

<div class="container-fluid">

    <div class="row">

        <!-- SIDEBAR -->
        <div class="col-md-3 col-lg-2 bg-dark min-vh-100 p-3">

            <h4 class="text-white mb-4">
                Sistem Pelanggaran
            </h4>

            <ul class="nav nav-pills flex-column">

                <li class="nav-item mb-2">
                    <a href="dashboard.php"
                       class="nav-link text-white">
                        Dashboard
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a href="siswa.php"
                       class="nav-link text-white">
                        Data Siswa
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a href="guru.php"
                       class="nav-link text-white">
                        Data Guru
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a href="kelas.php"
                       class="nav-link text-white">
                        Kelas
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a href="tahun_ajaran.php"
                       class="nav-link text-white">
                        Tahun Ajaran
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a href="penempatan_siswa.php"
                       class="nav-link active">
                        Penempatan Siswa
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a href="wali_kelas.php"
                       class="nav-link text-white">
                        Wali Kelas
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a href="kategori_pelanggaran.php"
                       class="nav-link text-white">
                        Kategori Pelanggaran
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a href="jenis_pelanggaran.php"
                       class="nav-link text-white">
                        Jenis Pelanggaran
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a href="laporan.php"
                       class="nav-link text-white">
                        Laporan
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a href="abautme.php"
                       class="nav-link text-white">
                        Abaut Me
                    </a>
                </li>

                <hr class="text-secondary">

                <li class="nav-item">
                    <a href="logout.php"
                       class="nav-link text-danger">
                        Logout
                    </a>
                </li>

            </ul>

        </div>

        <!-- KONTEN -->
        <main class="col-md-9 col-lg-10 p-4">

            <div class="d-flex justify-content-between align-items-center mb-4">

                <div>

                    <h2 class="fw-bold mb-1">
                        Penempatan Siswa
                    </h2>

                    <p class="text-secondary mb-2">
                        Kelola penempatan siswa ke kelas
                    </p>

                    <a href="dashboard.php"
                       class="btn btn-secondary btn-sm">
                        Kembali ke Dashboard
                    </a>

                </div>

                <a href="tambah_penempatan_siswa.php"
                   class="btn btn-primary">
                    + Tambah Penempatan
                </a>

            </div>

            <!-- PESAN BERHASIL -->
            <?php if (isset($_GET['pesan'])) { ?>

                <?php if ($_GET['pesan'] === 'berhasil') { ?>
                    <div class="alert alert-success">
                        Penempatan siswa berhasil disimpan.
                    </div>
                <?php } ?>

                <?php if ($_GET['pesan'] === 'hapus') { ?>
                    <div class="alert alert-success">
                        Penempatan siswa berhasil dihapus.
                    </div>
                <?php } ?>

                <?php if ($_GET['pesan'] === 'edit') { ?>
                    <div class="alert alert-success">
                        Penempatan siswa berhasil diperbarui.
                    </div>
                <?php } ?>

            <?php } ?>

            <!-- TABEL PENEMPATAN SISWA -->
            <div class="card border-0 shadow-sm">

                <div class="card-body">

                    <div class="table-responsive">

                        <table class="table align-middle">

                            <thead>
                                <tr>
                                    <th class="table-primary">No</th>
                                    <th class="table-primary">NISN</th>
                                    <th class="table-primary">Nama Siswa</th>
                                    <th class="table-primary">Kelas</th>
                                    <th class="table-primary">Tahun Ajaran</th>
                                    <th class="table-primary">Tanggal Mulai</th>
                                    <th class="table-primary">Tanggal Selesai</th>
                                    <th class="table-primary">Status</th>
                                    <th class="table-primary">Aksi</th>
                                </tr>
                            </thead>

                            <tbody>

                                <?php if (count($data_penempatan) > 0) { ?>

                                    <?php $no = 1; ?>

                                    <?php foreach ($data_penempatan as $row) { ?>

                                        <?php
                                        $sid = (int)($row['siswa_id'] ?? 0);
                                        $kid = (int)($row['kelas_id'] ?? 0);
                                        $tid = (int)($row['tahun_ajaran_id'] ?? 0);

                                        $s = $siswa_map[$sid] ?? null;
                                        ?>

                                        <tr>

                                            <td><?= $no++; ?></td>

                                            <td>
                                                <?= htmlspecialchars(
                                                    $s['nisn'] ?? ($row['nisn'] ?? '-')
                                                ); ?>
                                            </td>

                                            <td>
                                                <strong>
                                                    <?= htmlspecialchars(
                                                        $s['nama'] ?? ($row['nama_siswa'] ?? '-')
                                                    ); ?>
                                                </strong>
                                            </td>

                                            <td>
                                                <?= htmlspecialchars(
                                                    $kelas_map[$kid] ?? ($row['nama_kelas'] ?? '-')
                                                ); ?>
                                            </td>

                                            <td>
                                                <?= htmlspecialchars(
                                                    $tahun_map[$tid] ?? ($row['nama_tahun_ajaran'] ?? '-')
                                                ); ?>
                                            </td>

                                            <td>
                                                <?= htmlspecialchars(
                                                    $row['tanggal_mulai'] ?? '-'
                                                ); ?>
                                            </td>

                                            <td>
                                                <?= htmlspecialchars(
                                                    $row['tanggal_selesai'] ?? '-'
                                                ); ?>
                                            </td>

                                            <td>

                                                <?php if (($row['status_aktif'] ?? 1) == 1) { ?>

                                                    <span class="badge bg-success">
                                                        Aktif
                                                    </span>

                                                <?php } else { ?>

                                                    <span class="badge bg-secondary">
                                                        Tidak Aktif
                                                    </span>

                                                <?php } ?>

                                            </td>

                                            <td>

                                                <a
                                                    href="tambah_penempatan_siswa.php?edit=<?= (int)$row['id']; ?>"
                                                    class="btn btn-warning btn-sm">
                                                    Edit
                                                </a>

                                                <a
                                                    href="penempatan_siswa.php?hapus=<?= (int)$row['id']; ?>"
                                                    class="btn btn-danger btn-sm"
                                                    onclick="return confirm('Yakin ingin menghapus data penempatan ini?')">
                                                    Hapus
                                                </a>

                                            </td>

                                        </tr>

                                    <?php } ?>

                                <?php } else { ?>

                                    <tr>
                                        <td colspan="9" class="text-center">
                                            Belum ada data penempatan siswa.
                                        </td>
                                    </tr>

                                <?php } ?>

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </main>

    </div>

</div>

</body>

</html>
```