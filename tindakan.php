
<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

$role = strtolower(trim($_SESSION['role'] ?? ''));

if (!in_array($role, ['admin', 'guru'])) {
    exit("Anda tidak memiliki akses");
}

include "config/koneksi.php";

$pesan = "";
$error = "";

/* Proses perubahan hanya untuk Admin */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if ($role !== 'admin') {
        http_response_code(403);
        exit("Anda tidak memiliki akses");
    }

    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $tindakan = trim($_POST['tindakan'] ?? '');
    $status = trim($_POST['status'] ?? '');

    $daftarStatus = [
        'Belum Diproses',
        'Diproses',
        'Selesai'
    ];

    if (!$id || $tindakan === '' ||
        !in_array($status, $daftarStatus, true)) {

        $error = "Data tindakan tidak valid.";

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

                header("Location: tindakan.php?berhasil=1");
                exit;
            }

            $error = "Gagal memperbarui tindakan.";
            mysqli_stmt_close($stmt);

        } else {
            $error = "Terjadi kesalahan saat menyiapkan query.";
        }
    }
}

if (isset($_GET['berhasil'])) {
    $pesan = "Tindakan berhasil diperbarui.";
}

$cari = trim($_GET['cari'] ?? '');

if ($cari !== '') {
    $sql = "SELECT id, nama_siswa, nama_kelas,
                   nama_pelanggaran, nama_guru,
                   tanggal, poin, tindakan, status
            FROM t_pelanggaran_siswa
            WHERE nama_siswa LIKE ?
               OR nama_kelas LIKE ?
               OR nama_pelanggaran LIKE ?
               OR nama_guru LIKE ?
            ORDER BY tanggal DESC, id DESC";

    $stmt = mysqli_prepare($koneksi, $sql);
    $kata = "%".$cari."%";

    mysqli_stmt_bind_param(
        $stmt,
        "ssss",
        $kata,
        $kata,
        $kata,
        $kata
    );

    mysqli_stmt_execute($stmt);
    $data = mysqli_stmt_get_result($stmt);

} else {
    $data = mysqli_query(
        $koneksi,
        "SELECT id, nama_siswa, nama_kelas,
                nama_pelanggaran, nama_guru,
                tanggal, poin, tindakan, status
         FROM t_pelanggaran_siswa
         ORDER BY tanggal DESC, id DESC"
    );
}

