```php
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

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: guru.php");
    exit;
}

$id = (int) $_GET['id'];

mysqli_begin_transaction($koneksi);

try {
    // Pastikan data guru tersedia
    $cek = mysqli_query(
        $koneksi,
        "SELECT id FROM t_guru WHERE id = $id FOR UPDATE"
    );

    if (!$cek || mysqli_num_rows($cek) == 0) {
        throw new Exception("Data guru tidak ditemukan.");
    }

    // Hapus penempatan wali kelas yang menggunakan guru ini
    $hapusWali = mysqli_query(
        $koneksi,
        "DELETE FROM t_wali_kelas WHERE guru_id = $id"
    );

    if (!$hapusWali) {
        throw new Exception(mysqli_error($koneksi));
    }

    // Hapus data guru
    $hapusGuru = mysqli_query(
        $koneksi,
        "DELETE FROM t_guru WHERE id = $id"
    );

    if (!$hapusGuru) {
        throw new Exception(mysqli_error($koneksi));
    }

    mysqli_commit($koneksi);

    header("Location: guru.php?pesan=hapus_berhasil");
    exit;

} catch (Throwable $e) {
    mysqli_rollback($koneksi);

    header("Location: guru.php?pesan=gagal_hapus");
    exit;
}
?>
```