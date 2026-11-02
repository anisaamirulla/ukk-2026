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
                        "UPDATE t_user
                         SET name = ?, email = ?
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

$menuAdmin = [
    ['Dashboard', 'dashboard.php'],
    ['Data Siswa', 'siswa.php'],
    ['Data Guru', 'guru.php'],
    ['Kelas', 'kelas.php'],
    ['Tahun Ajaran', 'tahun_ajaran.php'],
    ['Penempatan Siswa', 'penempatan_siswa.php'],
    ['Wali Kelas', 'wali_kelas.php'],
    ['Kategori Pelanggaran', 'kategori_pelanggaran.php'],
    ['Jenis Pelanggaran', 'jenis_pelanggaran.php'],
    ['Laporan', 'laporan.php'],
    ['About Me', 'abautme.php']
];
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Edit Data Guru - Sistem Informasi Pelanggaran Siswa</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container-fluid">
    <div class="row">

        <!-- SIDEBAR DISESUAIKAN DENGAN EDIT SISWA -->
        <aside class="col-12 col-md-3 col-lg-2 bg-dark min-vh-100 p-3">

            <div class="text-white mb-4 pt-3 pb-3 border-bottom border-secondary">
                <h5 class="fw-bold mb-2">
                    <i class="bi bi-shield-check me-2"></i>
                    Sistem Pelanggaran
                </h5>

                <small class="text-secondary">
                    Panel Admin
                </small>
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
                            <?= $namaAdmin; ?>
                        </div>

                        <small class="text-secondary">
                            ADMIN
                        </small>
                    </div>

                </div>
            </div>

            <div class="text-secondary small fw-semibold mb-3">
                MENU UTAMA
            </div>

            <ul class="nav nav-pills flex-column gap-2">

                <?php foreach ($menuAdmin as $item): ?>

                    <li class="nav-item">
                        <a
                            href="<?= htmlspecialchars($item[1], ENT_QUOTES, 'UTF-8'); ?>"
                            class="nav-link <?= $item[0] === 'Data Guru' ? 'active' : 'text-white'; ?> rounded-2 py-2 px-3"
                        >
                            <?= htmlspecialchars($item[0], ENT_QUOTES, 'UTF-8'); ?>
                        </a>
                    </li>

                <?php endforeach; ?>

            </ul>

            <hr class="border-secondary my-4">

            <a href="logout.php" class="nav-link text-danger rounded-2 py-2 px-3">
                <i class="bi bi-box-arrow-left me-2"></i>
                Logout
            </a>

        </aside>

        <!-- KONTEN EDIT GURU -->
        <main class="col-12 col-md-9 col-lg-10 p-3 p-lg-5">

            <!-- JUDUL HALAMAN -->
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-4 border-bottom">

                <div>
                    <h2 class="fw-bold mb-2">
                        Edit Data Guru
                    </h2>

                    <p class="text-secondary mb-0">
                        Silakan perbarui data guru dengan lengkap.
                    </p>
                </div>

            </div>

            <!-- PESAN ERROR -->
            <?php if ($pesan !== ''): ?>

                <div class="alert alert-danger alert-dismissible fade show" role="alert">

                    <?= htmlspecialchars($pesan, ENT_QUOTES, 'UTF-8'); ?>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"
                        aria-label="Tutup"
                    ></button>

                </div>

            <?php endif; ?>

            <!-- FORMULIR EDIT GURU -->
            <div class="row justify-content-center">

                <div class="col-12 col-xl-10">

                    <div class="card border-0 shadow-sm rounded-3">

                        <div class="card-header bg-white p-3">

                            <h5 class="text-primary fw-bold mb-1">
                                Formulir Guru
                            </h5>

                            <small class="text-secondary">
                                Perbarui informasi guru pada kolom yang tersedia.
                            </small>

                        </div>

                        <div class="card-body p-4">

                            <form method="POST" action="edit_guru.php?id=<?= $id ?>">

                                <input
                                    type="hidden"
                                    name="id"
                                    value="<?= $id ?>"
                                >

                                <div class="row g-3">

                                    <!-- NIP -->
                                    <div class="col-md-6">

                                        <label for="nip" class="form-label">
                                            NIP <span class="text-danger">*</span>
                                        </label>

                                        <input
                                            type="text"
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
                                            value="<?= htmlspecialchars($guru['nip'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                            required
                                        >

                                    </div>

                                    <!-- NAMA GURU -->
                                    <div class="col-md-6">

                                        <label for="nama" class="form-label">
                                            Nama Guru <span class="text-danger">*</span>
                                        </label>

                                        <input
                                            type="text"
                                            name="nama"
                                            id="nama"
                                            class="form-control"
                                            placeholder="Masukkan nama lengkap guru"
                                            value="<?= htmlspecialchars($guru['nama'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                            required
                                        >

                                    </div>

                                    <!-- EMAIL -->
                                    <div class="col-md-6">

                                        <label for="email" class="form-label">
                                            Email <span class="text-danger">*</span>
                                        </label>

                                        <input
                                            type="email"
                                            name="email"
                                            id="email"
                                            class="form-control"
                                            placeholder="Masukkan email guru"
                                            value="<?= htmlspecialchars($guru['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                            required
                                        >

                                    </div>

                                    <!-- STATUS GURU -->
                                    <div class="col-md-6">

                                        <label for="status_aktif" class="form-label">
                                            Status Guru <span class="text-danger">*</span>
                                        </label>

                                        <select
                                            name="status_aktif"
                                            id="status_aktif"
                                            class="form-select"
                                            required
                                        >

                                            <option
                                                value="1"
                                                <?= (int)($guru['status_aktif'] ?? 0) === 1 ? 'selected' : ''; ?>
                                            >
                                                Aktif
                                            </option>

                                            <option
                                                value="0"
                                                <?= (int)($guru['status_aktif'] ?? 0) === 0 ? 'selected' : ''; ?>
                                            >
                                                Tidak Aktif
                                            </option>

                                        </select>

                                    </div>

                                </div>

                                <hr class="my-4">

                                <!-- TOMBOL AKSI -->
                                <div class="d-flex justify-content-end gap-2">

                                    <a href="guru.php" class="btn btn-secondary">
                                        Batal
                                    </a>

                                    <button
                                        type="submit"
                                        name="update"
                                        class="btn btn-primary"
                                    >
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

            <!-- FOOTER -->
            <div class="text-secondary small mt-5 pt-3 border-top">
                Sistem Informasi Pelanggaran Siswa
            </div>

        </main>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>