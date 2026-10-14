```php
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

$file = __DIR__ . "/data_penempatan.json";

if (!file_exists($file)) {
    file_put_contents($file, "[]", LOCK_EX);
}

$data_penempatan = json_decode(file_get_contents($file), true);

if (!is_array($data_penempatan)) {
    $data_penempatan = [];
}

$error = "";
$edit_id = (int)($_GET['edit'] ?? 0);

$form = [
    'siswa_id' => '',
    'kelas_id' => '',
    'tahun_ajaran_id' => '',
    'tanggal_mulai' => date('Y-m-d'),
    'tanggal_selesai' => '',
    'status_aktif' => 1
];

/* AMBIL DATA UNTUK PILIHAN FORM */
$siswa = mysqli_query(
    $koneksi,
    "SELECT id, nisn, nama FROM t_siswa ORDER BY nama ASC"
);

$kelas = mysqli_query(
    $koneksi,
    "SELECT id, nama FROM t_kelas ORDER BY nama ASC"
);

$tahun = mysqli_query(
    $koneksi,
    "SELECT id, nama FROM t_tahun_ajaran ORDER BY id DESC"
);

if (!$siswa || !$kelas || !$tahun) {
    die("Gagal mengambil data pilihan: " . mysqli_error($koneksi));
}

/* CARI DATA JIKA MODE EDIT */
if ($edit_id > 0) {
    $ditemukan = false;

    foreach ($data_penempatan as $row) {
        if ((int)($row['id'] ?? 0) === $edit_id) {
            $form = array_merge($form, $row);
            $ditemukan = true;
            break;
        }
    }

    if (!$ditemukan) {
        header("Location: penempatan_siswa.php");
        exit;
    }
}

/* SIMPAN DATA */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $edit_id = (int)($_POST['id'] ?? 0);

    $siswa_id = (int)($_POST['siswa_id'] ?? 0);
    $kelas_id = (int)($_POST['kelas_id'] ?? 0);
    $tahun_ajaran_id = (int)($_POST['tahun_ajaran_id'] ?? 0);
    $tanggal_mulai = trim($_POST['tanggal_mulai'] ?? '');
    $tanggal_selesai = trim($_POST['tanggal_selesai'] ?? '');
    $status_aktif = (int)($_POST['status_aktif'] ?? 1);

    if (
        $siswa_id <= 0 ||
        $kelas_id <= 0 ||
        $tahun_ajaran_id <= 0 ||
        $tanggal_mulai === '' ||
        $tanggal_selesai === ''
    ) {
        $error = "Semua kolom wajib diisi.";
    } else {
        /* Pastikan pilihan berasal dari database */
        $cek_siswa = mysqli_query(
            $koneksi,
            "SELECT id, nisn, nama FROM t_siswa WHERE id = $siswa_id"
        );

        $cek_kelas = mysqli_query(
            $koneksi,
            "SELECT id, nama FROM t_kelas WHERE id = $kelas_id"
        );

        $cek_tahun = mysqli_query(
            $koneksi,
            "SELECT id, nama FROM t_tahun_ajaran WHERE id = $tahun_ajaran_id"
        );

        if (
            !$cek_siswa || !$cek_kelas || !$cek_tahun ||
            mysqli_num_rows($cek_siswa) === 0 ||
            mysqli_num_rows($cek_kelas) === 0 ||
            mysqli_num_rows($cek_tahun) === 0
        ) {
            $error = "Data siswa, kelas, atau tahun ajaran tidak ditemukan.";
        } else {
            $data_siswa = mysqli_fetch_assoc($cek_siswa);
            $data_kelas = mysqli_fetch_assoc($cek_kelas);
            $data_tahun = mysqli_fetch_assoc($cek_tahun);

            $id = $edit_id;

            if ($id <= 0) {
                $id = 1;

                foreach ($data_penempatan as $row) {
                    $id = max($id, (int)($row['id'] ?? 0) + 1);
                }
            }

            $data_baru = [
                'id' => $id,
                'siswa_id' => $siswa_id,
                'nisn' => $data_siswa['nisn'],
                'nama_siswa' => $data_siswa['nama'],
                'kelas_id' => $kelas_id,
                'nama_kelas' => $data_kelas['nama'],
                'tahun_ajaran_id' => $tahun_ajaran_id,
                'nama_tahun_ajaran' => $data_tahun['nama'],
                'tanggal_mulai' => $tanggal_mulai,
                'tanggal_selesai' => $tanggal_selesai,
                'status_aktif' => $status_aktif
            ];

            if ($edit_id > 0) {
                foreach ($data_penempatan as $index => $row) {
                    if ((int)($row['id'] ?? 0) === $edit_id) {
                        $data_penempatan[$index] = $data_baru;
                        break;
                    }
                }
            } else {
                $data_penempatan[] = $data_baru;
            }

            $tersimpan = file_put_contents(
                $file,
                json_encode(
                    array_values($data_penempatan),
                    JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
                ),
                LOCK_EX
            );

            if ($tersimpan !== false) {
                header(
                    "Location: penempatan_siswa.php?pesan=" .
                    ($edit_id > 0 ? "edit" : "berhasil")
                );
                exit;
            }

            $error = "Gagal menyimpan data. Pastikan folder proyek bisa ditulisi.";
        }
    }

    $form = [
        'siswa_id' => $siswa_id,
        'kelas_id' => $kelas_id,
        'tahun_ajaran_id' => $tahun_ajaran_id,
        'tanggal_mulai' => $tanggal_mulai,
        'tanggal_selesai' => $tanggal_selesai,
        'status_aktif' => $status_aktif
    ];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><?= $edit_id > 0 ? 'Edit' : 'Tambah'; ?> Penempatan Siswa</title>

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
                    <a href="penempatan_siswa.php"
                       class="nav-link active bg-primary text-white rounded">
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
                <h2 class="fw-bold mb-1">
                    <?= $edit_id > 0 ? 'Edit' : 'Tambah'; ?> Penempatan Siswa
                </h2>

                <p class="text-secondary mb-2">
                    Tempatkan siswa ke kelas pada tahun ajaran tertentu.
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
                          action="tambah_penempatan_siswa.php<?= $edit_id > 0 ? '?edit=' . $edit_id : ''; ?>">

                        <input type="hidden" name="id"
                               value="<?= (int)$edit_id; ?>">

                        <div class="mb-3">
                            <label for="siswa_id" class="form-label">
                                Nama Siswa
                            </label>

                            <select name="siswa_id" id="siswa_id"
                                    class="form-select" required>
                                <option value="">-- Pilih Siswa --</option>

                                <?php
                                mysqli_data_seek($siswa, 0);
                                while ($row = mysqli_fetch_assoc($siswa)) {
                                ?>
                                    <option value="<?= (int)$row['id']; ?>"
                                        <?= (int)($form['siswa_id'] ?? 0) === (int)$row['id'] ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($row['nisn']); ?> -
                                        <?= htmlspecialchars($row['nama']); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="kelas_id" class="form-label">
                                Kelas
                            </label>

                            <select name="kelas_id" id="kelas_id"
                                    class="form-select" required>
                                <option value="">-- Pilih Kelas --</option>

                                <?php
                                mysqli_data_seek($kelas, 0);
                                while ($row = mysqli_fetch_assoc($kelas)) {
                                ?>
                                    <option value="<?= (int)$row['id']; ?>"
                                        <?= (int)($form['kelas_id'] ?? 0) === (int)$row['id'] ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($row['nama']); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="tahun_ajaran_id" class="form-label">
                                Tahun Ajaran
                            </label>

                            <select name="tahun_ajaran_id" id="tahun_ajaran_id"
                                    class="form-select" required>
                                <option value="">-- Pilih Tahun Ajaran --</option>

                                <?php
                                mysqli_data_seek($tahun, 0);
                                while ($row = mysqli_fetch_assoc($tahun)) {
                                ?>
                                    <option value="<?= (int)$row['id']; ?>"
                                        <?= (int)($form['tahun_ajaran_id'] ?? 0) === (int)$row['id'] ? 'selected' : ''; ?>>
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
                                   value="<?= htmlspecialchars($form['tanggal_mulai'] ?? ''); ?>"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label for="tanggal_selesai" class="form-label">
                                Tanggal Selesai
                            </label>

                            <input type="date" name="tanggal_selesai"
                                   id="tanggal_selesai" class="form-control"
                                   value="<?= htmlspecialchars($form['tanggal_selesai'] ?? ''); ?>"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label for="status_aktif" class="form-label">
                                Status
                            </label>

                            <select name="status_aktif" id="status_aktif"
                                    class="form-select" required>
                                <option value="1"
                                    <?= (int)($form['status_aktif'] ?? 1) === 1 ? 'selected' : ''; ?>>
                                    Aktif
                                </option>

                                <option value="0"
                                    <?= (int)($form['status_aktif'] ?? 1) === 0 ? 'selected' : ''; ?>>
                                    Tidak Aktif
                                </option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <?= $edit_id > 0 ? 'Simpan Perubahan' : 'Simpan Penempatan'; ?>
                        </button>

                        <a href="penempatan_siswa.php" class="btn btn-secondary">
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