if (!$data) {
    exit("Gagal mengambil data tindakan.");
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Tindakan Pelanggaran</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">
</head>

<body>

<div class="container-fluid">
    <div class="row">

        <aside class="col-md-3 col-lg-2 bg-dark min-vh-100 p-3">

            <h4 class="text-white mb-4">Sistem Pelanggaran</h4>

            <ul class="nav nav-pills flex-column">

                <li class="nav-item mb-2">
                    <a href="dashboard.php" class="nav-link text-white">
                        Dashboard
                    </a>
                </li>

                <?php if ($role === 'admin'): ?>

                    <li class="nav-item mb-2">
                        <a href="siswa.php" class="nav-link text-white">
                            Data Siswa
                        </a>
                    </li>

                    <li class="nav-item mb-2">
                        <a href="guru.php" class="nav-link text-white">
                            Data Guru
                        </a>
                    </li>

                    <li class="nav-item mb-2">
                        <a href="kelas.php" class="nav-link text-white">
                            Kelas
                        </a>
                    </li>

                    <li class="nav-item mb-2">
                        <a href="tahun_ajaran.php" class="nav-link text-white">
                            Tahun Ajaran
                        </a>
                    </li>

                    <li class="nav-item mb-2">
                        <a href="penempatan_siswa.php" class="nav-link text-white">
                            Penempatan Siswa
                        </a>
                    </li>

                    <li class="nav-item mb-2">
                        <a href="wali_kelas.php" class="nav-link text-white">
                            Wali Kelas
                        </a>
                    </li>

                    <li class="nav-item mb-2">
                        <a href="kategori_pelanggaran.php" class="nav-link text-white">
                            Kategori Pelanggaran
                        </a>
                    </li>

                    <li class="nav-item mb-2">
                        <a href="jenis_pelanggaran.php" class="nav-link text-white">
                            Jenis Pelanggaran
                        </a>
                    </li>

                    <li class="nav-item mb-2">
                        <a href="laporan.php" class="nav-link text-white">
                            Laporan
                        </a>
                    </li>

                <?php endif; ?>

                <li class="nav-item mb-2">
                    <a href="catatan_pelanggaran.php" class="nav-link text-white">
                        Catatan Pelanggaran
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a href="tindakan.php" class="nav-link active">
                        Tindakan
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a href="riwayat.php" class="nav-link text-white">
                        Riwayat
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a href="rekap_poin.php" class="nav-link text-white">
                        Rekap Poin
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a href="abautme.php" class="nav-link text-white">
                        About Me
                    </a>
                </li>

                <hr class="text-secondary">

                <li class="nav-item">
                    <a href="logout.php" class="nav-link text-danger">
                        Logout
                    </a>
                </li>

            </ul>
        </aside>

        <main class="col-md-9 col-lg-10 p-4">

            <h2>Tindakan Pelanggaran</h2>

            <p>
                Selamat datang,
                <strong><?= htmlspecialchars($_SESSION['name'] ?? 'Pengguna'); ?></strong>
            </p>

            <?php if ($pesan !== ''): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($pesan); ?>
                </div>
            <?php endif; ?>

            <?php if ($error !== ''): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div class="card shadow-sm">
                <div class="card-body">

                    <form method="GET" class="row g-2 mb-4">

                        <div class="col-md-6">
                            <input
                                type="text"
                                name="cari"
                                class="form-control"
                                placeholder="Cari nama siswa atau pelanggaran"
                                value="<?= htmlspecialchars($cari); ?>">
                        </div>

                        <div class="col-auto">
                            <button class="btn btn-primary" type="submit">
                                Cari
                            </button>

                            <a href="tindakan.php" class="btn btn-secondary">
                                Reset
                            </a>
                        </div>

                    </form>

                    <div class="table-responsive">

                        <table class="table table-bordered table-striped table-hover">

                            <thead class="table-dark">
                                <tr>
                                    <th>No.</th>
                                    <th>Nama Siswa</th>
                                    <th>Kelas</th>
                                    <th>Pelanggaran</th>
                                    <th>Guru</th>
                                    <th>Tanggal</th>
                                    <th>Poin</th>
                                    <th>Tindakan</th>
                                    <th>Status</th>
                                    <?php if ($role === 'admin'): ?>
                                        <th>Aksi</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>

                            <tbody>

                            <?php if (mysqli_num_rows($data) > 0): ?>

                                <?php $no = 1; ?>

                                <?php while ($row = mysqli_fetch_assoc($data)): ?>

                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td><?= htmlspecialchars($row['nama_siswa'] ?? ''); ?></td>
                                        <td><?= htmlspecialchars($row['nama_kelas'] ?? ''); ?></td>
                                        <td><?= htmlspecialchars($row['nama_pelanggaran'] ?? ''); ?></td>
                                        <td><?= htmlspecialchars($row['nama_guru'] ?? ''); ?></td>
                                        <td><?= htmlspecialchars($row['tanggal'] ?? ''); ?></td>
                                        <td><?= (int)$row['poin']; ?></td>

                                        <td>
                                            <?= htmlspecialchars($row['tindakan'] ?? ''); ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($row['status'] ?? ''); ?>
                                        </td>

                                        <?php if ($role === 'admin'): ?>
                                            <td>
                                                <button
                                                    type="button"
                                                    class="btn btn-warning btn-sm"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#edit<?= (int)$row['id']; ?>">
                                                    Edit
                                                </button>
                                            </td>
                                        <?php endif; ?>
                                    </tr>

                                    <?php if ($role === 'admin'): ?>

                                        <div class="modal fade"
                                             id="edit<?= (int)$row['id']; ?>"
                                             tabindex="-1"
                                             aria-hidden="true">

                                            <div class="modal-dialog">
                                                <div class="modal-content">

                                                    <form method="POST">

                                                        <div class="modal-header">
                                                            <h5 class="modal-title">
                                                                Edit Tindakan Pelanggaran
                                                            </h5>

                                                            <button type="button"
                                                                    class="btn-close"
                                                                    data-bs-dismiss="modal"
                                                                    aria-label="Tutup">
                                                            </button>
                                                        </div>

                                                        <div class="modal-body">

                                                            <input
                                                                type="hidden"
                                                                name="id"
                                                                value="<?= (int)$row['id']; ?>">

                                                            <div class="mb-3">
                                                                <label class="form-label">
                                                                    Nama Siswa
                                                                </label>

                                                                <input
                                                                    type="text"
                                                                    class="form-control"
                                                                    value="<?= htmlspecialchars($row['nama_siswa'] ?? ''); ?>"
                                                                    readonly>
                                                            </div>

                                                            <div class="mb-3">
                                                                <label class="form-label">
                                                                    Tindakan
                                                                </label>

                                                                <input
                                                                    type="text"
                                                                    name="tindakan"
                                                                    class="form-control"
                                                                    maxlength="255"
                                                                    required
                                                                    value="<?= htmlspecialchars($row['tindakan'] ?? ''); ?>">
                                                            </div>

                                                            <div class="mb-3">
                                                                <label class="form-label">
                                                                    Status
                                                                </label>

                                                                <select
                                                                    name="status"
                                                                    class="form-select"
                                                                    required>

                                                                    <?php
                                                                    $statusSekarang = $row['status'] ?? '';
                                                                    $opsiStatus = [
                                                                        'Belum Diproses',
                                                                        'Diproses',
                                                                        'Selesai'
                                                                    ];
                                                                    ?>

                                                                    <?php foreach ($opsiStatus as $opsi): ?>
                                                                        <option
                                                                            value="<?= htmlspecialchars($opsi); ?>"
                                                                            <?= $statusSekarang === $opsi ? 'selected' : ''; ?>>
                                                                            <?= htmlspecialchars($opsi); ?>
                                                                        </option>
                                                                    <?php endforeach; ?>

                                                                </select>
                                                            </div>

                                                        </div>

                                                        <div class="modal-footer">
                                                            <button
                                                                type="button"
                                                                class="btn btn-secondary"
                                                                data-bs-dismiss="modal">
                                                                Batal
                                                            </button>

                                                            <button
                                                                type="submit"
                                                                class="btn btn-primary">
                                                                Simpan Perubahan
                                                            </button>
                                                        </div>

                                                    </form>

                                                </div>
                                            </div>

                                        </div>

                                    <?php endif; ?>

                                <?php endwhile; ?>

                            <?php else: ?>

                                <tr>
                                    <td
                                        colspan="<?= $role === 'admin' ? 10 : 9; ?>"
                                        class="text-center">
                                        Data tindakan belum tersedia.
                                    </td>
                                </tr>

                            <?php endif; ?>

                            </tbody>
                        </table>
                    </div>

                </div>
            </div>

        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
