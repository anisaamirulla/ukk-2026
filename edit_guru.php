
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
$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

if ($id <= 0) {
    header("Location: guru.php");
    exit;
}

$query = mysqli_query(
    $koneksi,
    "SELECT * FROM t_guru WHERE id = $id"
);

if (!$query || mysqli_num_rows($query) == 0) {
    exit("Data guru tidak ditemukan.");
}

$guru = mysqli_fetch_assoc($query);

if (isset($_POST['update'])) {
    $nip = trim($_POST['nip'] ?? '');
    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $status_aktif = (int) ($_POST['status_aktif'] ?? -1);

    $guru['nip'] = $nip;
    $guru['nama'] = $nama;
    $guru['email'] = $email;
    $guru['status_aktif'] = $status_aktif;

    if (!preg_match('/^[0-9]{18}$/', $nip)) {
        $pesan = "NIP harus terdiri dari tepat 18 digit angka.";
    } elseif ($nama == '') {
        $pesan = "Nama guru wajib diisi.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $pesan = "Format email tidak valid.";
    } elseif (!in_array($status_aktif, [0, 1], true)) {
        $pesan = "Status guru tidak valid.";
    } else {
        $cek = mysqli_prepare(
            $koneksi,
            "SELECT id FROM t_guru
             WHERE (nip = ? OR email = ?) AND id != ?"
        );

        mysqli_stmt_bind_param($cek, "ssi", $nip, $email, $id);
        mysqli_stmt_execute($cek);
        $hasil = mysqli_stmt_get_result($cek);

        if (mysqli_num_rows($hasil) > 0) {
            $pesan = "NIP atau email sudah digunakan guru lain.";
        } else {
            mysqli_begin_transaction($koneksi);

            try {
                $updateGuru = mysqli_prepare(
                    $koneksi,
                    "UPDATE t_guru
                     SET nip = ?, nama = ?, email = ?, status_aktif = ?
                     WHERE id = ?"
                );

                mysqli_stmt_bind_param(
                    $updateGuru,
                    "sssii",
                    $nip,
                    $nama,
                    $email,
                    $status_aktif,
                    $id
                );

                if (!mysqli_stmt_execute($updateGuru)) {
                    throw new Exception("Gagal memperbarui data guru.");
                }

                // Perbarui email dan nama akun pengguna jika terhubung.
                $userQuery = mysqli_prepare(
                    $koneksi,
                    "SELECT user_id FROM t_guru WHERE id = ?"
                );

                mysqli_stmt_bind_param($userQuery, "i", $id);
                mysqli_stmt_execute($userQuery);
                $userResult = mysqli_stmt_get_result($userQuery);
                $userData = mysqli_fetch_assoc($userResult);

                if (!empty($userData['user_id'])) {
                    $userId = (int) $userData['user_id'];

                    $updateUser = mysqli_prepare(
                        $koneksi,
                        "UPDATE t_user SET name = ?, email = ?
                         WHERE id = ?"
                    );

                    mysqli_stmt_bind_param(
                        $updateUser,
                        "ssi",
                        $nama,
                        $email,
                        $userId
                    );

                    if (!mysqli_stmt_execute($updateUser)) {
                        throw new Exception("Gagal memperbarui akun guru.");
                    }
                }

                mysqli_commit($koneksi);

                header("Location: guru.php?pesan=edit_berhasil");
                exit;

            } catch (Throwable $e) {
                mysqli_rollback($koneksi);
                $pesan = "Data guru gagal diperbarui. Periksa kembali database.";
            }
        }
    }
}

$namaAdmin = htmlspecialchars($_SESSION['name'] ?? 'Admin');
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Data Guru</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>

<body class="bg-light">

