
<?php
session_start();
require_once __DIR__ . "/config/koneksi.php";

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    exit("Anda tidak memiliki akses");
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: jenis_pelanggaran.php");
    exit;
}

$id = (int) $_GET['id'];

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT * FROM t_pelanggaran WHERE id = ?"
);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$hasil = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($hasil);

if (!$data) {
    exit("Data jenis pelanggaran tidak ditemukan.");
}

$kategori = mysqli_query(
    $koneksi,
    "SELECT id, nama FROM t_pelanggaran_kategori
     WHERE status_aktif = 1 OR id = " . (int)$data['pelanggaran_kategori_id'] . "
     ORDER BY nama ASC"
);

if (!$kategori) {
    die("Gagal mengambil kategori: " . mysqli_error($koneksi));
}

$pesan = "";

if (isset($_POST['simpan'])) {
    $kode = trim($_POST['kode'] ?? '');
    $nama = trim($_POST['nama'] ?? '');
    $kategori_id = (int) ($_POST['pelanggaran_kategori_id'] ?? 0);
    $poin = filter_var($_POST['poin'] ?? '', FILTER_VALIDATE_INT);
    $deksripsi = trim($_POST['deksripsi'] ?? '');
    $status_aktif = filter_var(
        $_POST['status_aktif'] ?? '',
        FILTER_VALIDATE_INT
    );

    if ($kode == '' || $nama == '') {
        $pesan = "Kode dan nama pelanggaran wajib diisi.";
    } elseif ($poin === false || $poin < 0) {
        $pesan = "Poin harus berupa angka nol atau lebih.";
    } elseif (!in_array($status_aktif, [0, 1], true)) {
        $pesan = "Status tidak valid.";
    } else {
        $cek = mysqli_prepare(
            $koneksi,
            "SELECT id FROM t_pelanggaran_kategori WHERE id = ?"
        );
        mysqli_stmt_bind_param($cek, "i", $kategori_id);
        mysqli_stmt_execute($cek);
        $hasil_kategori = mysqli_stmt_get_result($cek);

        if (mysqli_num_rows($hasil_kategori) == 0) {
            $pesan = "Kategori pelanggaran tidak ditemukan.";
        } else {
            $update = mysqli_prepare(
                $koneksi,
                "UPDATE t_pelanggaran
                 SET kode = ?,
                     nama = ?,
                     pelanggaran_kategori_id = ?,
                     poin = ?,
                     deksripsi = ?,
                     status_aktif = ?
                 WHERE id = ?"
            );

            mysqli_stmt_bind_param(
                $update,
                "ssiisii",
                $kode,
                $nama,
                $kategori_id,
                $poin,
                $deksripsi,
                $status_aktif,
                $id
            );

            if (mysqli_stmt_execute($update)) {
                header("Location: jenis_pelanggaran.php?pesan=edit");
                exit;
            } else {
                $pesan = "Gagal memperbarui data. Pastikan kode tidak digunakan oleh data lain.";
            }
        }
    }

    $data['kode'] = $kode;
    $data['nama'] = $nama;
    $data['pelanggaran_kategori_id'] = $kategori_id;
    $data['poin'] = $_POST['poin'] ?? 0;
    $data['deksripsi'] = $deksripsi;
    $data['status_aktif'] = $status_aktif;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Jenis Pelanggaran</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>

<body class="bg-light">

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-9 col-lg-7">

            <div class="card border-0 shadow-sm">

                <div class="card-header bg-warning py-3">
                    <h4 class="mb-0">Edit Jenis Pelanggaran</h4>
                </div>

                <div class="card-body p-4">

                    <?php if ($pesan != ''): ?>
                        <div class="alert alert-danger">
                            <?= htmlspecialchars($pesan); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">

                        <div class="mb-3">
                            <label class="form-label" for="kode">Kode Pelanggaran</label>
                            <input type="text" name="kode" id="kode"
                                   class="form-control"
                                   value="<?= htmlspecialchars($data['kode'] ?? ''); ?>"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="nama">Nama Pelanggaran</label>
                            <input type="text" name="nama" id="nama"
                                   class="form-control"
                                   value="<?= htmlspecialchars($data['nama'] ?? ''); ?>"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="pelanggaran_kategori_id">
                                Kategori Pelanggaran
                            </label>

                            <select name="pelanggaran_kategori_id"
                                    id="pelanggaran_kategori_id"
                                    class="form-select" required>

                                <option value="">-- Pilih Kategori --</option>

                                <?php while ($k = mysqli_fetch_assoc($kategori)): ?>
                                    <option value="<?= (int)$k['id']; ?>"
                                        <?= (int)$data['pelanggaran_kategori_id'] == (int)$k['id'] ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($k['nama']); ?>
                                    </option>
                                <?php endwhile; ?>

                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="poin">Poin Pelanggaran</label>
                            <input type="number" name="poin" id="poin"
                                   class="form-control" min="0"
                                   value="<?= htmlspecialchars((string)($data['poin'] ?? 0)); ?>"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="deksripsi">Deskripsi</label>
                            <textarea name="deksripsi" id="deksripsi"
                                      class="form-control"
                                      rows="4"><?= htmlspecialchars($data['deksripsi'] ?? ''); ?></textarea>
                        </div>

                        <div class="mb-4">
                            <label class="form-label" for="status_aktif">Status</label>
                            <select name="status_aktif" id="status_aktif"
                                    class="form-select" required>
                                <option value="1"
                                    <?= ($data['status_aktif'] ?? 0) == 1 ? 'selected' : ''; ?>>
                                    Aktif
                                </option>
                                <option value="0"
                                    <?= ($data['status_aktif'] ?? 0) == 0 ? 'selected' : ''; ?>>
                                    Tidak Aktif
                                </option>
                            </select>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" name="simpan"
                                    class="btn btn-primary">
                                Simpan Perubahan
                            </button>

                            <a href="jenis_pelanggaran.php"
                               class="btn btn-secondary">
                                Kembali
                            </a>
                        </div>

                    </form>

                </div>
            </div>

        </div>
    </div>
</div>

</body>
</html>