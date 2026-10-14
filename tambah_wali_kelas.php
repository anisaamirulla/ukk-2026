
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

$error = "";
$edit_id = (int)($_GET['edit'] ?? 0);

$form = [
    'tahun_ajaran_id' => '',
    'kelas_id' => '',
    'guru_id' => '',
    'tanggal_mulai' => '',
    'tanggal_selesai' => '',
    'status_aktif' => 1
];

/* Ambil pilihan dari database */
$tahun = mysqli_query(
    $koneksi,
    "SELECT id, nama FROM t_tahun_ajaran ORDER BY id DESC"
);

$kelas = mysqli_query(
    $koneksi,
    "SELECT id, nama FROM t_kelas ORDER BY nama ASC"
);

$guru = mysqli_query(
    $koneksi,
    "SELECT id, nip, nama FROM t_guru ORDER BY nama ASC"
);

if (!$tahun || !$kelas || !$guru) {
    die("Gagal mengambil data pilihan: " . mysqli_error($koneksi));
}

/* Ambil data jika mode edit */
if ($edit_id > 0) {
    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT tahun_ajaran_id, kelas_id, guru_id,
                tanggal_mulai, tanggal_selesai, status_aktif
         FROM t_wali_kelas WHERE id = ?"
    );

    mysqli_stmt_bind_param($stmt, "i", $edit_id);
    mysqli_stmt_execute($stmt);
    $hasil = mysqli_stmt_get_result($stmt);
    $row_edit = mysqli_fetch_assoc($hasil);
    mysqli_stmt_close($stmt);

    if (!$row_edit) {
        header("Location: wali_kelas.php");
        exit;
    }

    $form = $row_edit;
}