<div class="container-fluid">
    <div class="row min-vh-100">

        <aside class="col-md-3 col-lg-2 bg-dark text-white p-3">
            <h4 class="text-center mb-4">Admin Panel</h4>

            <div class="nav nav-pills flex-column gap-2">
                <a href="dashboard.php" class="nav-link text-white">Dashboard</a>
                <a href="siswa.php" class="nav-link text-white">Data Siswa</a>
                <a href="guru.php" class="nav-link active">Data Guru</a>
                <a href="kelas.php" class="nav-link text-white">Data Kelas</a>
                <a href="tahun_ajaran.php" class="nav-link text-white">Tahun Ajaran</a>
                <a href="penempatan_siswa.php" class="nav-link text-white">Penempatan Siswa</a>
                <a href="wali_kelas.php" class="nav-link text-white">Wali Kelas</a>
                <a href="kategori_pelanggaran.php" class="nav-link text-white">Kategori Pelanggaran</a>
                <a href="jenis_pelanggaran.php" class="nav-link text-white">Jenis Pelanggaran</a>
                <a href="laporan.php" class="nav-link text-white">Laporan</a>
                <a href="abautme.php" class="nav-link text-white">About Me</a>

                <hr class="text-secondary">

                <div class="text-secondary small px-3">
                    Login sebagai:
                    <div class="text-white fw-semibold">
                        <?= $namaAdmin ?>
                    </div>
                </div>

                <a href="logout.php" class="nav-link text-danger">Logout</a>
            </div>
        </aside>

        <main class="col-md-9 col-lg-10 p-3 p-md-4">

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                <div>
                    <h2 class="fw-bold mb-1">Edit Data Guru</h2>
                    <p class="text-secondary mb-0">
                        Perbarui informasi data guru.
                    </p>
                </div>

                <a href="guru.php" class="btn btn-outline-secondary">
                    Kembali ke Data Guru
                </a>
            </div>

            <div class="row justify-content-center">
                <div class="col-12 col-xl-8">

                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h4 class="mb-0">Edit Data Guru</h4>
                        </div>

                        <div class="card-body p-3 p-md-4">

                            <?php if ($pesan != ''): ?>
                                <div class="alert alert-danger alert-dismissible fade show"
                                     role="alert">
                                    <?= htmlspecialchars($pesan) ?>
                                    <button type="button" class="btn-close"
                                            data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>

                            <form method="POST" action="edit_guru.php?id=<?= $id ?>">

                                <input type="hidden" name="id" value="<?= $id ?>">

                                <div class="mb-3">
                                    <label for="nip" class="form-label">
                                        NIP <span class="text-danger">*</span>
                                    </label>

                                    <input type="text"
                                           name="nip"
                                           id="nip"
                                           class="form-control"
                                           placeholder="Masukkan 18 digit NIP"
                                           inputmode="numeric"
                                           maxlength="18"
                                           minlength="18"
                                           pattern="[0-9]{18}"
                                           title="NIP harus tepat 18 digit angka"
                                           oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 18)"
                                           value="<?= htmlspecialchars($guru['nip'] ?? '') ?>"
                                           required>
                                </div>

                                <div class="mb-3">
                                    <label for="nama" class="form-label">
                                        Nama Guru <span class="text-danger">*</span>
                                    </label>

                                    <input type="text"
                                           name="nama"
                                           id="nama"
                                           class="form-control"
                                           value="<?= htmlspecialchars($guru['nama'] ?? '') ?>"
                                           required>
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label">
                                        Email <span class="text-danger">*</span>
                                    </label>

                                    <input type="email"
                                           name="email"
                                           id="email"
                                           class="form-control"
                                           value="<?= htmlspecialchars($guru['email'] ?? '') ?>"
                                           required>
                                </div>

                                <div class="mb-3">
                                    <label for="status_aktif" class="form-label">
                                        Status Guru <span class="text-danger">*</span>
                                    </label>

                                    <select name="status_aktif"
                                            id="status_aktif"
                                            class="form-select"
                                            required>
                                        <option value="1"
                                            <?= (int)$guru['status_aktif'] === 1 ? 'selected' : '' ?>>
                                            Aktif
                                        </option>

                                        <option value="0"
                                            <?= (int)$guru['status_aktif'] === 0 ? 'selected' : '' ?>>
                                            Tidak Aktif
                                        </option>
                                    </select>
                                </div>

                                <hr>

                                <button type="submit" name="update"
                                        class="btn btn-primary">
                                    Simpan Perubahan
                                </button>

                                <a href="guru.php" class="btn btn-secondary">
                                    Kembali
                                </a>

                            </form>
                        </div>
                    </div>

                </div>
            </div>

        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
