
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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: wali_kelas.php");
    exit;
}

$id = (int)($_POST['id'] ?? 0);

if ($id <= 0) {
    header("Location: wali_kelas.php?pesan=gagal_hapus");
    exit;
}

$stmt = mysqli_prepare($koneksi, "DELETE FROM t_wali_kelas WHERE id = ?");

if (!$stmt) {
    header("Location: wali_kelas.php?pesan=gagal_hapus");
    exit;
}

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$berhasil = mysqli_stmt_affected_rows($stmt) > 0;
mysqli_stmt_close($stmt);

header("Location: wali_kelas.php?pesan=" . ($berhasil ? "hapus" : "gagal_hapus"));
exit;