/* Simpan data */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $edit_id = (int)($_POST['id'] ?? 0);

    $tahun_ajaran_id = (int)($_POST['tahun_ajaran_id'] ?? 0);
    $kelas_id = (int)($_POST['kelas_id'] ?? 0);
    $guru_id = (int)($_POST['guru_id'] ?? 0);
    $tanggal_mulai = trim($_POST['tanggal_mulai'] ?? '');
    $tanggal_selesai = trim($_POST['tanggal_selesai'] ?? '');
    $status_aktif = (int)($_POST['status_aktif'] ?? 1);

    $form = [
        'tahun_ajaran_id' => $tahun_ajaran_id,
        'kelas_id' => $kelas_id,
        'guru_id' => $guru_id,
        'tanggal_mulai' => $tanggal_mulai,
        'tanggal_selesai' => $tanggal_selesai,
        'status_aktif' => $status_aktif
    ];

    if (
        $tahun_ajaran_id <= 0 ||
        $kelas_id <= 0 ||
        $guru_id <= 0 ||
        $tanggal_mulai === '' ||
        $tanggal_selesai === ''
    ) {
        $error = "Semua kolom wajib diisi.";
    } elseif ($tanggal_selesai < $tanggal_mulai) {
        $error = "Tanggal selesai tidak boleh sebelum tanggal mulai.";
    } else {
        if ($edit_id > 0) {
            $stmt = mysqli_prepare(
                $koneksi,
                "UPDATE t_wali_kelas
                 SET tahun_ajaran_id = ?,
                     kelas_id = ?,
                     guru_id = ?,
                     tanggal_mulai = ?,
                     tanggal_selesai = ?,
                     status_aktif = ?
                 WHERE id = ?"
            );

            if (!$stmt) {
                $error = "Gagal menyiapkan query: " . mysqli_error($koneksi);
            } else {
                mysqli_stmt_bind_param(
                    $stmt,
                    "iiissii",
                    $tahun_ajaran_id,
                    $kelas_id,
                    $guru_id,
                    $tanggal_mulai,
                    $tanggal_selesai,
                    $status_aktif,
                    $edit_id
                );

                if (mysqli_stmt_execute($stmt)) {
                    mysqli_stmt_close($stmt);
                    header("Location: wali_kelas.php?pesan=berhasil");
                    exit;
                }

                $error = "Gagal memperbarui data: " . mysqli_stmt_error($stmt);
                mysqli_stmt_close($stmt);
            }
        } else {
            $stmt = mysqli_prepare(
                $koneksi,
                "INSERT INTO t_wali_kelas
                 (tahun_ajaran_id, kelas_id, guru_id,
                  tanggal_mulai, tanggal_selesai, status_aktif)
                 VALUES (?, ?, ?, ?, ?, ?)"
            );

            if (!$stmt) {
                $error = "Gagal menyiapkan query: " . mysqli_error($koneksi);
            } else {
                mysqli_stmt_bind_param(
                    $stmt,
                    "iiissi",
                    $tahun_ajaran_id,
                    $kelas_id,
                    $guru_id,
                    $tanggal_mulai,
                    $tanggal_selesai,
                    $status_aktif
                );

                if (mysqli_stmt_execute($stmt)) {
                    mysqli_stmt_close($stmt);
                    header("Location: wali_kelas.php?pesan=berhasil");
                    exit;
                }

                $error = "Gagal menyimpan data: " . mysqli_stmt_error($stmt);
                mysqli_stmt_close($stmt);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><?= $edit_id > 0 ? 'Edit' : 'Tambah'; ?> Wali Kelas</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
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
                    <a href="tahun_ajaran.php" class="nav-link text-white rounded">
                        Tahun Ajaran
                    </a>
                </li>

                <li class="nav-item">
                    <a href="penempatan_siswa.php" class="nav-link text-white rounded">
                        Penempatan Siswa
                    </a>
                </li>

                <li class="nav-item">
                    <a href="wali_kelas.php"
                       class="nav-link active bg-primary text-white rounded">
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
                <h2 class="fw-bold mb-1">
                    <?= $edit_id > 0 ? 'Edit' : 'Tambah'; ?> Wali Kelas
                </h2>

                <p class="text-secondary mb-2">
                    Kelola penugasan wali kelas berdasarkan tahun ajaran.
                </p>

                <a href="dashboard.php" class="btn btn-secondary btn-sm">
                    Kembali ke Dashboard
                </a>
            </div>

            <?php if ($error !== '') { ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($error); ?>
                </div>
            <?php } ?>

            <!-- FORM -->
            <div class="card border-0 shadow-sm">
                <div class="card-body">

                    <form method="POST"
                          action="tambah_wali_kelas.php<?= $edit_id > 0 ? '?edit=' . $edit_id : ''; ?>">

                        <input type="hidden" name="id"
                               value="<?= $edit_id; ?>">

                        <div class="mb-3">
                            <label for="tahun_ajaran_id" class="form-label">
                                Tahun Ajaran
                            </label>

                            <select name="tahun_ajaran_id" id="tahun_ajaran_id"
                                    class="form-select" required>
                                <option value="">-- Pilih Tahun Ajaran --</option>

                                <?php while ($row = mysqli_fetch_assoc($tahun)) { ?>
                                    <option value="<?= (int)$row['id']; ?>"
                                        <?= (int)$form['tahun_ajaran_id'] === (int)$row['id'] ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($row['nama']); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="kelas_id" class="form-label">Kelas</label>

                            <select name="kelas_id" id="kelas_id"
                                    class="form-select" required>
                                <option value="">-- Pilih Kelas --</option>

                                <?php while ($row = mysqli_fetch_assoc($kelas)) { ?>
                                    <option value="<?= (int)$row['id']; ?>"
                                        <?= (int)$form['kelas_id'] === (int)$row['id'] ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($row['nama']); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="guru_id" class="form-label">Nama Guru</label>

                            <select name="guru_id" id="guru_id"
                                    class="form-select" required>
                                <option value="">-- Pilih Guru --</option>

                                <?php while ($row = mysqli_fetch_assoc($guru)) { ?>
                                    <option value="<?= (int)$row['id']; ?>"
                                        <?= (int)$form['guru_id'] === (int)$row['id'] ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($row['nip']); ?> -
                                        <?= htmlspecialchars($row['nama']); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="tanggal_mulai" class="form-label">
                                Tanggal Mulai
                            </label>

                            <input type="date" name="tanggal_mulai"
                                   id="tanggal_mulai" class="form-control"
                                   value="<?= htmlspecialchars($form['tanggal_mulai']); ?>"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label for="tanggal_selesai" class="form-label">
                                Tanggal Selesai
                            </label>

                            <input type="date" name="tanggal_selesai"
                                   id="tanggal_selesai" class="form-control"
                                   value="<?= htmlspecialchars($form['tanggal_selesai']); ?>"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label for="status_aktif" class="form-label">
                                Status
                            </label>

                            <select name="status_aktif" id="status_aktif"
                                    class="form-select" required>
                                <option value="1"
                                    <?= (int)$form['status_aktif'] === 1 ? 'selected' : ''; ?>>
                                    Aktif
                                </option>

                                <option value="0"
                                    <?= (int)$form['status_aktif'] === 0 ? 'selected' : ''; ?>>
                                    Tidak Aktif
                                </option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <?= $edit_id > 0 ? 'Simpan Perubahan' : 'Simpan Wali Kelas'; ?>
                        </button>

                        <a href="wali_kelas.php" class="btn btn-secondary">
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