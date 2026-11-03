
<?php
session_start();
require_once __DIR__ . "/config/koneksi.php";

// CEK LOGIN
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

// CEK ROLE ADMIN
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    exit("Anda tidak memiliki akses");
}

// CEK ID DATA
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    header("Location: jenis_pelanggaran.php?pesan=gagal");
    exit;
}

$id = (int) $_POST['id'];

// CEK APAKAH DATA MASIH DIGUNAKAN
$cek = mysqli_prepare(
    $koneksi,
    "SELECT id FROM t_pelanggaran_siswa WHERE pelanggaran_id = ? LIMIT 1"
);

if (!$cek) {
    header("Location: jenis_pelanggaran.php?pesan=gagal");
    exit;
}

mysqli_stmt_bind_param($cek, "i", $id);
mysqli_stmt_execute($cek);

$hasil = mysqli_stmt_get_result($cek);

// JIKA MASIH ADA RIWAYAT PELANGGARAN SISWA
if (mysqli_num_rows($hasil) > 0) {
    mysqli_stmt_close($cek);

    header("Location: jenis_pelanggaran.php?pesan=gagal");
    exit;
}

mysqli_stmt_close($cek);

// PROSES HAPUS DATA
$hapus = mysqli_prepare(
    $koneksi,
    "DELETE FROM t_pelanggaran WHERE id = ?"
);

if (!$hapus) {
    header("Location: jenis_pelanggaran.php?pesan=gagal");
    exit;
}

mysqli_stmt_bind_param($hapus, "i", $id);

if (mysqli_stmt_execute($hapus)) {

    if (mysqli_stmt_affected_rows($hapus) > 0) {
        mysqli_stmt_close($hapus);

        header("Location: jenis_pelanggaran.php?pesan=hapus");
        exit;
    }
}

mysqli_stmt_close($hapus);

header("Location: jenis_pelanggaran.php?pesan=gagal");
exit;
?>
