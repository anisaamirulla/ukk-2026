
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

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: kategori_pelanggaran.php");
    exit;
}

if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    header("Location: kategori_pelanggaran.php?pesan=gagal");
    exit;
}

$id = (int) $_POST['id'];

$query = mysqli_prepare(
    $koneksi,
    "DELETE FROM t_pelanggaran_kategori WHERE id = ?"
);

mysqli_stmt_bind_param($query, "i", $id);

if (mysqli_stmt_execute($query) && mysqli_stmt_affected_rows($query) > 0) {
    header("Location: kategori_pelanggaran.php?pesan=hapus");
    exit;
}

header("Location: kategori_pelanggaran.php?pesan=gagal");
exit;
